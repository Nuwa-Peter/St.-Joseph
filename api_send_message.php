<?php
// NOTE: This script will not work until the messaging tables are created in the database.
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['error' => 'User not logged in.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(['error' => 'Invalid request method.']);
    exit;
}

require_once 'config.php';

$current_user_id = $_SESSION['id'];
$data = json_decode(file_get_contents('php://input'), true);

$conversation_id = isset($data['conversation_id']) ? (int)$data['conversation_id'] : 0;
$content = isset($data['content']) ? trim($data['content']) : '';

if ($conversation_id === 0 || empty($content)) {
    echo json_encode(['error' => 'Missing conversation ID or content.']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Security Check: Ensure the current user is part of this conversation.
    $check_sql = "SELECT COUNT(*) FROM conversation_participants WHERE conversation_id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $conversation_id, $current_user_id);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($count == 0) {
        throw new Exception('Access denied.');
    }

    // Insert the new message
    $insert_sql = "INSERT INTO messages (conversation_id, sender_id, content) VALUES (?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iis", $conversation_id, $current_user_id, $content);
    $insert_stmt->execute();
    $new_message_id = $insert_stmt->insert_id;
    $insert_stmt->close();

    // Update the conversation's updated_at timestamp to bring it to the top of the list.
    $update_sql = "UPDATE conversations SET updated_at = NOW() WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $conversation_id);
    $update_stmt->execute();
    $update_stmt->close();

    // Commit transaction
    $conn->commit();

    // Fetch the newly created message to return it to the client
    $new_message_sql = "SELECT m.*, u.first_name, u.last_name FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.id = ?";
    $new_message_stmt = $conn->prepare($new_message_sql);
    $new_message_stmt->bind_param("i", $new_message_id);
    $new_message_stmt->execute();
    $result = $new_message_stmt->get_result();
    $new_message = $result->fetch_assoc();
    $new_message_stmt->close();

    echo json_encode(['success' => true, 'message' => $new_message]);

} catch (Exception $e) {
    $conn->rollback();
    // Use a generic error message for security. Log the actual error on the server.
    error_log('Message sending failed: ' . $e->getMessage());
    echo json_encode(['error' => 'Could not send message. The database may not be set up yet or another error occurred.']);
}

$conn->close();
?>
