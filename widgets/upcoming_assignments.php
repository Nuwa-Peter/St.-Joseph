<?php
// This widget requires $conn and $_SESSION to be available.

$user_id = $_SESSION['id'];
$user_role = $_SESSION['role'];
$upcoming_assignments = [];

$sql = "SELECT a.id, a.title, a.due_date, s.name as subject_name
        FROM assignments a
        JOIN subjects s ON a.subject_id = s.id ";

if ($user_role === 'teacher') {
    $sql .= "WHERE a.teacher_id = ? AND a.due_date > NOW() ORDER BY a.due_date ASC LIMIT 5";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
} elseif ($user_role === 'student') {
    // First, find the student's stream
    $stream_id = null;
    $stream_sql = "SELECT stream_id FROM stream_user WHERE user_id = ? LIMIT 1";
    if ($stream_stmt = $conn->prepare($stream_sql)) {
        $stream_stmt->bind_param("i", $user_id);
        $stream_stmt->execute();
        $stream_stmt->bind_result($stream_id);
        $stream_stmt->fetch();
        $stream_stmt->close();
    }

    if ($stream_id) {
        $sql .= "WHERE a.stream_id = ? AND a.due_date > NOW() ORDER BY a.due_date ASC LIMIT 5";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $stream_id);
    }
}

if (isset($stmt) && $stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $upcoming_assignments[] = $row;
    }
    $stmt->close();
}
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Upcoming Assignments</h6>
    </div>
    <div class="card-body">
        <?php if (empty($upcoming_assignments)): ?>
            <div class="alert alert-info">No upcoming assignments.</div>
        <?php else: ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($upcoming_assignments as $assignment): ?>
                    <li class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><?php echo htmlspecialchars($assignment['title']); ?></h6>
                            <small>Due: <?php echo date('M j, Y', strtotime($assignment['due_date'])); ?></small>
                        </div>
                        <p class="mb-1 text-muted"><?php echo htmlspecialchars($assignment['subject_name']); ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <a href="<?php echo $user_role === 'student' ? url('student-assignments-view') : assignments_url(); ?>" class="mt-3 d-block">View All Assignments &rarr;</a>
    </div>
</div>
