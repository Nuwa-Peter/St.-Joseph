<?php
require_once __DIR__ . '/../../config.php';

// Ensure user is an admin
$admin_roles = ['root', 'headteacher'];
if (!isset($_SESSION["loggedin"]) || !in_array($_SESSION['role'], $admin_roles))
 {
    header("location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stream_id = $_POST['stream_id'] ?? 0;
    $teacher_id = $_POST['teacher_id'] ?? null;
    $class_level_id = $_POST['class_level_id'] ?? 0;

    // If "None" is selected, teacher_id will be an empty string, so we should treat it as NULL.
    if (empty($teacher_id)) {
        $teacher_id = null;
    }

    if ($stream_id > 0) {
        $sql = "UPDATE streams SET class_teacher_id = ? WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $teacher_id, $stream_id);
            if ($stmt->execute()) {
                $_SESSION['message'] = "Class teacher updated successfully.";
            } else {
                $_SESSION['message'] = "Error updating record: " . $conn->error;
            }
            $stmt->close();
        }
    } else {
        $_SESSION['message'] = "Invalid stream ID.";
    }

    // Redirect back to the streams page for the correct class level
    if ($class_level_id > 0) {
        header("location: streams.php?class_level_id=" . $class_level_id);
    } else {
        header("location: class_levels.php"); // Fallback
    }
    exit();
}
?>
