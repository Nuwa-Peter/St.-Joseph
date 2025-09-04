<?php

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'config.php';
require_once 'includes/header.php';

$title = $content = "";
$announcement_id = $_GET['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $announcement_id = $_POST['id'];

    $sql = "UPDATE announcements SET title = ?, content = ? WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssi", $title, $content, $announcement_id);

        if ($stmt->execute()) {
            header("location: announcements.php");
        } else {
            echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
        }

        $stmt->close();
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
} else {
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
?>

<div class="container mt-5">
    <h2>Edit Announcement</h2>
    <form action="announcement_edit.php" method="post">
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
        <a href="announcements.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php
$conn->close();
?>
