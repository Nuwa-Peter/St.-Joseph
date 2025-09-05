<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';

$lin = isset($_GET['lin']) ? trim($_GET['lin']) : '';
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if (empty($lin)) {
    echo json_encode(['unique' => true, 'message' => '']); // Don't check empty strings
    exit;
}

$sql = "SELECT id FROM users WHERE lin = ?";
$params = [$lin];
$types = "s";

if ($user_id > 0) {
    $sql .= " AND id != ?";
    $params[] = $user_id;
    $types .= "i";
}

$is_unique = true;

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $is_unique = false;
    }
    $stmt->close();
}

echo json_encode(['unique' => $is_unique]);

$conn->close();
?>
