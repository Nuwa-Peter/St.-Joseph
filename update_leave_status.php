<?php
session_start();
require_once 'config.php';

// Ensure user is an admin
$admin_roles = ['root', 'headteacher', 'director', 'dos', 'deputy headteacher'];
if (!isset($_SESSION["loggedin"]) || !in_array($_SESSION['role'], $admin_roles)) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request_id = intval($_POST['request_id'] ?? 0);
    $new_status = $_POST['status'] ?? '';
    $reviewer_id = $_SESSION['id'];

    // Validate status
    if ($new_status !== 'approved' && $new_status !== 'rejected') {
        // Invalid status, redirect back
        header("location: admin_leave_requests.php");
        exit;
    }

    if ($request_id > 0) {
        $sql = "UPDATE leave_requests SET status = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sii", $new_status, $reviewer_id, $request_id);
            $stmt->execute();
            $stmt->close();

            // Optional: Notify the user who made the request
            // (Leaving this out for now to keep it simple, but could be added here)
        }
    }
}

// Redirect back to the admin page
header("location: admin_leave_requests.php");
exit;

$conn->close();
?>
