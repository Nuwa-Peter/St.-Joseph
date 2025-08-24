<?php
//header('Content-Type: application/json'); // Temporarily disable JSON header for debugging
require_once 'config.php';
session_start();

// --- Start Debug Output ---
header('Content-Type: text/plain');
echo "--- API DEBUG MODE ---\n\n";

// Security check: ensure user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo "Error: Unauthorized. User not logged in.\n";
    exit;
}
echo "User is logged in. Role: " . ($_SESSION['role'] ?? 'N/A') . "\n";

$stream_id = $_GET['stream_id'] ?? null;
echo "Attempting to fetch students for stream_id: " . htmlspecialchars($stream_id) . "\n";

if (!$stream_id) {
    echo "Error: Stream ID is required.\n";
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

echo "SQL Query:\n" . $sql . "\n\n";

$students = [];
if ($stmt = $conn->prepare($sql)) {
    echo "Statement prepared successfully.\n";
    $stmt->bind_param("i", $stream_id);
    echo "Parameters bound successfully (stream_id = " . htmlspecialchars($stream_id) . ").\n";

    if ($stmt->execute()) {
        echo "Statement executed successfully.\n";
        $result = $stmt->get_result();
        $num_rows = $result->num_rows;
        echo "Query returned " . $num_rows . " student(s).\n\n";

        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }

        echo "--- Found Students ---\n";
        print_r($students);

    } else {
        echo "Error: Statement execution failed: " . $stmt->error . "\n";
    }
    $stmt->close();
} else {
    echo "Error: Failed to prepare statement: " . $conn->error . "\n";
    exit;
}

$conn->close();

// Temporarily commenting out the JSON output
// echo json_encode($students);
?>
