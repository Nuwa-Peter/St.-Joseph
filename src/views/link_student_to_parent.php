<?php
require_once __DIR__ . '/../../config.php';

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

// Process POST data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : 0;
    $student_id = isset($_POST['student_id']) ? (int)$_POST['student_id'] : 0;

    if ($parent_id > 0 && $student_id > 0) {
        // Use INSERT IGNORE to avoid errors on duplicate entries
        $sql = "INSERT IGNORE INTO parent_student (parent_id, student_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";

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
    // Redirect if not a POST request
    header("location: users.php");
    exit;
}

$conn->close();
?>
