<?php
require_once 'config.php';
session_start();

require_once 'includes/url_helper.php';
require_once 'includes/csrf_helper.php';

// Check login and role for authorization
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], ['admin', 'headteacher', 'root', 'director'])) {
    header("location: " . login_url());
    exit;
}

// Allow GET requests for now, as implied by the UI's onclick link.
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($id === false) {
        $_SESSION['error_message'] = "Invalid class ID provided.";
        header("location: " . classes_url());
        exit;
    }

    // Use a transaction to ensure data integrity
    $conn->begin_transaction();

    try {
        // First, delete associated streams
        $sql_streams = "DELETE FROM streams WHERE class_level_id = ?";
        $stmt_streams = $conn->prepare($sql_streams);
        $stmt_streams->bind_param("i", $id);
        $stmt_streams->execute();
        $stmt_streams->close();

        // Then, delete the class level itself
        $sql_class = "DELETE FROM class_levels WHERE id = ?";
        $stmt_class = $conn->prepare($sql_class);
        $stmt_class->bind_param("i", $id);

        if ($stmt_class->execute()) {
            if ($stmt_class->affected_rows > 0) {
                $conn->commit();
                $_SESSION['success_message'] = "Class and all associated streams were deleted successfully.";
            } else {
                throw new Exception("No class found with the given ID.");
            }
        } else {
            throw new Exception("Error executing the delete statement for the class.");
        }
        $stmt_class->close();

    } catch (Exception $e) {
        $conn->rollback();
        // It's good practice to log the actual error for debugging
        // error_log("Error deleting class level ID $id: " . $e->getMessage());
        $_SESSION['error_message'] = "An error occurred. Could not delete the class. It might be referenced by other records (e.g., students).";
    }

    header("location: " . classes_url());
    exit;

} else {
    $_SESSION['error_message'] = "Invalid request.";
    header("location: " . classes_url());
    exit;
}
?>
