<?php
require_once 'config.php';

// Authorization: Ensure an admin is logged in
$admin_roles = ['root', 'headteacher', 'director', 'admin'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $admin_roles)) {
    header("location: " . login_url());
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $parent_id = isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : 0;
    $student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;

    if ($parent_id > 0 && $student_id > 0) {
        $sql = "DELETE FROM parent_student WHERE parent_id = ? AND student_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $parent_id, $student_id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Student unlinked successfully.";
            } else {
                $_SESSION['error_message'] = "Database error while unlinking student.";
            }
            $stmt->close();
        }
    } else {
        $_SESSION['error_message'] = "Invalid parent or student ID for unlinking.";
    }

    header("location: " . user_edit_url($parent_id));
    exit;
} else {
    header("location: " . users_url());
    exit;
}

$conn->close();
?>
