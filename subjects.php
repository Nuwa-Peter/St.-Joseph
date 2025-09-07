<?php
// 1. Include dependencies and start session
require_once 'config.php';
session_start();

// 2. Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php"); // Consider using login_url()
    exit;
}

// 3. Include the header, which also needs url_helper
require_once 'includes/header.php';

// 4. Page-specific logic: Fetch data
$sql = "SELECT id, name, code FROM subjects ORDER BY name ASC";
$result = $conn->query($sql);

// Fetch all results into an array to prevent mysqli_result errors
$subjects = [];
if ($result) {
    $subjects = $result->fetch_all(MYSQLI_ASSOC);
    $result->close();
}
?>

<h2>Subjects</h2>
<a href="<?php echo subject_create_url(); ?>" class="btn btn-success mb-3">Create Subject</a>

<?php
// Display success or error messages
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
    unset($_SESSION['error_message']);
}
?>

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
// 5. Include the footer
require_once 'includes/footer.php';
?>
