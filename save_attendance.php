<?php
require_once 'config.php';

// Ensure user is logged in and is a teacher or admin
$allowed_roles = ['teacher', 'headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: " . dashboard_url());
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stream_id = $_POST['stream_id'] ?? 0;
    $attendance_date = $_POST['attendance_date'] ?? '';
    $attendance_data = $_POST['attendance'] ?? [];

    if (empty($stream_id) || empty($attendance_date) || empty($attendance_data)) {
        $_SESSION['error_message'] = "Invalid data submitted.";
        header("location: " . take_attendance_url());
        exit;
    }

    $conn->begin_transaction();
    try {
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

        $delete_sql = "DELETE FROM attendances WHERE user_id IN (" . implode(',', array_fill(0, count($student_ids), '?')) . ") AND date = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $types = str_repeat('i', count($student_ids)) . 's';
        $params = array_merge($student_ids, [$attendance_date]);
        $delete_stmt->bind_param($types, ...$params);
        $delete_stmt->execute();
        $delete_stmt->close();

        $insert_sql = "INSERT INTO attendances (user_id, date, status, recorded_by_id) VALUES (?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $recorded_by = $_SESSION['id'];

        foreach ($attendance_data as $student_id => $status) {
            if (in_array($student_id, $student_ids) && !empty($status)) {
                 $insert_stmt->bind_param("issi", $student_id, $attendance_date, $status, $recorded_by);
                 $insert_stmt->execute();
            }
        }
        $insert_stmt->close();

        $conn->commit();
        $_SESSION['success_message'] = "Attendance for " . $attendance_date . " saved successfully!";

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
    }

    header("location: " . take_attendance_url());
    exit;

} else {
    header("location: " . take_attendance_url());
    exit;
}

$conn->close();
?>
