<?php
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
    echo json_encode(['error' => 'Client Error: Missing conversation ID or content.']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Security Check: Ensure the current user is part of this conversation.
    $check_sql = "SELECT COUNT(*) FROM conversation_participants WHERE conversation_id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    if (!$check_stmt) {
        throw new Exception("Security check preparation failed: " . $conn->error);
    }
    $check_stmt->bind_param("ii", $conversation_id, $current_user_id);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($count == 0) {
        throw new Exception('Security Error: You are not a participant in this conversation.');
    }

    // Insert the new message
    $insert_sql = "INSERT INTO messages (conversation_id, sender_id, content) VALUES (?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    if (!$insert_stmt) {
        throw new Exception("Message insert preparation failed: " . $conn->error);
    }
    $insert_stmt->bind_param("iis", $conversation_id, $current_user_id, $content);
    if (!$insert_stmt->execute()) {
        throw new Exception("Message insert execution failed: " . $insert_stmt->error);
    }
    $new_message_id = $insert_stmt->insert_id;
    $insert_stmt->close();

    // Update the conversation's updated_at timestamp to bring it to the top of the list.
    $update_sql = "UPDATE conversations SET updated_at = NOW() WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $conversation_id);
    $update_stmt->execute();
    $update_stmt->close();

    // --- Create Notifications for all Staff Members ---
    // 1. Get sender's name
    $sender_name = $_SESSION['name'];

    // 2. Get all staff members
    $staff_ids = [];
    $staff_sql = "SELECT id FROM users WHERE role != 'student'";
    $staff_result = $conn->query($staff_sql);
    while ($row = $staff_result->fetch_assoc()) {
        $staff_ids[] = $row['id'];
    }

    // 3. Create a unique list, excluding the sender
    if (($key = array_search($current_user_id, $staff_ids)) !== false) {
        unset($staff_ids[$key]);
    }

    // 4. Insert notifications for each staff member
    if (!empty($staff_ids)) {
        $notify_sql = "INSERT INTO app_notifications (user_id, message, link) VALUES (?, ?, ?)";
        $notify_stmt = $conn->prepare($notify_sql);
        $notification_message = "New message from " . $sender_name;
        $notification_link = "messages.php?conversation_id=" . $conversation_id;

        foreach ($staff_ids as $staff_id) {
            $notify_stmt->bind_param("iss", $staff_id, $notification_message, $notification_link);
            $notify_stmt->execute();
        }
        $notify_stmt->close();
    }

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
    error_log('Message sending failed: ' . $e->getMessage());
    echo json_encode(['error' => 'Failed to send message.', 'details' => $e->getMessage()]);
}

$conn->close();
?>
