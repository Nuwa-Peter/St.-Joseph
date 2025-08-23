<?php
session_start();
require_once 'config.php';

// --- Get Parameters ---
// In a real scenario, these would come from the form POST in report_card_generator.php
// For development, we are simulating the POST data if it's not present.
if (empty($_POST)) {
    $_POST['student_id'] = 3; // Corresponds to TENDO PRISCILLA in the database dump
    $_POST['class_level_id'] = 1; // S1
    $_POST['academic_year'] = '2025';
    $_POST['term'] = 'Term 2';
    // This should be selected on the form. Assuming ID 1 is the new competency scale.
    $_POST['grading_scale_id'] = 1;
    $_POST['next_term_begins'] = '15-SEP-2025';
    $_POST['next_term_ends'] = '05-DEC-2025';
    $_POST['class_teacher_remarks'] = "TENDO asks thoughtful questions and shows a genuine interest in learning new concepts.";
    $_POST['headteacher_remarks'] = "TENDO excels in Mathematics and demonstrates a deep understanding of the material.";
}

$student_id = $_POST['student_id'];
$class_level_id = $_POST['class_level_id'];
$grading_scale_id = $_POST['grading_scale_id'];

// --- Data Fetching ---

// 1. Fetch student details along with class and stream info
$stmt_student = $conn->prepare("SELECT u.*, cl.name as class_name, s.name as stream_name FROM users u
                               LEFT JOIN stream_user su ON u.id = su.user_id
                               LEFT JOIN streams s ON su.stream_id = s.id
                               LEFT JOIN class_levels cl ON s.class_level_id = cl.id
                               WHERE u.id = ?");
$stmt_student->bind_param("i", $student_id);
$stmt_student->execute();
$student_result = $stmt_student->get_result()->fetch_assoc();
$student_stream_id = $student_result['stream_id'] ?? null; // Get student's stream_id
$stmt_student->close();

if (!$student_result) {
    exit('Student not found.');
}

// 2. Fetch grading scale
$grade_boundaries = [];
$stmt_grades = $conn->prepare("SELECT grade_name, min_score, max_score, comment FROM grade_boundaries WHERE grading_scale_id = ? ORDER BY max_score DESC");
$stmt_grades->bind_param("i", $grading_scale_id);
$stmt_grades->execute();
$result_grades = $stmt_grades->get_result();
while ($row = $result_grades->fetch_assoc()) {
    $grade_boundaries[] = $row;
}
$stmt_grades->close();

// 3. Fetch subjects and marks for the student
$sql_marks = "
    SELECT
        sub.id AS subject_id,
        sub.name AS subject_name,
        MAX(CASE WHEN p.name = 'T1' THEN m.score ELSE NULL END) AS t1,
        MAX(CASE WHEN p.name = 'T2' THEN m.score ELSE NULL END) AS t2,
        MAX(CASE WHEN p.name = 'T3' THEN m.score ELSE NULL END) AS t3,
        MAX(CASE WHEN p.name = 'AS' THEN m.score ELSE NULL END) AS `as`,
        MAX(CASE WHEN p.name = 'FA_20' THEN m.score ELSE NULL END) AS fa_20,
        MAX(CASE WHEN p.name = 'EOT2_80' THEN m.score ELSE NULL END) AS eot2_80,
        (SELECT CONCAT(t.first_name, ' ', t.last_name)
         FROM users t
         JOIN paper_stream_user psu ON t.id = psu.user_id
         JOIN papers pa ON psu.paper_id = pa.id
         WHERE pa.subject_id = sub.id
         AND psu.stream_id = ?
         AND t.role = 'teacher'
         LIMIT 1) AS teacher_name
    FROM subjects sub
    JOIN stream_subject ss ON sub.id = ss.subject_id
    LEFT JOIN papers p ON sub.id = p.subject_id
    LEFT JOIN marks m ON p.id = m.paper_id AND m.user_id = ?
    WHERE ss.stream_id = ?
    GROUP BY sub.id, sub.name
    ORDER BY sub.name;
";

$stmt_marks = $conn->prepare($sql_marks);
$stmt_marks->bind_param("iii", $student_stream_id, $student_id, $student_stream_id);
$stmt_marks->execute();
$marks_result = $stmt_marks->get_result();

// --- Helper Functions ---
function calculateGrade($score, $boundaries) {
    if ($score === null) return ['grade' => 'N/A', 'descriptor' => 'N/A'];
    foreach ($boundaries as $boundary) {
        if ($score >= $boundary['min_score'] && $score <= $boundary['max_score']) {
            return ['grade' => $boundary['grade_name'], 'descriptor' => $boundary['comment']];
        }
    }
    return ['grade' => 'UNG', 'descriptor' => 'Ungraded'];
}

function getTeacherInitials($name) {
    if (empty($name)) return 'N/A';
    $parts = explode(' ', $name);
    $initials = '';
    foreach ($parts as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }
    return $initials;
}

// --- Data Processing ---
$subjects = [];
$total_final_score = 0;
$subject_count = 0;

while ($row = $marks_result->fetch_assoc()) {
    // Calculate final score. Use null coalescing operator to treat missing scores as 0.
    $final_100 = ($row['fa_20'] ?? 0) + ($row['eot2_80'] ?? 0);
    $grade_info = calculateGrade($final_100, $grade_boundaries);

    $subjects[] = [
        "subject" => $row['subject_name'],
        "t1" => $row['t1'] ?? '-',
        "t2" => $row['t2'] ?? '-',
        "t3" => $row['t3'] ?? '-',
        "as" => $row['as'] ?? '-',
        "fa_20" => $row['fa_20'] ?? '-',
        "eot2_80" => $row['eot2_80'] ?? '-',
        "final_100" => $final_100,
        "grade" => $grade_info['grade'],
        "descriptor" => $grade_info['descriptor'],
        "tr" => getTeacherInitials($row['teacher_name'])
    ];

    if ($final_100 > 0) { // Only include subjects with a score in the average calculation
        $total_final_score += $final_100;
        $subject_count++;
    }
}
$stmt_marks->close();

// --- Final Calculations & Variable Mapping ---

$report_title = strtoupper($student_result['class_name'] . ' END OF ' . $_POST['term'] . ' RESULTS');

$student = [
    "name" => strtoupper($student_result['first_name'] . ' ' . $student_result['last_name']),
    "scholar_id" => $student_result['unique_id'],
    "term" => $_POST['academic_year'] . ' ' . $_POST['term'],
    "date" => date('d-M-Y'),
    "class" => $student_result['class_name'] . ' / ' . $student_result['stream_name'],
    "vcode" => 'N/A', // This seems specific, maybe from another table
    "lin" => $student_result['lin'],
    "state" => ucfirst($student_result['status']),
    "residence" => 'N/A' // Not in users table
];

$overall_average = ($subject_count > 0) ? round($total_final_score / $subject_count) : 0;

// Level of achievement logic would go here. For now, it's a placeholder.
$level_achievement = "RESULT 1";

// Get variables for the template from the simulated POST
$next_term_begins = $_POST['next_term_begins'];
$next_term_ends = $_POST['next_term_ends'];
$class_teacher_remarks = $_POST['class_teacher_remarks'];
$headteacher_remarks = $_POST['headteacher_remarks'];

// --- Render Template ---
require_once 'report_card_template.php';


/*
--- PDF Generation Recommendation ---

This script generates a modern HTML report card with complex CSS.
For the best results when converting this HTML to a PDF, we recommend using a library
that has strong support for modern CSS3 features, such as mPDF or Dompdf.

The existing TCPDF library is excellent for generating PDFs programmatically,
but it may not render all the visual styles in this template with perfect fidelity.

Example using mPDF (if installed via Composer):
===============================================
// To use this, you would capture the output of the template instead of echoing it directly.
// For example, by using output buffering:

/*
ob_start();
require_once 'report_card_template.php';
$html = ob_get_clean();

// Then, load mPDF and generate the PDF
require_once 'vendor/autoload.php'; // Assuming composer is used
$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
$mpdf->WriteHTML($html);
$mpdf->Output('report-card.pdf', 'I');
*/

$conn->close();
?>
