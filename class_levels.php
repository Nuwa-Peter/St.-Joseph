<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'config.php';
require_once 'includes/header.php';

$sql = "SELECT id, name FROM class_levels ORDER BY name ASC";
$result = $conn->query($sql);
?>

<h2>Class Levels</h2>
<a href="class_level_create.php" class="btn btn-success mb-3">Create Class Level</a>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Name</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row["name"]); ?></td>
                    <td>
                        <a href="streams.php?class_level_id=<?php echo $row["id"]; ?>" class="btn btn-info btn-sm">View/Add Streams</a>
                        <a href="class_level_edit.php?id=<?php echo $row["id"]; ?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="class_level_delete.php?id=<?php echo $row["id"]; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this class level? This will also delete all associated streams.');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="2" class="text-center">No class levels found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
