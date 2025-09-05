<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';

// Security check: ensure user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$stream_id = $_GET['stream_id'] ?? null;

if (!$stream_id) {
    echo json_encode(['error' => 'Stream ID is required.']);
    exit;
}

// Select all students in the given stream
$sql = "
    SELECT
        u.id,
        u.first_name,
        u.last_name,
        u.unique_id
    FROM users u
    JOIN stream_user su ON u.id = su.user_id
    WHERE su.stream_id = ?
    AND u.role = 'student'
    ORDER BY u.last_name, u.first_name
";

$students = [];
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $stream_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
} else {
    // In a production environment, log this error instead of echoing it.
    error_log("Failed to prepare statement: " . $conn->error);
    echo json_encode(['error' => 'A database error occurred.']);
    exit;
}

echo json_encode($students);
$conn->close();
?>
