<?php

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once __DIR__ . '/../../config.php';

if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id = trim($_GET["id"]);

    // First, delete streams associated with the class level
    $sql_delete_streams = "DELETE FROM streams WHERE class_level_id = ?";
    if ($stmt_streams = $conn->prepare($sql_delete_streams)) {
        $stmt_streams->bind_param("i", $id);
        $stmt_streams->execute();
        $stmt_streams->close();
    }

    // Then, delete the class level
    $sql_delete_class_level = "DELETE FROM class_levels WHERE id = ?";
    if ($stmt_class_level = $conn->prepare($sql_delete_class_level)) {
        $stmt_class_level->bind_param("i", $id);
        if ($stmt_class_level->execute()) {
            header("location: class_levels.php");
            exit();
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
        $stmt_class_level->close();
    }

    $conn->close();
} else {
    echo "ID parameter is missing.";
    exit();
}
?>
