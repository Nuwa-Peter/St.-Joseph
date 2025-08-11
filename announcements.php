<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'config.php';
require_once 'includes/header.php';

$sql = "SELECT announcements.id, announcements.title, announcements.content, announcements.created_at, users.name
        FROM announcements
        JOIN users ON announcements.user_id = users.id
        ORDER BY announcements.created_at DESC";

$result = $conn->query($sql);
?>

<h2>Announcements</h2>
<a href="announcement_create.php" class="btn btn-success mb-3">Create Announcement</a>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Title</th>
            <th>Content</th>
            <th>Author</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row["title"]); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($row["content"])); ?></td>
                    <td><?php echo htmlspecialchars($row["name"]); ?></td>
                    <td><?php echo $row["created_at"]; ?></td>
                    <td>
                        <a href="announcement_edit.php?id=<?php echo $row["id"]; ?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="announcement_delete.php?id=<?php echo $row["id"]; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this announcement?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="text-center">No announcements found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
