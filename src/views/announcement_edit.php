<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../src/includes/url_helper.php';
require_once __DIR__ . '/../../src/includes/csrf_helper.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: " . login_url());
    exit;
}

$title = $content = "";
$announcement_id = $_GET['id'] ?? 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verify_csrf_token();
    $title = $_POST['title'];
    $content = $_POST['content'];
    $announcement_id = $_POST['id'];

    $sql = "UPDATE announcements SET title = ?, content = ? WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssi", $title, $content, $announcement_id);

        if ($stmt->execute()) {
            header("Location: " . announcements_url());
            exit();
        } else {
            $error_message = "Database Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $error_message = "Database Error: " . $conn->error;
    }
} else {
    if ($announcement_id > 0) {
        $sql = "SELECT title, content FROM announcements WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $announcement_id);
            if ($stmt->execute()) {
                $stmt->bind_result($title, $content);
                $stmt->fetch();
            }
            $stmt->close();
        }
    }
}

require_once __DIR__ . '/../../src/includes/header.php';
?>

<div class="container mt-5">
    <h2>Edit Announcement</h2>
    <?php if(isset($error_message)): ?><div class="alert alert-danger"><?php echo $error_message; ?></div><?php endif; ?>
    <form action="<?php echo announcement_edit_url($announcement_id); ?>" method="post">
        <?php echo csrf_input(); ?>
        <input type="hidden" name="id" value="<?php echo $announcement_id; ?>">
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
        </div>
        <div class="mb-3">
            <label for="content" class="form-label">Content</label>
            <textarea class="form-control" id="content" name="content" rows="5" required><?php echo htmlspecialchars($content); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="<?php echo announcements_url(); ?>" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php
$conn->close();
require_once __DIR__ . '/../../src/includes/footer.php';
?>
