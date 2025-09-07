<?php

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'config.php';
require_once 'includes/header.php';
require_once 'includes/url_helper.php';

$sql = "SELECT id, name, code FROM subjects ORDER BY name ASC";
$result = $conn->query($sql);

// Fetch all results into an array to prevent "mysqli_result object is already closed" error
$subjects = [];
if ($result && $result->num_rows > 0) {
    $subjects = $result->fetch_all(MYSQLI_ASSOC);
}
if ($result) {
    $result->close();
}

?>

<h2>Subjects</h2>
<a href="<?php echo subject_create_url(); ?>" class="btn btn-success mb-3">Create Subject</a>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Name</th>
            <th>Code</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($subjects)): ?>
            <?php foreach($subjects as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row["name"]); ?></td>
                    <td><?php echo htmlspecialchars($row["code"]); ?></td>
                    <td>
                        <a href="<?php echo subject_edit_url($row['id']); ?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="<?php echo subject_delete_url($row['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this subject?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="3" class="text-center">No subjects found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
require_once 'includes/footer.php';
$conn->close();
?>
