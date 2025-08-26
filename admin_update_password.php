<?php
session_start();
require_once 'config.php';

// 1. Authorization Check
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$authorized_roles = ['headteacher', 'root', 'director'];
if (!in_array($_SESSION['role'], $authorized_roles)) {
    // Optional: Redirect with an error message
    header("location: dashboard.php?error=unauthorized");
    exit;
}

$user_id_to_update = 0;
$new_password = "";
$errors = [];

// 2. Process POST data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['user_id']) && !empty(trim($_POST['user_id']))) {
        $user_id_to_update = trim($_POST['user_id']);
    } else {
        $errors[] = "User ID is missing.";
    }

    if (isset($_POST['new_password']) && !empty(trim($_POST['new_password']))) {
        $new_password = trim($_POST['new_password']);
        // Optional: Add password strength validation here
        if (strlen($new_password) < 6) {
            $errors[] = "Password must be at least 6 characters long.";
        }
    } else {
        $errors[] = "New password cannot be empty.";
    }

    // 3. If no errors, update the database
    if (empty($errors)) {
        // 4. Hash the password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // 5. Update the user's record
        $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("si", $hashed_password, $user_id_to_update);

            if ($stmt->execute()) {
                // Success: Redirect back to the edit page with a success message
                header("location: user_edit.php?id=" . $user_id_to_update . "&password_reset_success=1");
                exit();
            } else {
                // Database error
                $errors[] = "Database error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $errors[] = "Database query preparation failed.";
        }
    }

    // If there were errors, redirect back with error messages
    if (!empty($errors)) {
        // Store errors in session to display them on the next page
        $_SESSION['password_reset_errors'] = $errors;
        header("location: user_edit.php?id=" . $user_id_to_update);
        exit();
    }
} else {
    // Redirect if not a POST request
    header("location: users.php");
    exit;
}

$conn->close();
?>
