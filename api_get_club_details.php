<?php
require_once 'config.php';
header('Content-Type: application/json');

// Role-based access control
$admin_roles = ['root', 'headteacher'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $admin_roles)) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['error' => 'No club ID provided.']);
    exit;
}

$club_id = (int)$_GET['id'];

$sql = "SELECT id, name, description, teacher_in_charge_id FROM clubs WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $club_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $club = $result->fetch_assoc();
        echo json_encode($club);
    } else {
        echo json_encode(['error' => 'Club not found.']);
    }
    $stmt->close();
} else {
    echo json_encode(['error' => 'Database query failed.']);
}

$conn->close();
?>
