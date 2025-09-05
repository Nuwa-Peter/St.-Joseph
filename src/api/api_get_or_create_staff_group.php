<?php
header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['error' => 'User not logged in.']);
    exit;
}

require_once __DIR__ . '/../../config.php';

$staff_group_name = 'Staff Members';
$conversation_id = null;

// Use a transaction to ensure atomicity
$conn->begin_transaction();

try {
    // 1. Check if the group already exists
    $find_sql = "SELECT id FROM conversations WHERE name = ? AND is_group = 1 LIMIT 1";
    $find_stmt = $conn->prepare($find_sql);
    $find_stmt->bind_param("s", $staff_group_name);
    $find_stmt->execute();
    $find_stmt->bind_result($conversation_id);
    $find_stmt->fetch();
    $find_stmt->close();

    // 2. If it doesn't exist, create it
    if (!$conversation_id) {
        // Create the conversation
        $create_convo_sql = "INSERT INTO conversations (name, is_group) VALUES (?, 1)";
        $create_convo_stmt = $conn->prepare($create_convo_sql);
        $create_convo_stmt->bind_param("s", $staff_group_name);
        $create_convo_stmt->execute();
        $conversation_id = $conn->insert_id;
        $create_convo_stmt->close();

        // Find all staff members (anyone not a student)
        // Note: This could be refined if there are other non-staff roles like 'parent'.
        $staff_sql = "SELECT id FROM users WHERE role != 'student'";
        $staff_result = $conn->query($staff_sql);
        $staff_ids = [];
        while ($row = $staff_result->fetch_assoc()) {
            $staff_ids[] = (int)$row['id'];
        }

        // Add all staff members as participants
        if (!empty($staff_ids)) {
            $values_placeholder = implode(', ', array_fill(0, count($staff_ids), "(?, ?)"));
            $add_users_sql = "INSERT INTO conversation_participants (conversation_id, user_id) VALUES $values_placeholder";
            $add_users_stmt = $conn->prepare($add_users_sql);

            $params = [];
            foreach ($staff_ids as $staff_id) {
                $params[] = $conversation_id;
                $params[] = $staff_id;
            }
            // Create the type string (e.g., 'ii...')
            $types = str_repeat('i', count($params));
            $add_users_stmt->bind_param($types, ...$params);
            $add_users_stmt->execute();
            $add_users_stmt->close();
        }
    }

    $conn->commit();
    echo json_encode(['success' => true, 'conversation_id' => $conversation_id]);

} catch (Exception $e) {
    $conn->rollback();
    error_log('Staff group creation failed: ' . $e->getMessage());
    echo json_encode(['error' => 'Could not get or create staff group chat.']);
}

$conn->close();
?>
