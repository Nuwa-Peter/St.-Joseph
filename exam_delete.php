<?php
require_once 'config.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], ['root', 'headteacher'])) {
    header("location: login.php"); // Or show an unauthorized message
    exit;
}

$paper_id = $_GET['id'] ?? null;

if (!$paper_id) {
    header("location: set_exam.php?error=No ID provided");
    exit;
}

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

    // Then, delete the paper itself
    $sql_delete_paper = "DELETE FROM papers WHERE id = ?";
    if ($stmt_paper = $conn->prepare($sql_delete_paper)) {
        $stmt_paper->bind_param("i", $paper_id);
        $stmt_paper->execute();
        $stmt_paper->close();
    } else {
        throw new Exception("Failed to prepare statement for deleting the paper.");
    }

    $conn->commit();
    header("location: set_exam.php?success=Exam deleted successfully");
} catch (Exception $e) {
    $conn->rollback();
    header("location: set_exam.php?error=Failed to delete exam: " . urlencode($e->getMessage()));
}

$conn->close();
?>
