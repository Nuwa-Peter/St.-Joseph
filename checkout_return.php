<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $checkout_id = trim($_GET["id"]);
    $checked_in_by_id = $_SESSION['id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // First, get the book_id from the checkout record
        $book_id = null;
        $sql_get_book = "SELECT book_id FROM book_checkouts WHERE id = ? AND returned_date IS NULL";
        $stmt_get_book = $conn->prepare($sql_get_book);
        $stmt_get_book->bind_param("i", $checkout_id);
        $stmt_get_book->execute();
        $result = $stmt_get_book->get_result();

        if ($result->num_rows == 1) {
            $checkout = $result->fetch_assoc();
            $book_id = $checkout['book_id'];
        } else {
            throw new Exception("Checkout record not found or already returned.");
        }
        $stmt_get_book->close();

        // Update the checkout record
        $sql_return = "UPDATE book_checkouts SET returned_date = NOW(), checked_in_by_id = ? WHERE id = ?";
        $stmt_return = $conn->prepare($sql_return);
        $stmt_return->bind_param("ii", $checked_in_by_id, $checkout_id);
        $stmt_return->execute();

        // Increment the available quantity in the books table
        $sql_update_book = "UPDATE books SET available_quantity = available_quantity + 1 WHERE id = ?";
        $stmt_update_book = $conn->prepare($sql_update_book);
        $stmt_update_book->bind_param("i", $book_id);
        $stmt_update_book->execute();

        // Commit the transaction
        $conn->commit();

        header("location: checkouts.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        // In a real app, you'd want to show a proper error page
        die("ERROR: Could not process return. " . $e->getMessage());
    }

} else {
    echo "ID parameter is missing.";
    exit();
}
?>
