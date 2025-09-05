<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../src/includes/csrf_helper.php'; // Need to include helpers

// Start session - this file is called directly, so it needs to manage its own session and auth.

// Check for a logged-in user
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    // We need the url_helper to redirect properly
    require_once __DIR__ . '/../../src/includes/url_helper.php';
    header("location: " . login_url());
    exit;
}

// Check if the form was submitted
if (isset($_POST['update_profile'])) {
    verify_csrf_token();
    $user_id = $_SESSION['id'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $errors = [];

    // 4. Validation
    if (empty($username)) {
        $errors['username'] = "Username is required.";
    } else {
        // Check if username is taken by another user
        $sql = "SELECT id FROM users WHERE username = ? AND id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $username, $user_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors['username'] = "This username is already taken.";
        }
        $stmt->close();
    }

    if (empty($email)) {
        // If user wants to remove their email, we allow it, but it must be set to NULL
        $email_to_update = null;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    } else {
        // Check if email is taken by another user
        $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors['email'] = "This email is already registered by another user.";
        }
        $stmt->close();
        $email_to_update = $email;
    }

    // 5. Update database if no errors
    if (empty($errors)) {
        $sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $username, $email_to_update, $user_id);

        if ($stmt->execute()) {
            // Update session variables as well
            $_SESSION['username'] = $username;
            // Redirect with success message
            header("location: profile.php?update_success=1");
            exit();
        } else {
            // Redirect with error message
            $_SESSION['profile_errors'] = ['db' => 'Something went wrong. Please try again.'];
            header("location: profile.php");
            exit();
        }
        $stmt->close();
    } else {
        // Redirect back with validation errors
        $_SESSION['profile_errors'] = $errors;
        header("location: profile.php");
        exit();
    }
} else {
    // Redirect if form was not submitted correctly
    header("location: profile.php");
    exit;
}

$conn->close();
?>
