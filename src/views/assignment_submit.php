<?php
require_once __DIR__ . '/../../config.php';

// Authorization check: only students can view this page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] !== 'student') {
    header("location: dashboard.php");
    exit;
}

$assignment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($assignment_id === 0) {
    header("location: student_assignments_view.php?error=notfound");
    exit;
}

$student_id = $_SESSION['id'];

// Fetch assignment details
$sql_assignment = "SELECT a.*, s.name as subject_name, u.first_name, u.last_name
                   FROM assignments a
                   JOIN subjects s ON a.subject_id = s.id
                   JOIN users u ON a.teacher_id = u.id
                   WHERE a.id = ?";
$stmt_assignment = $conn->prepare($sql_assignment);
$stmt_assignment->bind_param("i", $assignment_id);
$stmt_assignment->execute();
$result_assignment = $stmt_assignment->get_result();
if ($result_assignment->num_rows !== 1) {
    header("location: student_assignments_view.php?error=notfound");
    exit;
}
$assignment = $result_assignment->fetch_assoc();
$stmt_assignment->close();

// Fetch existing submission details for this student
$submission = null;
$sql_submission = "SELECT * FROM assignment_submissions WHERE assignment_id = ? AND student_id = ?";
$stmt_submission = $conn->prepare($sql_submission);
$stmt_submission->bind_param("ii", $assignment_id, $student_id);
$stmt_submission->execute();
$result_submission = $stmt_submission->get_result();
if ($result_submission->num_rows === 1) {
    $submission = $result_submission->fetch_assoc();
}
$stmt_submission->close();

require_once __DIR__ . '/../../src/includes/header.php';
?>

<div class="container mt-4">
    <a href="student_assignments_view.php" class="btn btn-secondary mb-3"><i class="bi bi-arrow-left"></i> My Assignments</a>

    <div class="card mb-4">
        <div class="card-header">
            <h3 class="mb-0"><?php echo htmlspecialchars($assignment['title']); ?></h3>
        </div>
        <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($assignment['subject_name']); ?></h5>
            <h6 class="card-subtitle mb-2 text-muted">Assigned by: <?php echo htmlspecialchars($assignment['first_name'] . ' ' . $assignment['last_name']); ?></h6>
            <p class="card-text mt-3"><?php echo nl2br(htmlspecialchars($assignment['description'])); ?></p>
            <p><strong>Due Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($assignment['due_date'])); ?></p>
            <?php if ($assignment['file_path']): ?>
                <p><strong>Attachment:</strong> <a href="<?php echo htmlspecialchars($assignment['file_path']); ?>" download>Download Assignment File</a></p>
            <?php endif; ?>
        </div>
    </div>

    <h4>My Submission</h4>
    <div class="card">
        <div class="card-body">
            <?php if ($submission): ?>
                <h5>Your work has been submitted.</h5>
                <p><strong>Submitted on:</strong> <?php echo date('F j, Y, g:i a', strtotime($submission['submission_date'])); ?></p>
                <p><strong>Submitted File:</strong> <a href="<?php echo htmlspecialchars($submission['file_path']); ?>" download>Download Your Submission</a></p>
                <hr>
                <h5>Feedback</h5>
                <?php if (!empty($submission['grade']) || !empty($submission['feedback'])): ?>
                    <p><strong>Grade:</strong> <?php echo htmlspecialchars($submission['grade'] ?: 'Not graded yet'); ?></p>
                    <p><strong>Teacher's Feedback:</strong></p>
                    <div class="p-3 bg-light rounded">
                        <?php echo nl2br(htmlspecialchars($submission['feedback'] ?: 'No feedback provided yet.')); ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">Your submission has not been graded yet.</div>
                <?php endif; ?>
            <?php else: ?>
                <h5>Submit Your Work</h5>
                <form action="handle_submission.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="assignment_id" value="<?php echo $assignment_id; ?>">
                    <div class="mb-3">
                        <label for="submission_file" class="form-label">Upload your file:</label>
                        <input type="file" class="form-control" name="submission_file" id="submission_file" required>
                        <div class="form-text">Please submit your work as a single file.</div>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Assignment</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once __DIR__ . '/../../src/includes/footer.php';
?>
