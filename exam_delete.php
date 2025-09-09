<?php
require_once 'config.php';
session_start();
require_once 'includes/url_helper.php';
require_once 'includes/csrf_helper.php';

// Authentication and Authorization
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: " . login_url());
    exit;
}
if (!in_array($_SESSION['role'], ['admin', 'headteacher', 'root', 'director'])) {
    $_SESSION['error_message'] = "You are not authorized to perform this action.";
    header("location: " . dashboard_url());
    exit;
}

// For now, we allow GET requests as implied by the UI's onclick link.
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $paper_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($paper_id === false) {
        $_SESSION['error_message'] = "Invalid exam ID provided.";
        header("location: " . set_exam_url());
        exit;
    }

    // Use a transaction to ensure data integrity
    $conn->begin_transaction();

    try {
        // First, delete associated marks to maintain referential integrity
        $sql_delete_marks = "DELETE FROM marks WHERE paper_id = ?";
        if ($stmt_marks = $conn->prepare($sql_delete_marks)) {
            $stmt_marks->bind_param("i", $paper_id);
            $stmt_marks->execute();
            $stmt_marks->close();
        } else {
            throw new Exception("Failed to prepare statement for deleting marks.");
        }

        // Then, delete the paper (exam) itself
        $sql_delete_paper = "DELETE FROM papers WHERE id = ?";
        if ($stmt_paper = $conn->prepare($sql_delete_paper)) {
            $stmt_paper->bind_param("i", $paper_id);
            $stmt_paper->execute();
            if ($stmt_paper->affected_rows > 0) {
                 $_SESSION['success_message'] = "Exam and all associated marks have been deleted successfully.";
            } else {
                 $_SESSION['error_message'] = "No exam found with that ID.";
            }
            $stmt_paper->close();
        } else {
            throw new Exception("Failed to prepare statement for deleting the exam.");
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Failed to delete exam: An error occurred.";
        // error_log($e->getMessage());
    }

    header("location: " . set_exam_url());
    exit;
} else {
    $_SESSION['error_message'] = "Invalid request.";
    header("location: " . set_exam_url());
    exit;
}
?>
