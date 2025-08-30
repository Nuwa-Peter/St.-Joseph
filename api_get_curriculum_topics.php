<?php
header('Content-Type: application/json');
require_once 'config.php';
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$class_level_id = $_GET['class_level_id'] ?? null;
$subject_id = $_GET['subject_id'] ?? null;

if (!$class_level_id || !$subject_id) {
    echo json_encode(['error' => 'Class Level ID and Subject ID are required.']);
    exit;
}

$sql = "SELECT id, title, theme FROM curriculum_topics WHERE class_level_id = ? AND subject_id = ? ORDER BY title ASC";

$topics = [];
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ii", $class_level_id, $subject_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $topics[] = $row;
    }
    $stmt->close();
}

echo json_encode($topics);
$conn->close();
?>
