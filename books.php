<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'config.php';
require_once 'includes/header.php';

$sql = "SELECT id, title, author, isbn, publisher, published_year, quantity, available_quantity FROM books ORDER BY title ASC";
$result = $conn->query($sql);
?>

<h2>Book Management</h2>
<a href="book_create.php" class="btn btn-success mb-3">Add New Book</a>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Title</th>
            <th>Author</th>
            <th>ISBN</th>
            <th>Publisher</th>
            <th>Year</th>
            <th>Qty</th>
            <th>Available</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row["title"]); ?></td>
                    <td><?php echo htmlspecialchars($row["author"]); ?></td>
                    <td><?php echo htmlspecialchars($row["isbn"]); ?></td>
                    <td><?php echo htmlspecialchars($row["publisher"]); ?></td>
                    <td><?php echo htmlspecialchars($row["published_year"]); ?></td>
                    <td><?php echo htmlspecialchars($row["quantity"]); ?></td>
                    <td><?php echo htmlspecialchars($row["available_quantity"]); ?></td>
                    <td>
                        <a href="book_view.php?id=<?php echo $row["id"]; ?>" class="btn btn-info btn-sm">View</a>
                        <a href="book_edit.php?id=<?php echo $row["id"]; ?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="book_delete.php?id=<?php echo $row["id"]; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this book?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="8" class="text-center">No books found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
