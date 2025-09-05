<?php
header('Content-Type: application/json');
require_once 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$class_id = $_GET['class_level_id'] ?? null;
if (!$class_id) {
    echo json_encode(['error' => 'Class ID is required.']);
    exit;
}

// This query should be more complex in a real scenario,
// joining with subject_teacher to get only the subjects
// a specific teacher teaches in a specific stream.
// For now, we get all subjects taught in any stream of a given class level.

$sql = "
    SELECT DISTINCT s.id, s.name
    FROM subjects s
    JOIN stream_subject ss ON s.id = ss.subject_id
    JOIN streams st ON ss.stream_id = st.id
    WHERE st.class_level_id = ?
    ORDER BY s.name
";

$subjects = [];
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
    $stmt->close();
}

echo json_encode($subjects);
$conn->close();
?>
