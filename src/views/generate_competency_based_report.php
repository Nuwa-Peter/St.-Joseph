<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../src/includes/header.php'; // Include header at the top for consistent UI

// Helper function for displaying errors within the UI
function showError($message) {
    echo "<div class='container mt-4'><div class='alert alert-danger'><h4>Error</h4><p>" . htmlspecialchars($message) . "</p></div></div>";
    require_once __DIR__ . '/../../src/includes/footer.php';
    exit;
}

// --- Fetch School Settings ---
$school_settings = [];
$settings_sql = "SELECT setting_key, setting_value FROM school_settings";
$settings_result = $conn->query($settings_sql);
if ($settings_result) {
    while ($row = $settings_result->fetch_assoc()) {
        $school_settings[$row['setting_key']] = $row['setting_value'];
    }
}
// Provide fallbacks in case settings are not in DB, to prevent errors
$school_settings = array_merge([
    'school_name' => 'ST JOSEPH VOC. SEC SCHOOL - NYAMITYOBORA',
    'school_motto' => 'WITHOUT JESUS WHAT CAN THE WORLD GIVE YOU!',
    'school_id' => '1002215',
    'school_tel' => '0704050747',
    'school_email' => 's.josephthejust@gmail.com',
    'school_po_box' => 'P.O BOX 406 MBARARA',
    'school_logo_path' => 'images/logo.png'
], $school_settings);


// --- Get Parameters ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    showError("This page should be accessed via the report generation form.");
}

// Validate required POST variables
$generation_scope = $_POST['generation_scope'] ?? null;
$student_id = $_POST['student_id'] ?? null;
$class_level_id = $_POST['class_level_id'] ?? null;
$grading_scale_id = $_POST['grading_scale_id'] ?? null;

// Safely get optional POST variables
$next_term_begins = $_POST['next_term_begins'] ?? '';
$next_term_ends = $_POST['next_term_ends'] ?? '';
$class_teacher_remarks = $_POST['class_teacher_remarks'] ?? 'No remarks provided.';
$headteacher_remarks = $_POST['headteacher_remarks'] ?? 'No remarks provided.';
$academic_year = $_POST['academic_year'] ?? '';
$term = $_POST['term'] ?? '';


if (!$generation_scope || !$class_level_id || !$grading_scale_id) {
    showError("Missing required parameters. Please fill out the entire form.");
}
if ($generation_scope === 'individual' && !$student_id) {
    showError("Please select a student when generating an individual report card.");
}

// --- Get Student List ---
$student_ids = [];
if ($generation_scope === 'individual') {
    $student_ids[] = $student_id;
} else { // 'all'
    $stmt_all_students = $conn->prepare("SELECT u.id FROM users u JOIN stream_user su ON u.id = su.user_id JOIN streams s ON su.stream_id = s.id WHERE s.class_level_id = ? AND u.role = 'student' ORDER BY u.last_name, u.first_name");
    $stmt_all_students->bind_param("i", $class_level_id);
    $stmt_all_students->execute();
    $result_all_students = $stmt_all_students->get_result();
    while ($row = $result_all_students->fetch_assoc()) {
        $student_ids[] = $row['id'];
    }
    $stmt_all_students->close();
}

if (empty($student_ids)) {
    showError("No students found for the selected criteria.");
}

// --- Fetch shared data & assets ---
$grade_boundaries = [];
$stmt_grades = $conn->prepare("SELECT grade_name, min_score, max_score, comment FROM grade_boundaries WHERE grading_scale_id = ? ORDER BY max_score DESC");
$stmt_grades->bind_param("i", $grading_scale_id);
$stmt_grades->execute();
$result_grades = $stmt_grades->get_result();
while ($row = $result_grades->fetch_assoc()) {
    $grade_boundaries[] = $row;
}
$stmt_grades->close();

$school_logo_url = $school_settings['school_logo_path'];

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

