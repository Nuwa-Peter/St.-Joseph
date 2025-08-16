<?php
header('Content-Type: application/json');
require_once 'config.php';
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['error' => 'Unauthorized', 'success' => false]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['error' => 'Invalid request method.', 'success' => false]);
    exit;
}

$paper_id = $_POST['paper_id'] ?? null;
$student_ids = $_POST['student_ids'] ?? [];
$stream_ids = $_POST['stream_ids'] ?? [];
$scores = $_POST['scores'] ?? [];

if (!$paper_id || empty($student_ids) || count($student_ids) !== count($scores) || count($student_ids) !== count($stream_ids)) {
    echo json_encode(['error' => 'Invalid or incomplete data submitted.', 'success' => false]);
    exit;
}

$conn->begin_transaction();

try {
    // Using INSERT ... ON DUPLICATE KEY UPDATE requires a unique key on the columns that define a unique record.
    // The key on (user_id, paper_id, stream_id) in the `marks` table allows this.
    $sql = "
        INSERT INTO marks (user_id, paper_id, stream_id, score, created_at, updated_at)
        VALUES (?, ?, ?, ?, NOW(), NOW())
        ON DUPLICATE KEY UPDATE score = VALUES(score), updated_at = NOW()
    ";

    $stmt = $conn->prepare($sql);

    for ($i = 0; $i < count($student_ids); $i++) {
        $student_id = $student_ids[$i];
        $stream_id = $stream_ids[$i];
        // Allow empty scores to be saved as NULL
        $score = $scores[$i] !== '' ? (int)$scores[$i] : null;

        // Basic validation for score
        if ($score !== null && ($score < 0 || $score > 100)) {
            // Skip invalid scores, or you could throw an error
            continue;
        }

        $stmt->bind_param("iiis", $student_id, $paper_id, $stream_id, $score);
        $stmt->execute();
    }

    $stmt->close();
    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Marks saved successfully.']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['error' => 'Database error: ' . $e->getMessage(), 'success' => false]);
}

$conn->close();
?>
