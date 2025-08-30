<?php
// --- ONE-TIME SCRIPT TO CREATE THE STAFF GROUP CHAT ---
// --- Run this script once by navigating to it in your browser. ---
// --- It is safe to run multiple times; it will not create duplicates. ---

require_once 'config.php';
echo "<pre>"; // For readable output

$staff_group_name = 'Staff Members';

// Use a transaction
$conn->begin_transaction();

try {
    // 1. Check if the group already exists
    $find_sql = "SELECT id FROM conversations WHERE name = ? AND is_group = 1 LIMIT 1";
    $find_stmt = $conn->prepare($find_sql);
    $find_stmt->bind_param("s", $staff_group_name);
    $find_stmt->execute();
    $result = $find_stmt->get_result();
    $existing_group = $result->fetch_assoc();
    $find_stmt->close();

    $conversation_id = null;

    if ($existing_group) {
        echo "Group '{$staff_group_name}' already exists with ID: {$existing_group['id']}.\n";
        $conversation_id = $existing_group['id'];
    } else {
        // Create the conversation
        $create_convo_sql = "INSERT INTO conversations (name, is_group) VALUES (?, 1)";
        $create_convo_stmt = $conn->prepare($create_convo_sql);
        $create_convo_stmt->bind_param("s", $staff_group_name);
        $create_convo_stmt->execute();
        $conversation_id = $conn->insert_id;
        $create_convo_stmt->close();
        echo "Successfully created group '{$staff_group_name}' with ID: {$conversation_id}.\n";
    }

    // 2. Find all staff members (anyone not a student)
    $staff_sql = "SELECT id FROM users WHERE role != 'student'";
    $staff_result = $conn->query($staff_sql);
    $staff_ids = [];
    while ($row = $staff_result->fetch_assoc()) {
        $staff_ids[] = (int)$row['id'];
    }
    echo "Found " . count($staff_ids) . " staff members to add.\n";

    // 3. Add all staff members as participants, ignoring duplicates
    if (!empty($staff_ids)) {
        $added_count = 0;
        $add_users_sql = "INSERT IGNORE INTO conversation_participants (conversation_id, user_id) VALUES (?, ?)";
        $add_users_stmt = $conn->prepare($add_users_sql);

        foreach ($staff_ids as $staff_id) {
            $add_users_stmt->bind_param("ii", $conversation_id, $staff_id);
            $add_users_stmt->execute();
            if ($add_users_stmt->affected_rows > 0) {
                $added_count++;
            }
        }
        $add_users_stmt->close();
        echo "Added {$added_count} new members to the group.\n";
    }

    $conn->commit();
    echo "\nSUCCESS: Staff group setup is complete.\n";

} catch (Exception $e) {
    $conn->rollback();
    echo "An error occurred: " . $e->getMessage() . "\n";
}

echo "</pre>";
$conn->close();
?>
