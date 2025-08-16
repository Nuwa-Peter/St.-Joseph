<?php
session_start();
require_once 'config.php';
require_once 'vendor/autoload.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    exit('Unauthorized');
}

// --- Get POST data ---
$class_level_id = $_POST['class_level_id'] ?? null;
$academic_year = $_POST['academic_year'] ?? 'N/A';
$term = $_POST['term'] ?? 'N/A';
$generation_scope = $_POST['generation_scope'] ?? 'all';
$student_id = $_POST['student_id'] ?? null;
$grading_scale_id = $_POST['grading_scale_id'] ?? null;
$term_ends_on = $_POST['term_ends_on'] ?? 'N/A';
$next_term_begins_on = $_POST['next_term_begins_on'] ?? 'N/A';

if (!$class_level_id || !$grading_scale_id) {
    exit('Missing required parameters.');
}

// --- Data Fetching ---

// 1. Get Grading Scale
$grade_boundaries = [];
$stmt_grades = $conn->prepare("SELECT grade_name, min_score, max_score, comment FROM grade_boundaries WHERE grading_scale_id = ?");
$stmt_grades->bind_param("i", $grading_scale_id);
$stmt_grades->execute();
$result_grades = $stmt_grades->get_result();
while ($row = $result_grades->fetch_assoc()) {
    $grade_boundaries[] = $row;
}
$stmt_grades->close();

// 2. Get Student List
$student_ids = [];
if ($generation_scope === 'individual' && $student_id) {
    $student_ids[] = $student_id;
} else {
    $stmt_students = $conn->prepare("SELECT u.id FROM users u JOIN stream_user su ON u.id = su.user_id JOIN streams s ON su.stream_id = s.id WHERE s.class_level_id = ? AND u.role = 'student'");
    $stmt_students->bind_param("i", $class_level_id);
    $stmt_students->execute();
    $result_students = $stmt_students->get_result();
    while ($row = $result_students->fetch_assoc()) {
        $student_ids[] = $row['id'];
    }
    $stmt_students->close();
}

if (empty($student_ids)) {
    exit('No students found for the selected criteria.');
}

// --- Helper Functions ---
function calculateGrade($score, $boundaries) {
    if ($score === null) return ['grade' => 'N/A', 'comment' => 'N/A'];
    foreach ($boundaries as $boundary) {
        if ($score >= $boundary['min_score'] && $score <= $boundary['max_score']) {
            return ['grade' => $boundary['grade_name'], 'comment' => $boundary['comment']];
        }
    }
    return ['grade' => 'N/A', 'comment' => 'N/A'];
}

// --- PDF Generation ---

class ReportCardPDF extends TCPDF {
    // We will draw the header manually to have full control over position relative to the border
    public function Header() { }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }

    public function DrawBorder() {
        $this->SetLineStyle(['width' => 0.5, 'color' => [0, 0, 0]]);
        $this->Rect(5, 5, 200, 287); // Outer border
        $this->SetLineStyle(['width' => 0.2, 'color' => [0, 0, 0]]);
        $this->Rect(6, 6, 198, 285); // Inner border
    }
}

$pdf = new ReportCardPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('St. Joseph\'s VSS');
$pdf->SetTitle('Student Report Cards');
$pdf->SetMargins(10, 35, 10);
$pdf->SetAutoPageBreak(TRUE, 20);

