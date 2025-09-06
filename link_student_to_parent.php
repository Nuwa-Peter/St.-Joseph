<?php
require_once 'config.php';

// Authorization: Ensure an admin is logged in
$admin_roles = ['root', 'headteacher', 'director', 'admin'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $admin_roles)) {
    header("location: " . login_url());
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : 0;
    $student_id = isset($_POST['student_id']) ? (int)$_POST['student_id'] : 0;

    if ($parent_id > 0 && $student_id > 0) {
        $sql = "INSERT IGNORE INTO parent_student (parent_id, student_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $parent_id, $student_id);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $_SESSION['success_message'] = "Student linked successfully.";
                } else {
                    $_SESSION['error_message'] = "Student was already linked to this parent.";
                }
            } else {
                $_SESSION['error_message'] = "Database error while linking student.";
            }
            $stmt->close();
        }
    } else {
        $_SESSION['error_message'] = "Invalid parent or student ID.";
    }

    header("location: " . user_edit_url($parent_id));
    exit;
} else {
    header("location: " . users_url());
    exit;
}

$conn->close();
?>
