<?php
require_once 'config.php';

// Authorization check
$allowed_roles = ['librarian', 'root', 'headteacher', 'admin'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: " . login_url());
    exit;
}

// Handle new checkout submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['checkout'])) {
    $book_id = $_POST['book_id'];
    $user_id = $_POST['user_id'];
    $due_date = $_POST['due_date'];
    $checked_out_by_id = $_SESSION['id'];

    $conn->begin_transaction();
    try {
        $sql_checkout = "INSERT INTO book_checkouts (book_id, user_id, checkout_date, due_date, checked_out_by_id) VALUES (?, ?, NOW(), ?, ?)";
        $stmt_checkout = $conn->prepare($sql_checkout);
        $stmt_checkout->bind_param("iisi", $book_id, $user_id, $due_date, $checked_out_by_id);
        $stmt_checkout->execute();

        $sql_update_book = "UPDATE books SET available_quantity = available_quantity - 1 WHERE id = ? AND available_quantity > 0";
        $stmt_update_book = $conn->prepare($sql_update_book);
        $stmt_update_book->bind_param("i", $book_id);
        $stmt_update_book->execute();

        if ($stmt_update_book->affected_rows > 0) {
            $conn->commit();
            $_SESSION['success_message'] = "Book checked out successfully.";
        } else {
            throw new Exception("Book not available or does not exist.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Checkout failed: " . $e->getMessage();
    }
    header("Location: " . checkouts_url());
    exit();
}

// Fetch session messages
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Fetch data for forms and lists
$students_sql = "SELECT id, first_name, last_name FROM users WHERE role = 'student' ORDER BY first_name";
$students_result = $conn->query($students_sql);

$books_sql = "SELECT id, title FROM books WHERE available_quantity > 0 ORDER BY title";
$books_result = $conn->query($books_sql);

$active_checkouts_sql = "
    SELECT bc.id, b.title, u.first_name, u.last_name, bc.checkout_date, bc.due_date
    FROM book_checkouts bc
    JOIN books b ON bc.book_id = b.id
    JOIN users u ON bc.user_id = u.id
    WHERE bc.returned_date IS NULL
    ORDER BY bc.due_date ASC
";
$active_checkouts_result = $conn->query($active_checkouts_sql);

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center my-4">
        <h2 class="text-primary"><i class="bi bi-journal-arrow-up me-2"></i>Manage Checkouts</h2>
    </div>

    <?php if ($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
    <?php if ($error_message): ?><div class="alert alert-danger"><?php echo $error_message; ?></div><?php endif; ?>

    <div class="card mb-4 shadow-sm">
        <div class="card-header">New Checkout</div>
        <div class="card-body">
            <form action="<?php echo checkouts_url(); ?>" method="post">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="book_id" class="form-label">Book</label>
                        <select name="book_id" id="book_id" class="form-select" required>
                            <option value="">Select a book...</option>
                            <?php if($books_result) { while($book = $books_result->fetch_assoc()): ?>
                                <option value="<?php echo $book['id']; ?>"><?php echo htmlspecialchars($book['title']); ?></option>
                            <?php endwhile; } ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="user_id" class="form-label">Student</label>
                        <select name="user_id" id="user_id" class="form-select" required>
                            <option value="">Select a student...</option>
                            <?php if($students_result) { while($student = $students_result->fetch_assoc()): ?>
                                <option value="<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></option>
                            <?php endwhile; } ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="due_date" class="form-label">Due Date</label>
                        <input type="date" name="due_date" id="due_date" class="form-control" value="<?php echo date('Y-m-d', strtotime('+2 weeks')); ?>" required>
                    </div>
                    <div class="col-md-1 align-self-end mb-3">
                        <button type="submit" name="checkout" class="btn btn-primary w-100">Go</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">Active Checkouts</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Book Title</th>
                            <th>Student</th>
                            <th>Checkout Date</th>
                            <th>Due Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($active_checkouts_result && $active_checkouts_result->num_rows > 0): ?>
                            <?php while($row = $active_checkouts_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row["title"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["first_name"] . ' ' . $row["last_name"]); ?></td>
                                    <td><?php echo date("d M Y", strtotime($row["checkout_date"])); ?></td>
                                    <td><?php echo date("d M Y", strtotime($row["due_date"])); ?></td>
                                    <td>
                                        <a href="<?php echo checkout_return_url($row["id"]); ?>" class="btn btn-success btn-sm" onclick="return confirm('Are you sure this book has been returned?');">Mark as Returned</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No active checkouts.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
