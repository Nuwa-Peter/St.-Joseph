<?php
header('Content-Type: application/json');
require_once 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$class_id = $_GET['class_id'] ?? null;
$paper_id = $_GET['paper_id'] ?? null;

if (!$class_id || !$paper_id) {
    echo json_encode(['error' => 'Class ID and Paper ID are required.']);
    exit;
}

// Select all students in any stream belonging to the given class level,
// and left join any existing marks for the specified paper.
$sql = "
    SELECT
        u.id,
        u.first_name,
        u.last_name,
        m.score,
        su.stream_id
    FROM users u
    JOIN stream_user su ON u.id = su.user_id
    JOIN streams s ON su.stream_id = s.id
    LEFT JOIN marks m ON u.id = m.user_id AND m.paper_id = ?
    WHERE s.class_level_id = ?
    AND u.role = 'student'
    ORDER BY u.last_name, u.first_name
";

$students = [];
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ii", $paper_id, $class_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
}

echo json_encode($students);
$conn->close();
?>
