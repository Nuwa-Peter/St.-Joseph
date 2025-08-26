<?php
// NOTE: This script will not work until the messaging tables are created in the database.
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['error' => 'User not logged in.']);
    exit;
}

require_once 'config.php';

$current_user_id = $_SESSION['id'];
$conversation_id = isset($_GET['conversation_id']) ? (int)$_GET['conversation_id'] : 0;

if ($conversation_id === 0) {
    echo json_encode(['error' => 'Invalid conversation ID.']);
    exit;
}

// Security Check: Ensure the current user is part of this conversation.
$check_sql = "SELECT COUNT(*) FROM conversation_participants WHERE conversation_id = ? AND user_id = ?";
if ($check_stmt = $conn->prepare($check_sql)) {
    $check_stmt->bind_param("ii", $conversation_id, $current_user_id);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($count == 0) {
        echo json_encode(['error' => 'Access denied.']);
        exit;
    }
} else {
    echo json_encode(['error' => 'Database security check failed.']);
    exit;
}

// Mark messages in this conversation as read by the current user.
$update_sql = "UPDATE messages SET read_at = NOW() WHERE conversation_id = ? AND sender_id != ? AND read_at IS NULL";
if ($update_stmt = $conn->prepare($update_sql)) {
    $update_stmt->bind_param("ii", $conversation_id, $current_user_id);
    $update_stmt->execute();
    $update_stmt->close();
}
// We don't stop if this fails, as fetching is the primary goal.

// Fetch all messages for the conversation, joining with users to get sender details.
$messages_sql = "
    SELECT
        m.id,
        m.sender_id,
        m.content,
        m.created_at,
        u.first_name,
        u.last_name
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE m.conversation_id = ?
    ORDER BY m.created_at ASC;
";

$messages = [];

if ($stmt = $conn->prepare($messages_sql)) {
    $stmt->bind_param("i", $conversation_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        $stmt->close();
    } else {
        echo json_encode(['error' => 'Could not fetch messages. The database may not be set up yet.']);
        exit;
    }
} else {
    echo json_encode(['error' => 'Database query preparation failed.']);
    exit;
}

echo json_encode($messages);

$conn->close();
?>
