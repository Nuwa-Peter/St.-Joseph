<?php
// This script is intended to be run from the command line via a cron job.
// Example: 0 1 * * * /usr/bin/php /path/to/your/project/cron_delete_unregistered.php

require_once 'config.php';

echo "Cron Job: Deleting unregistered students older than 60 days...\n";

// Calculate the date 60 days ago
$cutoff_date = date('Y-m-d H:i:s', strtotime('-60 days'));

// Find users to delete
$sql_find = "SELECT id, first_name, last_name FROM users WHERE status = 'unregistered' AND status_changed_at < ?";
$users_to_delete = [];
if ($stmt_find = $conn->prepare($sql_find)) {
    $stmt_find->bind_param("s", $cutoff_date);
    $stmt_find->execute();
    $result = $stmt_find->get_result();
    while ($row = $result->fetch_assoc()) {
        $users_to_delete[] = $row;
    }
    $stmt_find->close();
}

if (empty($users_to_delete)) {
    echo "No users to delete.\n";
    $conn->close();
    exit;
}

// Delete the users
$deleted_count = 0;
$sql_delete = "DELETE FROM users WHERE id = ?";
if ($stmt_delete = $conn->prepare($sql_delete)) {
    foreach ($users_to_delete as $user) {
        $stmt_delete->bind_param("i", $user['id']);
        if ($stmt_delete->execute()) {
            echo "Deleted user: " . $user['first_name'] . " " . $user['last_name'] . " (ID: " . $user['id'] . ")\n";
            $deleted_count++;
        } else {
            echo "Failed to delete user ID: " . $user['id'] . "\n";
        }
    }
    $stmt_delete->close();
}

echo "----------------------------------------\n";
echo "Cron job finished. Deleted " . $deleted_count . " users.\n";

$conn->close();
?>
