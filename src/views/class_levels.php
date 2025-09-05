<?php

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../src/includes/header.php';

$sql = "
    SELECT
        cl.id,
        cl.name,
        GROUP_CONCAT(s.name ORDER BY s.name SEPARATOR ', ') as streams
    FROM class_levels cl
    LEFT JOIN streams s ON cl.id = s.class_level_id
    GROUP BY cl.id, cl.name
    ORDER BY cl.name ASC
";
$result = $conn->query($sql);
?>

<h2>Classes & Streams</h2>
<a href="class_level_create.php" class="btn btn-success mb-3">Create Class</a>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Class</th>
            <th>Streams</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row["name"]); ?></td>
                    <td><?php echo htmlspecialchars($row["streams"] ?? 'No streams yet'); ?></td>
                    <td>
                        <a href="streams.php?class_level_id=<?php echo $row["id"]; ?>" class="btn btn-info btn-sm">View/Add Streams</a>
                        <a href="class_level_edit.php?id=<?php echo $row["id"]; ?>" class="btn btn-primary btn-sm">Edit Class</a>
                        <a href="class_level_delete.php?id=<?php echo $row["id"]; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this class? This will also delete all associated streams.');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="3" class="text-center">No classes found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$conn->close();
require_once __DIR__ . '/../../src/includes/footer.php';
?>
