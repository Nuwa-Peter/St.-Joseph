<?php

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../src/includes/header.php';

$sql = "SELECT id, name, code FROM subjects ORDER BY name ASC";
$result = $conn->query($sql);
?>

<h2>Subjects</h2>
<a href="subject_create.php" class="btn btn-success mb-3">Create Subject</a>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Name</th>
            <th>Code</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row["name"]); ?></td>
                    <td><?php echo htmlspecialchars($row["code"]); ?></td>
                    <td>
                        <a href="subject_edit.php?id=<?php echo $row["id"]; ?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="subject_delete.php?id=<?php echo $row["id"]; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this subject?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="3" class="text-center">No subjects found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$conn->close();
require_once __DIR__ . '/../../src/includes/footer.php';
?>
