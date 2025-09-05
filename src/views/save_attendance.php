<?php
require_once __DIR__ . '/../../config.php';

// Ensure user is logged in and is a teacher or admin
$allowed_roles = ['teacher', 'headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stream_id = $_POST['stream_id'] ?? 0;
    $attendance_date = $_POST['attendance_date'] ?? '';
    $attendance_data = $_POST['attendance'] ?? [];

    // --- Validation ---
    if (empty($stream_id) || empty($attendance_date) || empty($attendance_data)) {
        // Handle error - redirect back with a message
        $_SESSION['attendance_error'] = "Invalid data submitted.";
        header("location: take_attendance.php");
        exit;
    }

    $conn->begin_transaction();
    try {
        // --- Get all student IDs for the given stream to ensure we only affect relevant students ---
        $student_ids_stmt = $conn->prepare("SELECT user_id FROM stream_user WHERE stream_id = ?");
        $student_ids_stmt->bind_param("i", $stream_id);
        $student_ids_stmt->execute();
        $result = $student_ids_stmt->get_result();
        $student_ids = [];
        while ($row = $result->fetch_assoc()) {
            $student_ids[] = $row['user_id'];
        }
        $student_ids_stmt->close();

        if (empty($student_ids)) {
             throw new Exception("No students found for this class.");
        }

        // --- Clear out any existing attendance for this class on this day ---
        // This makes the save operation idempotent.
        $delete_sql = "DELETE FROM attendances WHERE user_id IN (" . implode(',', array_fill(0, count($student_ids), '?')) . ") AND date = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        // Create the type string and params array for bind_param
        $types = str_repeat('i', count($student_ids)) . 's';
        $params = array_merge($student_ids, [$attendance_date]);
        $delete_stmt->bind_param($types, ...$params);
        $delete_stmt->execute();
        $delete_stmt->close();

        // --- Insert the new attendance data ---
        $insert_sql = "INSERT INTO attendances (user_id, date, status, recorded_by_id) VALUES (?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $recorded_by = $_SESSION['id'];

        foreach ($attendance_data as $student_id => $status) {
            // Only insert if the student actually belongs to this stream
            if (in_array($student_id, $student_ids) && !empty($status)) {
                 $insert_stmt->bind_param("issi", $student_id, $attendance_date, $status, $recorded_by);
                 $insert_stmt->execute();
            }
        }
        $insert_stmt->close();

        $conn->commit();
        $_SESSION['attendance_success'] = "Attendance for " . $attendance_date . " saved successfully!";

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['attendance_error'] = "An error occurred: " . $e->getMessage();
    }

    header("location: take_attendance.php");
    exit;

} else {
    // Redirect if not a POST request
    header("location: take_attendance.php");
    exit;
}

$conn->close();
?>
