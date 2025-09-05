<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../src/includes/url_helper.php';
require_once __DIR__ . '/../../src/includes/csrf_helper.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: " . login_url());
    exit;
}

$title = $author = $isbn = $publisher = $published_year = $quantity = "";
$errors = [];
$book_id = 0;

if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $book_id = trim($_GET["id"]);

    $sql = "SELECT * FROM books WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $book_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $book = $result->fetch_assoc();
                $title = $book['title'];
                $author = $book['author'];
                $isbn = $book['isbn'];
                $publisher = $book['publisher'];
                $published_year = $book['published_year'];
                $quantity = $book['quantity'];
            } else {
                // Redirect or show error if book not found
                header("Location: " . library_url());
                exit();
            }
        } else {
            die("Oops! Something went wrong. Please try again later.");
        }
        $stmt->close();
    }
} else {
    // Redirect if ID is missing
    header("Location: " . library_url());
    exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verify_csrf_token();
    $book_id = $_POST['id'];
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $isbn = trim($_POST['isbn']);
    $publisher = trim($_POST['publisher']);
    $published_year = trim($_POST['published_year']);
    $quantity = trim($_POST['quantity']);

    if (empty($title)) {
        $errors['title'] = "Title is required.";
    }
    if (empty($author)) {
        $errors['author'] = "Author is required.";
    }
    if (empty($quantity) || !is_numeric($quantity) || $quantity < 0) {
        $errors['quantity'] = "A valid quantity is required.";
    }
    if (!empty($published_year) && (!is_numeric($published_year) || strlen($published_year) != 4)) {
        $errors['published_year'] = "A valid 4-digit year is required.";
    }

    if (empty($errors)) {
        $sql = "UPDATE books SET title = ?, author = ?, isbn = ?, publisher = ?, published_year = ?, quantity = ?, updated_at = NOW() WHERE id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssiii", $title, $author, $isbn, $publisher, $published_year, $quantity, $book_id);

            if ($stmt->execute()) {
                header("Location: " . library_url());
                exit();
            } else {
                $errors['db'] = "Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
}
$conn->close();

require_once __DIR__ . '/../../src/includes/header.php';
?>

<h2>Edit Book</h2>
<p>Update the book's details below.</p>

<form action="<?php echo book_edit_url($book_id); ?>" method="post">
    <?php echo csrf_input(); ?>
    <input type="hidden" name="id" value="<?php echo $book_id; ?>"/>
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

    <?php if(isset($errors['db'])): ?>
        <div class="alert alert-danger"><?php echo $errors['db']; ?></div>
    <?php endif; ?>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary">Update Book</button>
        <a href="<?php echo library_url(); ?>" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php require_once __DIR__ . '/../../src/includes/footer.php'; ?>
