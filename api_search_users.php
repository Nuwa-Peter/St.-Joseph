<?php
header('Content-Type: application/json');
require_once 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$role = $_GET['role'] ?? null;
$query = $_GET['query'] ?? '';

if (!$role) {
    echo json_encode(['error' => 'Role is required.']);
    exit;
}

$allowed_roles = ['student', 'teacher', 'headteacher'];
if (!in_array($role, $allowed_roles)) {
    echo json_encode(['error' => 'Invalid role specified.']);
    exit;
}

if (strlen($query) < 2) {
    echo json_encode([]); // Return empty array if query is too short
    exit;
}

$sql = "
    SELECT id, first_name, last_name, lin
    FROM users
    WHERE
        (first_name LIKE ? OR last_name LIKE ? OR lin LIKE ?)
        AND role = ?
    ORDER BY last_name, first_name
    LIMIT 10
";

$users = [];
if ($stmt = $conn->prepare($sql)) {
    $search_term = "%" . $query . "%";
    $stmt->bind_param("ssss", $search_term, $search_term, $search_term, $role);
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
