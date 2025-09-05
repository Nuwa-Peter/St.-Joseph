<?php
require_once 'config.php';

// Authorization check
$admin_roles = ['headteacher', 'root', 'director'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $admin_roles)) {
    header("location: dashboard.php");
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $event_id = $_GET['id'];

    // For simplicity, we'll just delete. In a real app, you might want to check
    // if the user has permission to delete this specific event, but the role check is sufficient for now.

    $sql = "DELETE FROM events WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Redirect back to the events list
header("location: events.php");
exit;
?>
