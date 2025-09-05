<?php
require_once 'config.php';
require_once 'includes/url_helper.php';
require_once 'includes/csrf_helper.php';

// Authorization check
$allowed_roles = ['headteacher', 'root', 'director'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: " . dashboard_url());
    exit;
}

$title = $content = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verify_csrf_token();
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
                header("Location: " . announcements_url());
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
    <form action="<?php echo announcement_create_url(); ?>" method="post">
        <?php echo csrf_input(); ?>
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
        <a href="<?php echo announcements_url(); ?>" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php
require_once 'includes/footer.php';
?>
