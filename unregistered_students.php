<?php
session_start();
require_once 'config.php';

// Handle Re-register action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reregister_student'])) {
    $student_id = $_POST['student_id'];
    $stmt = $conn->prepare("UPDATE users SET status = 'active', status_change_reason = NULL, status_changed_at = NOW() WHERE id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $stmt->close();
    header("location: unregistered_students.php");
    exit();
}

require_once 'includes/header.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

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
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Unregistered Students</h2>
    <a href="<?php echo students_url(); ?>" class="btn btn-secondary">Back to Active Students</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead>
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
                                    <form action="unregistered_students.php" method="post" class="d-inline">
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

<?php
$conn->close();
require_once 'includes/footer.php';
?>
