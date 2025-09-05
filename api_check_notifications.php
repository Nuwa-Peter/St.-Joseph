<?php
header('Content-Type: application/json');

require_once 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['id'];
$response = [
    'unread_count' => 0,
    'notifications' => []
];

// Get unread count
$count_stmt = $conn->prepare("SELECT COUNT(id) as unread_count FROM app_notifications WHERE user_id = ? AND is_read = 0");
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result()->fetch_assoc();
$response['unread_count'] = $count_result['unread_count'] ?? 0;
$count_stmt->close();

// Get recent unread notifications
$notifications_stmt = $conn->prepare("SELECT id, message, link, created_at FROM app_notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 5");
$notifications_stmt->bind_param("i", $user_id);
$notifications_stmt->execute();
$result = $notifications_stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $response['notifications'][] = $row;
}
$notifications_stmt->close();


// --- Optional: Mark notifications as read if a specific flag is passed ---
if (isset($_GET['mark_as_read']) && $_GET['mark_as_read'] == 'true') {
    // This part is for when the user clicks the bell, we'll mark them as read.
    // We get the IDs from the notifications we just fetched.
    $notification_ids = array_column($response['notifications'], 'id');
    if (!empty($notification_ids)) {
        $ids_placeholder = implode(',', array_fill(0, count($notification_ids), '?'));
        $types = str_repeat('i', count($notification_ids));

        $update_stmt = $conn->prepare("UPDATE app_notifications SET is_read = 1 WHERE user_id = ? AND id IN ($ids_placeholder)");
        $params = array_merge([$user_id], $notification_ids);
        $update_stmt->bind_param("i" . $types, ...$params);
        $update_stmt->execute();
        $update_stmt->close();
    }
}


echo json_encode($response);
$conn->close();
exit;
?>
