<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$title = $content = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['id'];

    if (empty($title)) {
        $errors['title'] = "Title is required.";
    }
    if (empty($content)) {
        $errors['content'] = "Content is required.";
    }

    if (empty($errors)) {
        $sql = "INSERT INTO announcements (title, content, user_id, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssi", $title, $content, $user_id);

            if ($stmt->execute()) {
                header("location: announcements.php");
                exit();
            } else {
                $errors['db'] = "Database Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $errors['db'] = "Database Error: " . $conn->error;
        }
    }
    $conn->close();
}

require_once 'includes/header.php';
?>

<div class="container mt-5">
    <h2>Create Announcement</h2>
    <?php if(isset($errors['db'])): ?><div class="alert alert-danger"><?php echo $errors['db']; ?></div><?php endif; ?>
    <form action="announcement_create.php" method="post">
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control <?php echo isset($errors['title']) ? 'is-invalid' : ''; ?>" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>">
            <?php if(isset($errors['title'])): ?><div class="invalid-feedback"><?php echo $errors['title']; ?></div><?php endif; ?>
        </div>
        <div class="mb-3">
            <label for="content" class="form-label">Content</label>
            <textarea class="form-control <?php echo isset($errors['content']) ? 'is-invalid' : ''; ?>" id="content" name="content" rows="5"><?php echo htmlspecialchars($content); ?></textarea>
            <?php if(isset($errors['content'])): ?><div class="invalid-feedback"><?php echo $errors['content']; ?></div><?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
        <a href="announcements.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php
require_once 'includes/footer.php';
?>
