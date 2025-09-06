<?php
require_once 'config.php';

// Authorization check
$allowed_roles = ['teacher', 'headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: " . dashboard_url());
    exit;
}

$assignment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($assignment_id === 0) {
    $_SESSION['error_message'] = "Assignment not found.";
    header("location: " . assignments_url());
    exit;
}

// Fetch assignment details and verify ownership
$sql_assignment = "SELECT * FROM assignments WHERE id = ?";
if ($stmt_assignment = $conn->prepare($sql_assignment)) {
    $stmt_assignment->bind_param("i", $assignment_id);
    $stmt_assignment->execute();
    $result = $stmt_assignment->get_result();
    if ($result->num_rows === 1) {
        $assignment = $result->fetch_assoc();
        if ($_SESSION['role'] === 'teacher' && $assignment['teacher_id'] != $_SESSION['id']) {
            $_SESSION['error_message'] = "You are not authorized to view these submissions.";
            header("location: " . assignments_url());
            exit;
        }
    } else {
        $_SESSION['error_message'] = "Assignment not found.";
        header("location: " . assignments_url());
        exit;
    }
    $stmt_assignment->close();
}

// Fetch submissions for this assignment
$submissions = [];
$sql_submissions = "
    SELECT
        su.id, su.submission_date, su.file_path, su.grade, su.feedback,
        u.first_name, u.last_name
    FROM assignment_submissions su
    JOIN users u ON su.student_id = u.id
    WHERE su.assignment_id = ?
    ORDER BY u.last_name, u.first_name
";
if ($stmt_submissions = $conn->prepare($sql_submissions)) {
    $stmt_submissions->bind_param("i", $assignment_id);
    $stmt_submissions->execute();
    $result_submissions = $stmt_submissions->get_result();
    $submissions = $result_submissions->fetch_all(MYSQLI_ASSOC);
    $stmt_submissions->close();
}

// Fetch session messages
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);


require_once 'includes/header.php';
?>

<div class="container mt-4">
    <a href="<?php echo assignments_url(); ?>" class="btn btn-secondary mb-3"><i class="bi bi-arrow-left"></i> Back to Assignments</a>

    <?php if ($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
    <?php if ($error_message): ?><div class="alert alert-danger"><?php echo $error_message; ?></div><?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <h3><?php echo htmlspecialchars($assignment['title']); ?></h3>
        </div>
        <div class="card-body">
            <p><?php echo nl2br(htmlspecialchars($assignment['description'])); ?></p>
            <p><strong>Due Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($assignment['due_date'])); ?></p>
            <?php if ($assignment['file_path']): ?>
                <p><strong>Attachment:</strong> <a href="<?php echo url($assignment['file_path']); ?>" download>Download Attached File</a></p>
            <?php endif; ?>
        </div>
    </div>

    <h4>Submissions</h4>
    <form action="<?php echo grade_submission_url(); ?>" method="post">
        <input type="hidden" name="assignment_id" value="<?php echo $assignment_id; ?>">
        <div class="card">
            <div class="card-body">
                <?php if (empty($submissions)): ?>
                    <div class="alert alert-info">No submissions have been made for this assignment yet.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Student</th>
                                    <th>Submitted On</th>
                                    <th>File</th>
                                    <th>Grade</th>
                                    <th>Feedback</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($submissions as $sub): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($sub['first_name'] . ' ' . $sub['last_name']); ?></td>
                                        <td><?php echo date('F j, Y, g:i a', strtotime($sub['submission_date'])); ?></td>
                                        <td><a href="<?php echo url($sub['file_path']); ?>" download class="btn btn-sm btn-outline-primary">Download</a></td>
                                        <td>
                                            <input type="hidden" name="submissions[<?php echo $sub['id']; ?>][id]" value="<?php echo $sub['id']; ?>">
                                            <input type="text" name="submissions[<?php echo $sub['id']; ?>][grade]" class="form-control" value="<?php echo htmlspecialchars($sub['grade']); ?>" style="width: 100px;">
                                        </td>
                                        <td>
                                            <textarea name="submissions[<?php echo $sub['id']; ?>][feedback]" class="form-control" rows="1"><?php echo htmlspecialchars($sub['feedback']); ?></textarea>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-success">Save All Grades & Feedback</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
