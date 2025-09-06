<?php
require_once 'config.php';

// Ensure user is an admin
$admin_roles = ['root', 'headteacher', 'admin'];
if (!isset($_SESSION["loggedin"]) || !in_array($_SESSION['role'], $admin_roles)) {
    header("location: " . dashboard_url());
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stream_id = $_POST['stream_id'] ?? 0;
    $teacher_id = $_POST['teacher_id'] ?? null;
    $class_level_id = $_POST['class_level_id'] ?? 0;

    if (empty($teacher_id)) {
        $teacher_id = null;
    }

    if ($stream_id > 0) {
        $sql = "UPDATE streams SET class_teacher_id = ? WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $teacher_id, $stream_id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Class teacher updated successfully.";
            } else {
                $_SESSION['error_message'] = "Error updating record: " . $conn->error;
            }
            $stmt->close();
        }
    } else {
        $_SESSION['error_message'] = "Invalid stream ID.";
    }

    if ($class_level_id > 0) {
        header("location: " . streams_url(['class_level_id' => $class_level_id]));
    } else {
        header("location: " . classes_url());
    }
    exit();
}
?>
