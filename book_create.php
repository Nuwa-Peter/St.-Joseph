<?php
require_once 'config.php';

// Authorization check
$allowed_roles = ['librarian', 'root', 'headteacher', 'admin'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: " . login_url());
    exit;
}

$title = $author = $isbn = $publisher = $published_year = $quantity = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $isbn = trim($_POST['isbn']);
    $publisher = trim($_POST['publisher']);
    $published_year = trim($_POST['published_year']);
    $quantity = trim($_POST['quantity']);

    if (empty($title)) $errors['title'] = "Title is required.";
    if (empty($author)) $errors['author'] = "Author is required.";
    if (empty($quantity) || !is_numeric($quantity) || $quantity < 0) $errors['quantity'] = "A valid quantity is required.";
    if (!empty($published_year) && (!is_numeric($published_year) || strlen($published_year) != 4)) $errors['published_year'] = "A valid 4-digit year is required.";

    if (empty($errors)) {
        $sql = "INSERT INTO books (title, author, isbn, publisher, published_year, quantity, available_quantity, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssiii", $title, $author, $isbn, $publisher, $published_year, $quantity, $quantity);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Book added successfully.";
                header("location: " . library_url());
                exit();
            } else {
                $errors['db'] = "Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-book-half me-2"></i>Add New Book</h2>
        <a href="<?php echo library_url(); ?>" class="btn btn-secondary">Back to Library</a>
    </div>

    <form action="<?php echo book_create_url(); ?>" method="post">
        <div class="card shadow-sm">
            <div class="card-body">
                <p>Fill out the form to add a new book to the library.</p>
                <?php if(isset($errors['db'])): ?><div class="alert alert-danger"><?php echo $errors['db']; ?></div><?php endif; ?>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" name="title" class="form-control <?php echo isset($errors['title']) ? 'is-invalid' : ''; ?>" id="title" value="<?php echo htmlspecialchars($title); ?>">
                        <?php if(isset($errors['title'])): ?><div class="invalid-feedback"><?php echo $errors['title']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="author" class="form-label">Author</label>
                        <input type="text" name="author" class="form-control <?php echo isset($errors['author']) ? 'is-invalid' : ''; ?>" id="author" value="<?php echo htmlspecialchars($author); ?>">
                        <?php if(isset($errors['author'])): ?><div class="invalid-feedback"><?php echo $errors['author']; ?></div><?php endif; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="isbn" class="form-label">ISBN</label>
                        <input type="text" name="isbn" class="form-control" id="isbn" value="<?php echo htmlspecialchars($isbn); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="publisher" class="form-label">Publisher</label>
                        <input type="text" name="publisher" class="form-control" id="publisher" value="<?php echo htmlspecialchars($publisher); ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="published_year" class="form-label">Published Year</label>
                        <input type="text" name="published_year" class="form-control <?php echo isset($errors['published_year']) ? 'is-invalid' : ''; ?>" id="published_year" value="<?php echo htmlspecialchars($published_year); ?>">
                        <?php if(isset($errors['published_year'])): ?><div class="invalid-feedback"><?php echo $errors['published_year']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-control <?php echo isset($errors['quantity']) ? 'is-invalid' : ''; ?>" id="quantity" value="<?php echo htmlspecialchars($quantity); ?>">
                        <?php if(isset($errors['quantity'])): ?><div class="invalid-feedback"><?php echo $errors['quantity']; ?></div><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Add Book</button>
            <a href="<?php echo library_url(); ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
