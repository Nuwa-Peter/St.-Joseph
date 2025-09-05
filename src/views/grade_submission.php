<?php
require_once __DIR__ . '/../../config.php';

// Authorization check
$allowed_roles = ['teacher', 'headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $submission_id = isset($_POST['submission_id']) ? (int)$_POST['submission_id'] : 0;
    $assignment_id = isset($_POST['assignment_id']) ? (int)$_POST['assignment_id'] : 0; // For redirecting
    $grade = trim($_POST['grade']);
    $feedback = trim($_POST['feedback']);

    if ($submission_id > 0 && $assignment_id > 0) {
        // Optional: Add a security check here to ensure the teacher is authorized to grade this particular submission
        // (e.g., check if the teacher owns the parent assignment). For now, we rely on the session role check.

        $sql = "UPDATE assignment_submissions SET grade = ?, feedback = ?, updated_at = NOW() WHERE id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssi", $grade, $feedback, $submission_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Redirect back to the submissions page
    header("location: assignment_submissions.php?id=" . $assignment_id);
    exit;
} else {
    // Redirect if not a POST request
    header("location: assignments.php");
    exit;
}

$conn->close();
?>
