<?php
session_start();
header('Content-Type: application/json');

// All logged-in users can search, but we must be connected to the db
require_once 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$search_query = $_GET['q'] ?? '';
$results = [];

// Only search if the query is not empty and has a reasonable length
if (strlen(trim($search_query)) > 1) {
    $sql = "SELECT
                id,
                first_name,
                last_name,
                role,
                photo
            FROM
                users
            WHERE
                first_name LIKE ? OR
                last_name LIKE ? OR
                username LIKE ? OR
                email LIKE ? OR
                lin LIKE ?
            LIMIT 10"; // Limit to 10 suggestions for performance

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $search_term = "%{$search_query}%";
        $stmt->bind_param("sssss", $search_term, $search_term, $search_term, $search_term, $search_term);

        $stmt->execute();
        $result = $stmt->get_result();
        $results = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}

echo json_encode($results);
$conn->close();
exit;
?>
