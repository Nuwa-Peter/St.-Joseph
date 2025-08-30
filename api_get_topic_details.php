<?php
header('Content-Type: application/json');
require_once 'config.php';
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$topic_id = $_GET['topic_id'] ?? null;

if (!$topic_id) {
    echo json_encode(['error' => 'Topic ID is required.']);
    exit;
}

$details = [
    'outcomes' => [],
    'activities' => []
];

// Fetch learning outcomes (This is a placeholder, as we haven't parsed this data yet)
// In a real scenario, you would query the `curriculum_learning_outcomes` table.
$details['outcomes'][] = ['outcome_text' => 'Demonstrate understanding of the topic.'];
$details['outcomes'][] = ['outcome_text' => 'Apply knowledge in practical scenarios.'];


// Fetch activities (This is also a placeholder)
// In a real scenario, you would query the `curriculum_activities` table.
$details['activities'][] = [
    'activity_title' => 'Sample Activity 1: Group Discussion',
    'instructions' => 'In groups, discuss the key concepts of the topic.',
    'possible_responses' => 'A list of potential discussion points.'
];
$details['activities'][] = [
    'activity_title' => 'Sample Activity 2: Practical Exercise',
    'instructions' => 'Complete the provided worksheet for this topic.',
    'possible_responses' => 'N/A'
];


// Note: The above data is placeholder because the main processing script was simplified
// to only insert topics. A more advanced script would also parse and insert these details.
// However, this API structure is now ready for when that data becomes available.

echo json_encode($details);
$conn->close();
?>
