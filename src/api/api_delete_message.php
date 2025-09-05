<?php
header('Content-Type: application/json');

// 1. Authorization Check
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['error' => 'User not logged in.']);
    exit;
}

$authorized_roles = ['headteacher', 'root', 'director'];
if (!in_array($_SESSION['role'], $authorized_roles)) {
    echo json_encode(['error' => 'Unauthorized: You do not have permission to delete messages.']);
    exit;
}

require_once __DIR__ . '/../../config.php';

// 2. Get message_id from POST body
$data = json_decode(file_get_contents('php://input'), true);
$message_id = isset($data['message_id']) ? (int)$data['message_id'] : 0;

if ($message_id === 0) {
    echo json_encode(['error' => 'Invalid message ID.']);
    exit;
}

// 3. Delete the message from the database
try {
    $sql = "DELETE FROM messages WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Database query preparation failed: " . $conn->error);
    }

    $stmt->bind_param("i", $message_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("Message not found or already deleted.");
        }
    } else {
        throw new Exception("Failed to execute deletion: " . $stmt->error);
    }

    $stmt->close();
} catch (Exception $e) {
    error_log("Message deletion failed: " . $e->getMessage());
    echo json_encode(['error' => 'Could not delete message.', 'details' => $e->getMessage()]);
}

$conn->close();
?>
