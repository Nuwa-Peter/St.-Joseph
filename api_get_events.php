<?php
header('Content-Type: application/json');
session_start();
require_once 'config.php';

// Allow any logged-in user to see calendar events
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Fetch all events from the database
$sql = "SELECT id, title, start_date, end_date, event_type, description FROM events";
$result = $conn->query($sql);

$events = [];
while ($row = $result->fetch_assoc()) {
    // Assign a color based on the event type for better visualization
    $color = '#007bff'; // Default blue color
    switch ($row['event_type']) {
        case 'Holiday':
            $color = '#dc3545'; // Red
            break;
        case 'Exam':
            $color = '#ffc107'; // Yellow
            break;
        case 'Meeting':
            $color = '#198754'; // Green
            break;
        case 'Sports':
            $color = '#fd7e14'; // Orange
            break;
    }

    $events[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'start' => $row['start_date'],
        'end' => $row['end_date'], // FullCalendar handles null end dates
        'color' => $color,
        'description' => $row['description'] // Custom property for display
    ];
}

echo json_encode($events);

$conn->close();
?>
