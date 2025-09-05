<?php
require_once __DIR__ . '/../../config.php';

// Ensure user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

$errors = [];
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['id'];
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $reason = trim($_POST['reason'] ?? '');

    // --- Validation ---
    if (empty($start_date)) {
        $errors[] = "Start date is required.";
    }
    if (empty($end_date)) {
        $errors[] = "End date is required.";
    }
    if (empty($reason)) {
        $errors[] = "Reason for leave is required.";
    }
    if (strtotime($end_date) < strtotime($start_date)) {
        $errors[] = "End date cannot be before the start date.";
    }

    if (empty($errors)) {
        // --- Database Insertion ---
        $sql = "INSERT INTO leave_requests (user_id, start_date, end_date, reason, status) VALUES (?, ?, ?, ?, 'pending')";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("isss", $user_id, $start_date, $end_date, $reason);

            if ($stmt->execute()) {
                $success_message = "Your leave request has been submitted successfully.";

                // --- Notification Logic ---
                $admin_roles = "'headteacher', 'root', 'director', 'dos', 'deputy headteacher'";
                $admin_ids_sql = "SELECT id FROM users WHERE role IN ($admin_roles)";

                if ($admin_result = $conn->query($admin_ids_sql)) {
                    $requester_name = $_SESSION['name'] ?? 'A user';
                    $message = "A new leave request has been submitted by " . $requester_name . ".";
                    $link = "admin_leave_requests.php"; // Link to the future admin view page

                    $notify_stmt = $conn->prepare("INSERT INTO app_notifications (user_id, message, link) VALUES (?, ?, ?)");

                    while ($admin_row = $admin_result->fetch_assoc()) {
                        $admin_id = $admin_row['id'];
                        $notify_stmt->bind_param("iss", $admin_id, $message, $link);
                        $notify_stmt->execute();
                    }
                    $notify_stmt->close();
                }

                // Redirect back to the form page with a success message
                $_SESSION['success_message'] = $success_message;
                header("location: request_leave.php");
                exit();

            } else {
                $errors[] = "Failed to submit your request. Please try again later.";
            }
            $stmt->close();
        } else {
            $errors[] = "Database error. Could not prepare the statement.";
        }
    }

    // If there were errors, redirect back with the errors
    $_SESSION['errors'] = $errors;
    header("location: request_leave.php");
    exit();
} else {
    // Redirect if accessed directly
    header("location: request_leave.php");
    exit;
}

$conn->close();
?>
