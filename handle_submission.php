<?php
require_once 'config.php';

// Authorization check: only students can submit
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] !== 'student') {
    header("location: " . dashboard_url());
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $assignment_id = isset($_POST['assignment_id']) ? (int)$_POST['assignment_id'] : 0;
    $student_id = $_SESSION['id'];
    $file_path = null;
    $error = "";

    if ($assignment_id === 0) {
        $_SESSION['error_message'] = "Invalid assignment ID.";
        header("location: " . student_assignments_view_url());
        exit;
    }

    // Check if a file was uploaded
    if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] == 0) {
        $target_dir = "uploads/submissions/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $filename = "sub_" . $assignment_id . "_" . $student_id . "_" . time() . "_" . basename($_FILES["submission_file"]["name"]);
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES["submission_file"]["tmp_name"], $target_file)) {
            $file_path = $target_file;
        } else {
            $error = "Sorry, there was an error uploading your file.";
        }
    } else {
        $error = "No file was uploaded or an error occurred during upload.";
    }

    // If file upload was successful, insert into database
    if (empty($error) && $file_path) {
        // Use INSERT ... ON DUPLICATE KEY UPDATE to handle resubmissions
        $sql = "INSERT INTO assignment_submissions (assignment_id, student_id, file_path, submission_date)
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE file_path = VALUES(file_path), submission_date = NOW(), updated_at = NOW()";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iis", $assignment_id, $student_id, $file_path);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Your assignment was submitted successfully.";
                header("location: " . url('assignment_submit.php', ['id' => $assignment_id]));
                exit();
            } else {
                $error = "Database error: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    if (!empty($error)) {
        $_SESSION['error_message'] = $error;
        header("location: " . url('assignment_submit.php', ['id' => $assignment_id]));
        exit();
    }

} else {
    header("location: " . student_assignments_view_url());
    exit;
}

$conn->close();
?>
