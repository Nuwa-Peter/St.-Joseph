<?php
require_once 'config.php';

// Authorization check
$allowed_roles = ['admin', 'headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: " . login_url());
    exit;
}

// Handle Re-register action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reregister_student'])) {
    $student_id = $_POST['student_id'];
    $stmt = $conn->prepare("UPDATE users SET status = 'active', status_change_reason = NULL, status_changed_at = NOW() WHERE id = ?");
    $stmt->bind_param("i", $student_id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Student has been successfully re-registered.";
    } else {
        $_SESSION['error_message'] = "Failed to re-register student.";
    }
    $stmt->close();
    header("location: " . unregistered_students_url());
    exit();
}

// Fetch session messages
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Fetch all unregistered students
$sql = "
    SELECT
        u.id, u.first_name, u.last_name, u.status_change_reason, u.status_changed_at,
        s.name AS stream_name, cl.name AS class_level_name
    FROM users u
    LEFT JOIN stream_user su ON u.id = su.user_id
    LEFT JOIN streams s ON su.stream_id = s.id
    LEFT JOIN class_levels cl ON s.class_level_id = cl.id
    WHERE u.role = 'student' AND u.status = 'unregistered'
    ORDER BY u.status_changed_at DESC
";
$result = $conn->query($sql);

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center my-4">
        <h2 class="text-primary"><i class="bi bi-person-x-fill me-2"></i>Unregistered Students</h2>
        <a href="<?php echo students_url(); ?>" class="btn btn-secondary">Back to Active Students</a>
    </div>

    <?php if($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
    <?php if($error_message): ?><div class="alert alert-danger"><?php echo $error_message; ?></div><?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Last Known Class</th>
                            <th>Reason for Unregistering</th>
                            <th>Date Unregistered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row["first_name"] . ' ' . $row["last_name"]); ?></td>
                                    <td><?php echo htmlspecialchars(($row["class_level_name"] ?? '') . ' - ' . ($row["stream_name"] ?? 'N/A')); ?></td>
                                    <td><?php echo htmlspecialchars($row["status_change_reason"]); ?></td>
                                    <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($row["status_changed_at"]))); ?></td>
                                    <td>
                                        <form action="<?php echo unregistered_students_url(); ?>" method="post" class="d-inline">
                                            <input type="hidden" name="student_id" value="<?php echo $row["id"]; ?>">
                                            <button type="submit" name="reregister_student" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to re-register this student?');">Re-register</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No unregistered students found.</td>
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
