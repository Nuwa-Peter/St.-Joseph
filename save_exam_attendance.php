<?php
session_start();
require_once 'config.php';

// This script handles the submission of the exam attendance form.

// Security Check: Ensure the user is logged in and has an appropriate role.
$allowed_roles = ['teacher', 'headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: dashboard.php");
    exit;
}

// Ensure the request is a POST request.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("location: exam_attendance.php");
    exit;
}

// --- Get and Validate Form Data ---
$stream_id = $_POST['stream_id'] ?? 0;
$attendance_date = $_POST['attendance_date'] ?? '';
$attendance_data = $_POST['attendance'] ?? [];
// Note: Exam attendance does not currently support notes, but the logic is here if needed later.
// $notes_data = $_POST['notes'] ?? [];

if (empty($stream_id) || empty($attendance_date) || empty($attendance_data)) {
    $_SESSION['attendance_error'] = "Invalid data submitted. Please select a class and mark attendance.";
    header("location: exam_attendance.php");
    exit;
}

// --- Database Operations ---
$conn->begin_transaction();
try {
    // 1. Get all student IDs for the given stream.
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

    // 2. Clear out any existing attendance for this specific stream and date.
    // This is a simplified approach. A more robust system might have an 'attendance_type' column.
    $delete_sql = "DELETE FROM attendances WHERE stream_id = ? AND date = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("is", $stream_id, $attendance_date);
    $delete_stmt->execute();
    $delete_stmt->close();

    // 3. Insert the new attendance data.
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

    // 4. Commit the transaction.
    $conn->commit();
    $_SESSION['attendance_success'] = "Exam attendance for " . htmlspecialchars($attendance_date) . " saved successfully!";

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['attendance_error'] = "An error occurred: " . $e->getMessage();
}

// --- Redirect back to the attendance page ---
header("location: exam_attendance.php");
exit;

?>
