<?php
require_once __DIR__ . '/../../config.php';

// Authorization check: only students can submit
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] !== 'student') {
    header("location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $assignment_id = isset($_POST['assignment_id']) ? (int)$_POST['assignment_id'] : 0;
    $student_id = $_SESSION['id'];
    $file_path = null;
    $error = "";

    if ($assignment_id === 0) {
        // Redirect with error
        header("location: student_assignments_view.php?error=invalid_id");
        exit;
    }

    // Check if a file was uploaded
    if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] == 0) {
        $target_dir = "uploads/submissions/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        // Sanitize filename and make it unique
        $filename = "sub_" . $assignment_id . "_" . $student_id . "_" . time() . "_" . basename($_FILES["submission_file"]["name"]);
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES["submission_file"]["tmp_name"], $target_file)) {
            $file_path = $target_file;
        } else {
            $error = "Sorry, there was an error uploading your file.";
        }
    } else {
        $error = "No file was uploaded or an error occurred.";
    }

    // If file upload was successful, insert into database
    if (empty($error) && $file_path) {
        // Use INSERT IGNORE or check for existing submission to prevent duplicates
        $sql = "INSERT INTO assignment_submissions (assignment_id, student_id, file_path, submission_date)
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE file_path = VALUES(file_path), submission_date = NOW()";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iis", $assignment_id, $student_id, $file_path);
            if ($stmt->execute()) {
                // Success, redirect back to the assignment page
                header("location: assignment_submit.php?id=" . $assignment_id);
                exit();
            } else {
                $error = "Database error: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    // If there was an error, redirect back with an error message
    // A more robust solution would use session flashes for errors
    if (!empty($error)) {
        header("location: assignment_submit.php?id=" . $assignment_id . "&error=" . urlencode($error));
        exit();
    }

} else {
    // Redirect if not a POST request
    header("location: student_assignments_view.php");
    exit;
}

$conn->close();
?>
