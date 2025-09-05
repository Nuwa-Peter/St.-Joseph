<?php
// NOTE: This script will not work until the messaging tables are created in the database.
header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['error' => 'User not logged in.']);
    exit;
}

require_once __DIR__ . '/../../config.php';

$current_user_id = $_SESSION['id'];

// This query retrieves all conversations for the current user.
// It handles both 1-on-1 chats and group chats.
$sql = "
    SELECT
        c.id AS conversation_id,
        c.name AS conversation_name,
        c.is_group,
        c.updated_at,
        CASE
            WHEN c.is_group = 0 THEN (
                SELECT CONCAT(u.first_name, ' ', u.last_name)
                FROM users u
                JOIN conversation_participants cp_inner ON u.id = cp_inner.user_id
                WHERE cp_inner.conversation_id = c.id AND cp_inner.user_id != ?
                LIMIT 1
            )
            ELSE c.name
        END AS display_name,
        (SELECT content FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) AS last_message,
        (SELECT created_at FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) AS last_message_time,
        (SELECT COUNT(*) FROM messages WHERE conversation_id = c.id AND sender_id != ? AND read_at IS NULL) AS unread_count
    FROM conversations c
    WHERE c.id IN (
        SELECT conversation_id FROM conversation_participants WHERE user_id = ?
    )
    ORDER BY c.updated_at DESC;
";

$conversations = [];

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("iii", $current_user_id, $current_user_id, $current_user_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $conversations[] = $row;
        }
        $stmt->close();
    } else {
        // If the query fails, it's likely because the tables don't exist yet.
        // Return an empty array to prevent frontend errors during development.
        echo json_encode(['error' => 'Could not fetch conversations. The database may not be set up yet.']);
        exit;
    }
} else {
    echo json_encode(['error' => 'Database query preparation failed.']);
    exit;
}

echo json_encode($conversations);

$conn->close();
?>