// Loop through each student and generate their report card
foreach ($student_ids as $s_id) {
    // Fetch student details
    $stmt_student = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt_student->bind_param("i", $s_id);
    $stmt_student->execute();
    $student = $stmt_student->get_result()->fetch_assoc();
    $stmt_student->close();

    // Fetch student's marks for the term
    // This is a complex pivot query. We'll fetch all marks and process in PHP.
    $sql_marks = "
        SELECT sub.name as subject_name, p.exam_type, m.score, u.first_name, u.last_name
        FROM marks m
        JOIN papers p ON m.paper_id = p.id
        JOIN subjects sub ON p.subject_id = sub.id
        JOIN subject_teacher st ON sub.id = st.subject_id
        JOIN users u ON st.user_id = u.id
        WHERE m.user_id = ?
    "; // In a real app, you'd also filter by academic_year and term

    $stmt_marks = $conn->prepare($sql_marks);
    $stmt_marks->bind_param("i", $s_id);
    $stmt_marks->execute();
    $results_marks = $stmt_marks->get_result();

    $subject_marks = [];
    while($mark = $results_marks->fetch_assoc()) {
        $subject_name = $mark['subject_name'];
        if (!isset($subject_marks[$subject_name])) {
            $subject_marks[$subject_name] = [
                'teacher' => $mark['first_name'] . ' ' . $mark['last_name'],
                'scores' => []
            ];
        }
        $subject_marks[$subject_name]['scores'][$mark['exam_type']] = $mark['score'];
    }
    $stmt_marks->close();

    // --- Start PDF Page for this student ---
    $pdf->AddPage();
    $pdf->DrawBorder();

    // --- Manual Header ---
    // School Logo
    $pdf->Image('images/logo.png', 15, 8, 25, 25, 'PNG');
    // School Name
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->Cell(0, 15, 'St. Joseph\'s VSS', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    // Student Photo
    $photo_path = $student['photo'] ?? null;
    if ($photo_path && file_exists($photo_path)) {
        $pdf->Image($photo_path, 170, 10, 25, 25, 'PNG');
    } else {
        $placeholder = ($student['gender'] === 'Female') ? 'images/placeholder_female.png' : 'images/placeholder_male.png';
        if (file_exists($placeholder)) {
            $pdf->Image($placeholder, 170, 10, 25, 25, 'PNG');
        }
    }

    // Student Info Section
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Ln(15);
    $info_html = '
        <table border="1" cellpadding="4">
            <tr>
                <td width="15%"><b>Name:</b></td><td width="55%">' . htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) . '</td>
                <td width="15%"><b>LIN:</b></td><td width="15%">' . htmlspecialchars($student['lin'] ?? 'N/A') . '</td>
            </tr>
            <tr>
                <td><b>Class:</b></td><td>CLASS_NAME_HERE</td>
                <td><b>Term:</b></td><td>' . htmlspecialchars($term) . '</td>
            </tr>
        </table>';
    $pdf->writeHTML($info_html, true, false, true, false, '');

    // Marks Table
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', '', 9);
    $marks_html = '
        <table border="1" cellpadding="3">
            <tr bgcolor="#E0E0E0" style="font-weight:bold; text-align:center;">
                <th width="25%">Subject</th>
                <th width="10%">AOI</th>
                <th width="10%">CA</th>
                <th width="10%">BOT</th>
                <th width="10%">MOT</th>
                <th width="10%">EOT</th>
                <th width="10%">Avg</th>
                <th width="8%">Grd</th>
                <th width="7%">Init</th>
            </tr>';

    foreach($subject_marks as $name => $data) {
        $scores = $data['scores'];
        $aoi = $scores['AOI'] ?? '-';
        $ca = $scores['CA'] ?? '-';
        $bot = $scores['Beginning of Term'] ?? '-';
        $mot = $scores['Midterm'] ?? '-';
        $eot = $scores['End of Term'] ?? '-';

        $numeric_scores = array_filter([$aoi, $ca, $bot, $mot, $eot], 'is_numeric');
        $avg = !empty($numeric_scores) ? round(array_sum($numeric_scores) / count($numeric_scores)) : null;
        $grade_info = calculateGrade($avg, $grade_boundaries);
        $teacher_initials = 'N/A'; // Simplified

        $marks_html .= '
            <tr>
                <td>' . htmlspecialchars($name) . '</td>
                <td align="center">' . $aoi . '</td>
                <td align="center">' . $ca . '</td>
                <td align="center">' . $bot . '</td>
                <td align="center">' . $mot . '</td>
                <td align="center">' . $eot . '</td>
                <td align="center"><b>' . ($avg ?? '-') . '</b></td>
                <td align="center">' . $grade_info['grade'] . '</td>
                <td align="center">' . $teacher_initials . '</td>
            </tr>';
    }
    $marks_html .= '</table>';
    $pdf->writeHTML($marks_html, true, false, true, false, '');

    // Footer Info
    $pdf->Ln(10);
    $footer_html = '
        <table border="0" cellpadding="4">
            <tr>
                <td width="50%"><b>Class Teacher\'s Remarks:</b> .....................................................</td>
                <td width="50%"><b>Head Teacher\'s Remarks:</b> .....................................................</td>
            </tr>
             <tr>
                <td width="50%"><br><br><b>This Term Ends On:</b> '.htmlspecialchars($term_ends_on).'</td>
                <td width="50%"><br><br><b>Next Term Begins On:</b> '.htmlspecialchars($next_term_begins_on).'</td>
            </tr>
        </table>';
    $pdf->writeHTML($footer_html, true, false, true, false, '');
}

$conn->close();
$pdf->Output('report_cards.pdf', 'I');
exit;
?>
