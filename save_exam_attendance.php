<?php
session_start();
require_once 'config.php';

// Ensure user is logged in and is a teacher or admin
$allowed_roles = ['teacher', 'headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || !in_array($_SESSION['role'], $allowed_roles
)) {
    header("location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stream_id = $_POST['stream_id'] ?? 0;
    $attendance_date = $_POST['attendance_date'] ?? '';
    $attendance_data = $_POST['attendance'] ?? [];

    // --- Validation ---
    if (empty($stream_id) || empty($attendance_date) || empty($attendance_data))
 {
        // Handle error - redirect back with a message
        $_SESSION['attendance_error'] = "Invalid data submitted.";
        header("location: take_attendance.php");
        exit;
    }

    $conn->begin_transaction();
    try {
        // --- Get all student IDs for the given stream to ensure we only affect
 relevant students ---
        $student_ids_stmt = $conn->prepare("SELECT user_id FROM stream_user WHER
E stream_id = ?");
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

        // This is safer. It only deletes attendance for the specific stream and date.
        $delete_sql = "DELETE FROM attendances WHERE stream_id = ? AND date = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("is", $stream_id, $attendance_date);
        $delete_stmt->execute();
        $delete_stmt->close();

        // Add stream_id to the insert statement
        $insert_sql = "INSERT INTO attendances (user_id, stream_id, date, status, recorded_by_id) VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $recorded_by = $_SESSION['id'];

        foreach ($attendance_data as $student_id => $status) {
            // Only insert if the student actually belongs to this stream
            if (in_array($student_id, $student_ids) && !empty($status)) {
                 $insert_stmt->bind_param("iissi", $student_id, $stream_id, $attendance_date, $status, $recorded_by);
                 $insert_stmt->execute();
            }
        }
        $insert_stmt->close();

        $conn->commit();
        $_SESSION['attendance_success'] = "Attendance for " . $attendance_date .
 " saved successfully!";

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['attendance_error'] = "An error occurred: " . $e->getMessage()
;
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
