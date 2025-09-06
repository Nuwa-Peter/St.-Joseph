<?php
require_once 'config.php';

// Security Check: Ensure the user is logged in and has an appropriate role.
$allowed_roles = ['teacher', 'headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: " . dashboard_url());
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("location: " . exam_attendance_url());
    exit;
}

$stream_id = $_POST['stream_id'] ?? 0;
$attendance_date = $_POST['attendance_date'] ?? '';
$attendance_data = $_POST['attendance'] ?? [];

if (empty($stream_id) || empty($attendance_date) || empty($attendance_data)) {
    $_SESSION['error_message'] = "Invalid data submitted. Please select a class and mark attendance.";
    header("location: " . exam_attendance_url());
    exit;
}

$conn->begin_transaction();
try {
    $student_ids_stmt = $conn->prepare("SELECT user_id FROM stream_user WHERE stream_id = ?");
    $student_ids_stmt->bind_param("i", $stream_id);
    $student_ids_stmt->execute();
    $result = $student_ids_stmt->get_result();
    $student_ids_in_stream = [];
    while ($row = $result->fetch_assoc()) {
        $student_ids_in_stream[] = $row['user_id'];
    }
    $student_ids_stmt->close();

    if (empty($student_ids_in_stream)) {
        throw new Exception("No students are registered for this class.");
    }

    // This logic assumes we are overwriting attendance for a given day.
    $delete_sql = "DELETE FROM attendances WHERE stream_id = ? AND date = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("is", $stream_id, $attendance_date);
    $delete_stmt->execute();
    $delete_stmt->close();

    $insert_sql = "INSERT INTO attendances (user_id, stream_id, date, status, recorded_by_id) VALUES (?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $recorded_by = $_SESSION['id'];

    foreach ($attendance_data as $student_id => $status) {
        if (in_array($student_id, $student_ids_in_stream) && !empty($status)) {
            $insert_stmt->bind_param("iissi", $student_id, $stream_id, $attendance_date, $status, $recorded_by);
            $insert_stmt->execute();
        }
    }
    $insert_stmt->close();

    $conn->commit();
    $_SESSION['success_message'] = "Exam attendance for " . htmlspecialchars($attendance_date) . " saved successfully!";

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
}

header("location: " . exam_attendance_url());
exit;

?>
