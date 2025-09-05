<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';

$streams = [];
$class_level_id = isset($_GET['class_level_id']) ? (int)$_GET['class_level_id'] : 0;

if ($class_level_id > 0) {
    $sql = "SELECT id, name FROM streams WHERE class_level_id = ? ORDER BY name ASC";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $class_level_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $streams[] = $row;
            }
        }
        $stmt->close();
    }
}

echo json_encode($streams);

$conn->close();
?>