// --- Main Loop ---
foreach ($student_ids as $current_student_id) {
    // Fetch student details
    $stmt_student = $conn->prepare("SELECT u.*, cl.name as class_name, s.name as stream_name, s.id as stream_id FROM users u
                                   LEFT JOIN stream_user su ON u.id = su.user_id
                                   LEFT JOIN streams s ON su.stream_id = s.id
                                   LEFT JOIN class_levels cl ON s.class_level_id = cl.id
                                   WHERE u.id = ?");
    $stmt_student->bind_param("i", $current_student_id);
    $stmt_student->execute();
    $student_result = $stmt_student->get_result()->fetch_assoc();
    $student_stream_id = $student_result['stream_id'] ?? null;
    $stmt_student->close();

    if (!$student_result) {
        echo "<div class='container mt-4'><div class='alert alert-warning'>Could not fetch details for student ID: " . htmlspecialchars($current_student_id) . ". Skipping.</div></div>";
        continue;
    }

    // Fetch subjects and marks for the current student
    $sql_marks = "
        SELECT
            sub.id AS subject_id, sub.name AS subject_name,
            MAX(CASE WHEN p.name = 'T1' THEN m.score ELSE NULL END) AS t1,
            MAX(CASE WHEN p.name = 'T2' THEN m.score ELSE NULL END) AS t2,
            MAX(CASE WHEN p.name = 'T3' THEN m.score ELSE NULL END) AS t3,
            MAX(CASE WHEN p.name = 'AS' THEN m.score ELSE NULL END) AS `as`,
            MAX(CASE WHEN p.name = 'FA_20' THEN m.score ELSE NULL END) AS fa_20,
            MAX(CASE WHEN p.name = 'EOT2_80' THEN m.score ELSE NULL END) AS eot2_80,
            (SELECT CONCAT(t.first_name, ' ', t.last_name) FROM users t JOIN paper_stream_user psu ON t.id = psu.user_id JOIN papers pa ON psu.paper_id = pa.id WHERE pa.subject_id = sub.id AND psu.stream_id = ? AND t.role = 'teacher' LIMIT 1) AS teacher_name
        FROM subjects sub
        JOIN stream_subject ss ON sub.id = ss.subject_id
        LEFT JOIN papers p ON sub.id = p.subject_id
        LEFT JOIN marks m ON p.id = m.paper_id AND m.user_id = ?
        WHERE ss.stream_id = ?
        GROUP BY sub.id, sub.name ORDER BY sub.name;";
    $stmt_marks = $conn->prepare($sql_marks);
    $stmt_marks->bind_param("iii", $student_stream_id, $current_student_id, $student_stream_id);
    $stmt_marks->execute();
    $marks_result = $stmt_marks->get_result();

    // Data Processing
    $subjects = [];
    $total_final_score = 0;
    $subject_count = 0;
    while ($row = $marks_result->fetch_assoc()) {
        $final_100 = ($row['fa_20'] ?? 0) + ($row['eot2_80'] ?? 0);
        $grade_info = calculateGrade($final_100, $grade_boundaries);
        $subjects[] = [
            "subject" => $row['subject_name'], "t1" => $row['t1'] ?? '-', "t2" => $row['t2'] ?? '-',
            "t3" => $row['t3'] ?? '-', "as" => $row['as'] ?? '-', "fa_20" => $row['fa_20'] ?? '-',
            "eot2_80" => $row['eot2_80'] ?? '-', "final_100" => $final_100, "grade" => $grade_info['grade'],
            "descriptor" => $grade_info['descriptor'], "tr" => getTeacherInitials($row['teacher_name'])
        ];
        if ($final_100 > 0) {
            $total_final_score += $final_100;
            $subject_count++;
        }
    }
    $stmt_marks->close();

    // Final Calculations & Variable Mapping
    $report_title = strtoupper($student_result['class_name'] . ' END OF ' . $term . ' RESULTS');

    // Determine student photo path, consistent with TCPDF implementation
    $photo_path = 'images/placeholder_male.png'; // Default
    if (!empty($student_result['photo']) && file_exists($student_result['photo'])) {
        $photo_path = $student_result['photo'];
    } elseif (!empty($student_result['gender'])) {
        if (strtolower($student_result['gender']) === 'female' && file_exists('images/placeholder_female.png')) {
            $photo_path = 'images/placeholder_female.png';
        }
    }

    $student = [
        "name" => strtoupper($student_result['first_name'] . ' ' . $student_result['last_name']),
        "scholar_id" => $student_result['unique_id'], "term" => $academic_year . ' ' . $term,
        "date" => date('d-M-Y'), "class" => $student_result['class_name'] . ' / ' . $student_result['stream_name'],
        "vcode" => 'N/A', "lin" => $student_result['lin'], "state" => ucfirst($student_result['status']), "residence" => 'N/A',
        "photo_url" => $photo_path
    ];
    $overall_average = ($subject_count > 0) ? round($total_final_score / $subject_count) : 0;
    $level_achievement = "RESULT 1"; // Placeholder

    // Render Template for the current student
    require 'report_card_template.php';
}

$conn->close();
?>
