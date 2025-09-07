<?php
// 1. Include dependencies and start session
require_once 'config.php';
session_start();

// 2. Check if the user is logged in and has the correct role
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php"); // Consider using login_url() helper
    exit;
}

// 3. Include the header, which also needs url_helper
require_once 'includes/header.php';

// 4. Page-specific logic: Fetch data
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

// Fetch all results into an array to prevent mysqli_result errors
$classes = [];
if ($result) {
    $classes = $result->fetch_all(MYSQLI_ASSOC);
    $result->close();
}
?>

<h2>Classes & Streams</h2>
<a href="<?php echo class_create_url(); ?>" class="btn btn-success mb-3">Create Class</a>

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

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Class</th>
            <th>Streams</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($classes)): ?>
            <?php foreach($classes as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row["name"]); ?></td>
                    <td><?php echo htmlspecialchars($row["streams"] ?? 'No streams yet'); ?></td>
                    <td>
                        <a href="<?php echo streams_url() . '?class_level_id=' . $row['id']; ?>" class="btn btn-info btn-sm">View/Add Streams</a>
                        <a href="<?php echo class_edit_url($row['id']); ?>" class="btn btn-primary btn-sm">Edit Class</a>
                        <a href="<?php echo class_delete_url($row['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this class? This will also delete all associated streams.');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="3" class="text-center">No classes found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
// 5. Include the footer
require_once 'includes/footer.php';
// The connection is closed in footer.php, so no need to close it here again if it's already there.
// Let's assume it is, for consistency.
?>
