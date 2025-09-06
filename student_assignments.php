<?php
require_once 'config.php';

// Authorization check
$allowed_roles = ['admin', 'headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: " . login_url());
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $stream_id = $_POST['stream_id'];

    $assign_sql = "INSERT INTO stream_user (user_id, stream_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW()) ON DUPLICATE KEY UPDATE stream_id = VALUES(stream_id), updated_at = NOW()";

    if($stmt = $conn->prepare($assign_sql)) {
        $stmt->bind_param("ii", $student_id, $stream_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Student assigned to stream successfully.";
        } else {
            $_SESSION['error_message'] = "Error assigning student.";
        }
        $stmt->close();
    }
    header("location: " . student_assignments_url());
    exit();
}

// Fetch session messages
$success_message = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);

// Fetch data for dropdowns
$students_sql = "SELECT id, first_name, last_name FROM users WHERE role = 'student' ORDER BY first_name ASC";
$students_result = $conn->query($students_sql);

$streams_sql = "SELECT s.id, s.name, cl.name AS class_level_name FROM streams s JOIN class_levels cl ON s.class_level_id = cl.id ORDER BY cl.name, s.name ASC";
$streams_result = $conn->query($streams_sql);

// Fetch existing assignments
$assignments_sql = "
    SELECT su.id, u.first_name, u.last_name, st.name AS stream_name, cl.name AS class_level_name
    FROM stream_user su
    JOIN users u ON su.user_id = u.id
    JOIN streams st ON su.stream_id = st.id
    JOIN class_levels cl ON st.class_level_id = cl.id
    ORDER BY u.first_name
";
$assignments_result = $conn->query($assignments_sql);

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <h2>Assign Student to Stream</h2>

    <?php if($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>

    <form action="<?php echo student_assignments_url(); ?>" method="post">
        <div class="row">
            <div class="col-md-4"><label for="student_id" class="form-label">Student</label><select name="student_id" id="student_id" class="form-control" required><option value="">Select Student</option><?php while($student = $students_result->fetch_assoc()): ?><option value="<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></option><?php endwhile; ?></select></div>
            <div class="col-md-4"><label for="stream_id" class="form-label">Stream</label><select name="stream_id" id="stream_id" class="form-control" required><option value="">Select Stream</option><?php while($stream = $streams_result->fetch_assoc()): ?><option value="<?php echo $stream['id']; ?>"><?php echo htmlspecialchars($stream['class_level_name'] . ' - ' . $stream['name']); ?></option><?php endwhile; ?></select></div>
            <div class="col-md-4 align-self-end"><input type="submit" class="btn btn-primary" value="Assign"></div>
        </div>
    </form>

    <h3 class="mt-5">Current Student Assignments</h3>
    <table class="table table-bordered">
        <thead><tr><th>Student</th><th>Stream</th><th>Action</th></tr></thead>
        <tbody>
            <?php if ($assignments_result->num_rows > 0): ?>
                <?php while($row = $assignments_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row["first_name"] . ' ' . $row["last_name"]); ?></td>
                        <td><?php echo htmlspecialchars($row["class_level_name"] . ' - ' . $row["stream_name"]); ?></td>
                        <td><a href="<?php echo student_assignment_delete_url($row["id"]); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="3" class="text-center">No assignments found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
