<?php
header('Content-Type: application/json');
require_once 'config.php';

// Basic security checks
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_GET['stream_id']) || !isset($_GET['date'])) {
    http_response_code(401); // Use 401 for Unauthorized
    echo json_encode(['error' => 'Unauthorized or missing parameters.']);
    exit;
}

// Further role-based authorization can be added here if needed
// For example, only allow teachers of that stream or admins
// $allowed_roles = ['teacher', 'headteacher', 'root'];
// if (!in_array($_SESSION['role'], $allowed_roles)) {
//     http_response_code(403); // Use 403 for Forbidden
//     echo json_encode(['error' => 'You do not have permission to perform this action.']);
//     exit;
// }

$stream_id = intval($_GET['stream_id']);
$date = $_GET['date'];

// Validate date format
if (!DateTime::createFromFormat('Y-m-d', $date)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid date format. Please use YYYY-MM-DD.']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT COUNT(id) as count FROM attendances WHERE stream_id = ? AND date = ?");
    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
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
    // Log the actual error for debugging, but don't expose it to the client
    error_log("Attendance check failed: " . $e->getMessage());
    echo json_encode(['error' => 'A database error occurred.']);
}

// The connection is often closed by a shutdown function in config.php or index.php
// If not, it's good practice to close it.
// $conn->close();
?>
