<?php
header('Content-Type: application/json');
session_start();
require_once 'config.php';

// Basic security checks
if (!isset($_SESSION["loggedin"]) || !isset($_GET['stream_id']) || !isset($_GET[
'date'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Unauthorized or missing parameters.']);
    exit;
}

$stream_id = intval($_GET['stream_id']);
$date = $_GET['date'];

// Validate date format
if (!DateTime::createFromFormat('Y-m-d', $date)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid date format.']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT COUNT(id) as count FROM attendances WHERE stream_id = ? AND date = ?");
    $stmt->bind_param("is", $stream_id, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row['count'] > 0) {
        echo json_encode(['status' => 'taken']);
    } else {
        echo json_encode(['status' => 'not_taken']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database query failed.']);
}

$conn->close();
?>
