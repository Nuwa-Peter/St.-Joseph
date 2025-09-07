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

// For now, we allow GET requests as implied by the UI's onclick link.
// A POST request from a form would be more secure against CSRF.
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($id === false) {
        $_SESSION['error_message'] = "Invalid subject ID provided.";
        header("location: " . subjects_url());
        exit;
    }

    // Prepare the delete statement
    $sql = "DELETE FROM subjects WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            // Check if a row was actually deleted
            if ($stmt->affected_rows > 0) {
                $_SESSION['success_message'] = "Subject has been deleted successfully.";
            } else {
                $_SESSION['error_message'] = "No subject found with that ID, or it was already deleted.";
            }
        } else {
            // Execution failed, likely due to a foreign key constraint
            $_SESSION['error_message'] = "Error deleting subject. It might be assigned to a class or teacher and cannot be deleted.";
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "An error occurred while preparing the delete statement.";
    }

    $conn->close();
    header("location: " . subjects_url());
    exit;

} else {
    // Invalid request method
    $_SESSION['error_message'] = "Invalid request.";
    header("location: " . subjects_url());
    exit;
}
?>
