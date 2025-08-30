<?php
session_start();
require_once 'config.php';
require_once 'includes/header.php';

// Authorization check: only students can view this page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] !== 'student') {
    header("location: dashboard.php");
    exit;
}

$student_id = $_SESSION['id'];
$assignments = [];

// 1. Find the student's stream
$stream_id = null;
$stream_sql = "SELECT stream_id FROM stream_user WHERE user_id = ? LIMIT 1";
if ($stream_stmt = $conn->prepare($stream_sql)) {
    $stream_stmt->bind_param("i", $student_id);
    $stream_stmt->execute();
    $stream_stmt->bind_result($stream_id);
    $stream_stmt->fetch();
    $stream_stmt->close();
}

if ($stream_id) {
    // 2. Fetch all assignments for that stream, and join with submissions to check status
    $sql = "SELECT
                a.id, a.title, a.due_date,
                s.name as subject_name,
                sub.id IS NOT NULL AS submitted,
                sub.grade IS NOT NULL AS graded
            FROM assignments a
            JOIN subjects s ON a.subject_id = s.id
            LEFT JOIN assignment_submissions sub ON a.id = sub.assignment_id AND sub.student_id = ?
            WHERE a.stream_id = ?
            ORDER BY a.due_date DESC";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $student_id, $stream_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $assignments[] = $row;
        }
        $stmt->close();
    }
}
?>

<div class="container mt-4">
    <h2>My Assignments</h2>

    <div class="card">
        <div class="card-body">
            <?php if (!$stream_id): ?>
                <div class="alert alert-warning">You are not currently assigned to a class, so you cannot see any assignments.</div>
            <?php elseif (empty($assignments)): ?>
                <div class="alert alert-info">You have no assignments at the moment.</div>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($assignments as $assignment): ?>
                        <a href="assignment_submit.php?id=<?php echo $assignment['id']; ?>" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1"><?php echo htmlspecialchars($assignment['title']); ?></h5>
                                <small>Due: <?php echo date('F j, Y', strtotime($assignment['due_date'])); ?></small>
                            </div>
                            <p class="mb-1"><?php echo htmlspecialchars($assignment['subject_name']); ?></p>
                            <small>
                                <?php
                                if ($assignment['graded']) {
                                    echo '<span class="badge bg-success">Graded</span>';
                                } elseif ($assignment['submitted']) {
                                    echo '<span class="badge bg-primary">Submitted</span>';
                                } else {
                                    echo '<span class="badge bg-warning text-dark">Not Submitted</span>';
                                }
                                ?>
                            </small>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
