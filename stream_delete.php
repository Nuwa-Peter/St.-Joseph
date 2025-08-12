<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'config.php';

$class_level_id = 0;
if (isset($_GET["class_level_id"]) && !empty(trim($_GET["class_level_id"]))) {
    $class_level_id = trim($_GET["class_level_id"]);
} else {
    echo "Class level ID parameter is missing.";
    exit();
}


if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id = trim($_GET["id"]);

    $sql = "DELETE FROM streams WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            header("location: streams.php?class_level_id=" . $class_level_id);
            exit();
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
        $stmt->close();
    }

    $conn->close();
} else {
    echo "ID parameter is missing.";
    exit();
}
?>
