<?php
require_once 'config.php';

// Authorization: Ensure an admin is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
$admin_roles = ['root', 'headteacher', 'director']; // Or other appropriate admin roles
if (!in_array($_SESSION['role'], $admin_roles)) {
    // Redirect if not an authorized admin
    header("location: dashboard.php?error=unauthorized");
    exit;
}

// Process GET data
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $parent_id = isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : 0;
    $student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;

    if ($parent_id > 0 && $student_id > 0) {
        $sql = "DELETE FROM parent_student WHERE parent_id = ? AND student_id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $parent_id, $student_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Redirect back to the user edit page
    header("location: user_edit.php?id=" . $parent_id);
    exit;
} else {
    // Redirect if not a GET request
    header("location: users.php");
    exit;
}

$conn->close();
?>
