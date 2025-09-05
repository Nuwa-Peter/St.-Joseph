<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../src/includes/header.php';

// Authorization check: only teachers and admins can manage assignments
$allowed_roles = ['teacher', 'headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    // Redirect to dashboard if not authorized
    header("location: dashboard.php");
    exit;
}

$teacher_id = $_SESSION['id'];
$assignments = [];

// Fetch assignments created by the logged-in teacher
// Admins can see all assignments, teachers only see their own.
$sql = "SELECT
            a.id, a.title, a.due_date,
            s.name as subject_name,
            st.name as stream_name,
            cl.name as class_level_name
        FROM assignments a
        JOIN subjects s ON a.subject_id = s.id
        JOIN streams st ON a.stream_id = st.id
        JOIN class_levels cl ON st.class_level_id = cl.id";

if ($_SESSION['role'] === 'teacher') {
    $sql .= " WHERE a.teacher_id = ?";
}
$sql .= " ORDER BY a.due_date DESC";

if ($stmt = $conn->prepare($sql)) {
    if ($_SESSION['role'] === 'teacher') {
        $stmt->bind_param("i", $teacher_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $assignments[] = $row;
    }
    $stmt->close();
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Assignments</h2>
        <a href="/assignments/create" class="btn btn-primary"><i class="bi bi-plus-circle-fill me-2"></i>Create New Assignment</a>
    </div>

    <?php
    if (isset($_SESSION['success_message'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . $_SESSION['success_message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        unset($_SESSION['success_message']);
    }
    ?>

    <div class="card">
        <div class="card-body">
            <?php if (empty($assignments)): ?>
                <div class="alert alert-info">You have not created any assignments yet.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Due Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignments as $assignment): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                    <td><?php echo htmlspecialchars($assignment['class_level_name'] . ' - ' . $assignment['stream_name']); ?></td>
                                    <td><?php echo htmlspecialchars($assignment['subject_name']); ?></td>
                                    <td><?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($assignment['due_date']))); ?></td>
                                    <td>
                                        <a href="assignment_submissions.php?id=<?php echo $assignment['id']; ?>" class="btn btn-sm btn-info" title="View Submissions"><i class="bi bi-eye-fill"></i></a>
                                        <a href="assignment_edit.php?id=<?php echo $assignment['id']; ?>" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                                        <a href="assignment_delete.php?id=<?php echo $assignment['id']; ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this assignment?');"><i class="bi bi-trash-fill"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once __DIR__ . '/../../src/includes/footer.php';
?>
