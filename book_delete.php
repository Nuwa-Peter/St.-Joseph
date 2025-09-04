<?php
require_once 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $book_id = trim($_GET["id"]);

    // A robust implementation would also check for existing checkouts before deleting.
    // For now, we will just delete the book record.

    $sql = "DELETE FROM books WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $book_id);

        if ($stmt->execute()) {
            header("location: books.php");
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
