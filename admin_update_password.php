<?php
require_once 'config.php';

// Authorization Check
$authorized_roles = ['headteacher', 'root', 'director', 'admin'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $authorized_roles)) {
    header("location: " . login_url());
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id_to_update = isset($_POST['user_id']) ? trim($_POST['user_id']) : 0;
    $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';

    if (empty($user_id_to_update)) {
        $_SESSION['error_message'] = "User ID is missing.";
        header("location: " . users_url());
        exit();
    }

    if (strlen($new_password) < 6) {
        $_SESSION['error_message'] = "Password must be at least 6 characters long.";
        header("location: " . user_edit_url($user_id_to_update));
        exit();
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("si", $hashed_password, $user_id_to_update);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "User's password has been reset successfully.";
            // Notification logic can be added here if desired
        } else {
            $_SESSION['error_message'] = "Database error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Database query preparation failed.";
    }

    header("location: " . user_edit_url($user_id_to_update));
    exit();

} else {
    header("location: " . users_url());
    exit;
}

$conn->close();
?>
