<?php
require_once 'config.php';

// Authorization check
$allowed_roles = ['teacher', 'headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: " . dashboard_url());
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $submissions = $_POST['submissions'] ?? [];
    $assignment_id = isset($_POST['assignment_id']) ? (int)$_POST['assignment_id'] : 0;

    if (!empty($submissions) && $assignment_id > 0) {
        $conn->begin_transaction();
        try {
            $sql = "UPDATE assignment_submissions SET grade = ?, feedback = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);

            foreach ($submissions as $submission_id => $data) {
                $grade = trim($data['grade']);
                $feedback = trim($data['feedback']);
                $id = (int)$data['id'];

                // Add a security check here to ensure the submission belongs to the assignment
                // This is a simplified version. A more robust check might be needed.

                $stmt->bind_param("ssi", $grade, $feedback, $id);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update submission ID: $id");
                }
            }
            $stmt->close();
            $conn->commit();
            $_SESSION['success_message'] = "Grades and feedback saved successfully.";

        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "No submission data received.";
    }

    // Redirect back to the submissions page
    header("location: " . assignment_submissions_url(['id' => $assignment_id]));
    exit;
} else {
    // Redirect if not a POST request
    header("location: " . assignments_url());
    exit;
}

$conn->close();
?>
