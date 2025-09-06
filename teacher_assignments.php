<?php
require_once 'config.php';

// Authorization check
$allowed_roles = ['admin', 'headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: " . login_url());
    exit;
}

// Handle form submission for new assignment
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $teacher_id = $_POST['teacher_id'];
    $subject_id = $_POST['subject_id'];
    $stream_id = $_POST['stream_id'];

    $paper_id = null;
    $paper_sql = "SELECT id FROM papers WHERE subject_id = ?";
    if($stmt = $conn->prepare($paper_sql)) {
        $stmt->bind_param("i", $subject_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0) {
            $paper = $result->fetch_assoc();
            $paper_id = $paper['id'];
        } else {
            $subject_name_sql = "SELECT name FROM subjects WHERE id = ?";
            if($stmt_subject = $conn->prepare($subject_name_sql)) {
                $stmt_subject->bind_param("i", $subject_id);
                $stmt_subject->execute();
                $subject_name = $stmt_subject->get_result()->fetch_assoc()['name'];
                $stmt_subject->close();

                $insert_paper_sql = "INSERT INTO papers (subject_id, name, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";
                if($stmt_insert_paper = $conn->prepare($insert_paper_sql)) {
                    $stmt_insert_paper->bind_param("is", $subject_id, $subject_name);
                    $stmt_insert_paper->execute();
                    $paper_id = $stmt_insert_paper->insert_id;
                    $stmt_insert_paper->close();
                }
            }
        }
        $stmt->close();
    }

    if($paper_id) {
        $assign_sql = "INSERT INTO paper_stream_user (paper_id, stream_id, user_id, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
        if($stmt = $conn->prepare($assign_sql)) {
            $stmt->bind_param("iii", $paper_id, $stream_id, $teacher_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    $_SESSION['success_message'] = "Teacher assignment created successfully.";
    header("location: " . teacher_assignments_url());
    exit();
}

// Fetch session messages
$success_message = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);

// Fetch data for dropdowns
$teachers_sql = "SELECT id, first_name, last_name FROM users WHERE role = 'teacher' ORDER BY first_name ASC";
$teachers_result = $conn->query($teachers_sql);

$subjects_sql = "SELECT id, name FROM subjects ORDER BY name ASC";
$subjects_result = $conn->query($subjects_sql);

$streams_sql = "SELECT s.id, s.name, cl.name AS class_level_name FROM streams s JOIN class_levels cl ON s.class_level_id = cl.id ORDER BY cl.name, s.name ASC";
$streams_result = $conn->query($streams_sql);

// Fetch existing assignments
$assignments_sql = "
    SELECT psu.id, u.first_name, u.last_name, s.name AS subject_name, st.name AS stream_name, cl.name AS class_level_name
    FROM paper_stream_user psu
    JOIN users u ON psu.user_id = u.id
    JOIN papers p ON psu.paper_id = p.id
    JOIN subjects s ON p.subject_id = s.id
    JOIN streams st ON psu.stream_id = st.id
    JOIN class_levels cl ON st.class_level_id = cl.id
    ORDER BY u.first_name, s.name
";
$assignments_result = $conn->query($assignments_sql);

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <h2>Assign Teacher to Subject and Stream</h2>

    <?php if($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>

    <form action="<?php echo teacher_assignments_url(); ?>" method="post">
        <div class="row">
            <div class="col-md-3"><label for="teacher_id" class="form-label">Teacher</label><select name="teacher_id" id="teacher_id" class="form-control" required><option value="">Select Teacher</option><?php while($teacher = $teachers_result->fetch_assoc()): ?><option value="<?php echo $teacher['id']; ?>"><?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?></option><?php endwhile; ?></select></div>
            <div class="col-md-3"><label for="subject_id" class="form-label">Subject</label><select name="subject_id" id="subject_id" class="form-control" required><option value="">Select Subject</option><?php while($subject = $subjects_result->fetch_assoc()): ?><option value="<?php echo $subject['id']; ?>"><?php echo htmlspecialchars($subject['name']); ?></option><?php endwhile; ?></select></div>
            <div class="col-md-3"><label for="stream_id" class="form-label">Stream</label><select name="stream_id" id="stream_id" class="form-control" required><option value="">Select Stream</option><?php while($stream = $streams_result->fetch_assoc()): ?><option value="<?php echo $stream['id']; ?>"><?php echo htmlspecialchars($stream['class_level_name'] . ' - ' . $stream['name']); ?></option><?php endwhile; ?></select></div>
            <div class="col-md-3 align-self-end"><input type="submit" class="btn btn-primary" value="Assign"></div>
        </div>
    </form>

    <h3 class="mt-5">Current Assignments</h3>
    <table class="table table-bordered">
        <thead><tr><th>Teacher</th><th>Subject</th><th>Stream</th><th>Action</th></tr></thead>
        <tbody>
            <?php if ($assignments_result->num_rows > 0): ?>
                <?php while($row = $assignments_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row["first_name"] . ' ' . $row["last_name"]); ?></td>
                        <td><?php echo htmlspecialchars($row["subject_name"]); ?></td>
                        <td><?php echo htmlspecialchars($row["class_level_name"] . ' - ' . $row["stream_name"]); ?></td>
                        <td><a href="<?php echo teacher_assignment_delete_url($row["id"]); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" class="text-center">No assignments found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
