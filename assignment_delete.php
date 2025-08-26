<?php
session_start();
require_once 'config.php';

// Authorization check
$allowed_roles = ['teacher', 'headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: dashboard.php");
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $assignment_id = $_GET['id'];

    // First, get the assignment to check for ownership and file path
    $sql_select = "SELECT teacher_id, file_path FROM assignments WHERE id = ?";
    if ($stmt_select = $conn->prepare($sql_select)) {
        $stmt_select->bind_param("i", $assignment_id);
        $stmt_select->execute();
        $result = $stmt_select->get_result();
        $assignment = $result->fetch_assoc();
        $stmt_select->close();

        if ($assignment) {
            // Security check: only the teacher who created it or an admin can delete it
            if ($_SESSION['role'] === 'teacher' && $assignment['teacher_id'] != $_SESSION['id']) {
                // Not authorized
                header("location: assignments.php?error=unauthorized");
                exit;
            }

            // Delete the associated file if it exists
            if (!empty($assignment['file_path']) && file_exists($assignment['file_path'])) {
                unlink($assignment['file_path']);
            }

            // Delete the assignment record
            $sql_delete = "DELETE FROM assignments WHERE id = ?";
            if ($stmt_delete = $conn->prepare($sql_delete)) {
                $stmt_delete->bind_param("i", $assignment_id);
                $stmt_delete->execute();
                $stmt_delete->close();
            }
        }
    }
}

// Redirect back to the assignments list
header("location: assignments.php");
exit;

?>
