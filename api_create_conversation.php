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
$recipient_id = isset($data['recipient_id']) ? (int)$data['recipient_id'] : 0;

if ($recipient_id === 0 || $recipient_id === $current_user_id) {
    echo json_encode(['error' => 'Invalid recipient ID.']);
    exit;
}

// Check if a 1-on-1 conversation between these two users already exists.
$find_sql = "
    SELECT T1.conversation_id
    FROM conversation_participants AS T1
    INNER JOIN conversation_participants AS T2 ON T1.conversation_id = T2.conversation_id
    WHERE T1.user_id = ? AND T2.user_id = ?
    AND (
        SELECT COUNT(*)
        FROM conversation_participants
        WHERE conversation_id = T1.conversation_id
    ) = 2
    LIMIT 1;
";
$existing_conversation_id = null;
if ($find_stmt = $conn->prepare($find_sql)) {
    $find_stmt->bind_param("ii", $current_user_id, $recipient_id);
    if ($find_stmt->execute()) {
        $find_stmt->bind_result($existing_conversation_id);
        $find_stmt->fetch();
    }
    $find_stmt->close();
}


if ($existing_conversation_id) {
    echo json_encode(['success' => true, 'conversation_id' => $existing_conversation_id, 'existed' => true]);
    $conn->close();
    exit;
}

// If no conversation exists, create a new one.
$conn->begin_transaction();
try {
    // 1. Create a new conversation
    $conn->query("INSERT INTO conversations () VALUES ()");
    $new_conversation_id = $conn->insert_id;

    // 2. Add both users as participants
    $part_sql = "INSERT INTO conversation_participants (conversation_id, user_id) VALUES (?, ?), (?, ?)";
    $part_stmt = $conn->prepare($part_sql);
    $part_stmt->bind_param("iiii", $new_conversation_id, $current_user_id, $new_conversation_id, $recipient_id);
    $part_stmt->execute();
    $part_stmt->close();

    $conn->commit();

    echo json_encode(['success' => true, 'conversation_id' => $new_conversation_id, 'existed' => false]);

} catch (Exception $e) {
    $conn->rollback();
    error_log('Conversation creation failed: ' . $e->getMessage());
    echo json_encode(['error' => 'Could not create conversation. The database may not be set up yet or another error occurred.']);
}

$conn->close();
?>
