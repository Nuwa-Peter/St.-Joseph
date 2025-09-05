<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../src/includes/header.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$book = null;
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $book_id = trim($_GET["id"]);

    $sql = "SELECT * FROM books WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $book_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $book = $result->fetch_assoc();
            } else {
                echo "<div class='alert alert-danger'>No book found with that ID.</div>";
                exit();
            }
        } else {
            echo "<div class='alert alert-danger'>Oops! Something went wrong. Please try again later.</div>";
            exit();
        }
        $stmt->close();
    }
} else {
    echo "<div class='alert alert-danger'>ID parameter is missing from the request.</div>";
    exit();
}
$conn->close();
?>

<h2>View Book Details</h2>

<?php if ($book): ?>
<div class="card">
    <div class="card-header">
        <h3><?php echo htmlspecialchars($book['title']); ?></h3>
    </div>
    <div class="card-body">
        <h5 class="card-title">By <?php echo htmlspecialchars($book['author']); ?></h5>
        <p class="card-text">
            <strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn']); ?><br>
            <strong>Publisher:</strong> <?php echo htmlspecialchars($book['publisher']); ?><br>
            <strong>Published Year:</strong> <?php echo htmlspecialchars($book['published_year']); ?>
        </p>
        <hr>
        <p class="card-text">
            <strong>Total Quantity:</strong> <?php echo htmlspecialchars($book['quantity']); ?><br>
            <strong>Available Quantity:</strong> <?php echo htmlspecialchars($book['available_quantity']); ?>
        </p>
    </div>
    <div class="card-footer text-muted">
        <a href="books.php" class="btn btn-primary">Back to Book List</a>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../src/includes/footer.php'; ?>
