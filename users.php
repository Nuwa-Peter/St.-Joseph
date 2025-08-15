<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Add role check for admin access
// if ($_SESSION['role'] !== 'root' && $_SESSION['role'] !== 'headteacher') {
//     header("location: dashboard.php");
//     exit;
// }

require_once 'config.php';
require_once 'includes/header.php';

$sql = "SELECT id, first_name, last_name, email, role, status FROM users ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
    <h2 class="mb-3 mb-md-0">User Management</h2>
    <a href="user_create.php" class="btn btn-success"><i class="bi bi-plus-circle-fill me-2"></i>Create User</a>
</div>
<div class="table-responsive">
    <table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row["first_name"] . ' ' . $row["last_name"]); ?></td>
                    <td><?php echo htmlspecialchars($row["email"]); ?></td>
                    <td><?php echo htmlspecialchars($row["role"]); ?></td>
                    <td><?php echo htmlspecialchars($row["status"]); ?></td>
                    <td>
                        <a href="user_edit.php?id=<?php echo $row["id"]; ?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="user_delete.php?id=<?php echo $row["id"]; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="text-center">No users found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
