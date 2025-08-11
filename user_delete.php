<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Add role check for admin access
// if ($_SESSION['role'] !== 'root' && $_SESSION['role'] !== 'headteacher') {
//     header("location: dashboard.php");
//     exit;
// }

require_once 'config.php';

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Prevent deleting the root user
    if ($user_id == 1) {
        header("location: users.php?error=cannotdeleteadmin");
        exit;
    }

    $sql = "DELETE FROM users WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            header("location: users.php");
        } else {
            echo "Error deleting record: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}

$conn->close();
?>
