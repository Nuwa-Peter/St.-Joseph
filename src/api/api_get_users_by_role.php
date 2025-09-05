<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$role = $_GET['role'] ?? null;
if (!$role) {
    echo json_encode(['error' => 'Role is required.']);
    exit;
}

$allowed_roles = ['student', 'teacher', 'headteacher'];
if (!in_array($role, $allowed_roles)) {
    echo json_encode(['error' => 'Invalid role specified.']);
    exit;
}

$sql = "SELECT id, first_name, last_name FROM users WHERE role = ? ORDER BY last_name, first_name";
$users = [];

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $stmt->close();
}

echo json_encode($users);
$conn->close();
?>
