<?php
require_once 'config.php';
require_once 'includes/url_helper.php';

// Authorization check
$allowed_roles = ['admin', 'headteacher', 'root', 'teacher'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: " . login_url());
    exit;
}

$sql = "SELECT id, name, code FROM subjects ORDER BY name ASC";
$result = $conn->query($sql);

// Fetch session messages
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-book me-2"></i>Subjects</h2>
        <?php if(in_array($_SESSION['role'], ['admin', 'headteacher', 'root'])): ?>
            <a href="<?php echo subject_create_url(); ?>" class="btn btn-success">Create Subject</a>
        <?php endif; ?>
    </div>

    <?php if($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
    <?php if($error_message): ?><div class="alert alert-danger"><?php echo $error_message; ?></div><?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
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
                                        <a href="<?php echo subject_edit_url($row["id"]); ?>" class="btn btn-primary btn-sm">Edit</a>
                                        <a href="<?php echo subject_delete_url($row["id"]); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this subject?');">Delete</a>
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
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
