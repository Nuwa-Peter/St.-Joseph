<?php
header('Content-Type: application/json');

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

/**
 * Finds if a 1-on-1 conversation already exists between two users.
 * @param mysqli $conn
 * @param int $user1_id
 * @param int $user2_id
 * @return int|null The conversation ID if found, otherwise null.
 */
function find_existing_1on1_conversation($conn, $user1_id, $user2_id) {
    $sql = "
        SELECT cp1.conversation_id
        FROM conversation_participants cp1
        INNER JOIN conversation_participants cp2 ON cp1.conversation_id = cp2.conversation_id
        WHERE cp1.user_id = ? AND cp2.user_id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user1_id, $user2_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $convo_id = $row['conversation_id'];
        // Check if it's a 2-person chat
        $count_stmt = $conn->prepare("SELECT COUNT(*) FROM conversation_participants WHERE conversation_id = ?");
        $count_stmt->bind_param("i", $convo_id);
        $count_stmt->execute();
        $count_stmt->bind_result($participant_count);
        $count_stmt->fetch();
        $count_stmt->close();

        if ($participant_count == 2) {
            $stmt->close();
            return $convo_id;
        }
    }

    $stmt->close();
    return null;
}

$existing_conversation_id = find_existing_1on1_conversation($conn, $current_user_id, $recipient_id);

if ($existing_conversation_id) {
    echo json_encode(['success' => true, 'conversation_id' => $existing_conversation_id, 'existed' => true]);
    $conn->close();
    exit;
}

// If no conversation exists, create a new one.
$conn->begin_transaction();
try {
    // 1. Create a new conversation
    $create_sql = "INSERT INTO conversations (is_group) VALUES (0)";
    if (!$conn->query($create_sql)) {
        throw new Exception("Failed to create conversation record: " . $conn->error);
    }
    $new_conversation_id = $conn->insert_id;

    // 2. Add both users as participants
    $part_sql = "INSERT INTO conversation_participants (conversation_id, user_id) VALUES (?, ?), (?, ?)";
    $part_stmt = $conn->prepare($part_sql);
    if (!$part_stmt) {
        throw new Exception("Failed to prepare participants statement: " . $conn->error);
    }
    $part_stmt->bind_param("iiii", $new_conversation_id, $current_user_id, $new_conversation_id, $recipient_id);
    if (!$part_stmt->execute()) {
        throw new Exception("Failed to add participants: " . $part_stmt->error);
    }
    $part_stmt->close();

    $conn->commit();

    echo json_encode(['success' => true, 'conversation_id' => $new_conversation_id, 'existed' => false]);

} catch (Exception $e) {
    $conn->rollback();
    error_log('Conversation creation failed: ' . $e->getMessage());
    echo json_encode(['error' => 'Could not create conversation. ' . $e->getMessage()]);
}

$conn->close();
?>
