<?php
header('Content-Type: application/json');
require_once 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$subject_id = $_GET['subject_id'] ?? null;
if (!$subject_id) {
    echo json_encode(['error' => 'Subject ID is required.']);
    exit;
}

$sql = "SELECT id, name, exam_type FROM papers WHERE subject_id = ? ORDER BY name";

$papers = [];
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $subject_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $papers[] = $row;
    }
    $stmt->close();
}

echo json_encode($papers);
$conn->close();
?>
