<?php
// This widget requires the $conn database connection and the logged-in user's ID.
$teacher_id = $_SESSION['id'];
$assignments = [];

// Fetch all streams and subjects assigned to the logged-in teacher.
// This assumes a `teacher_assignments` table linking teachers to stream-subject combinations.
// A more normalized schema might have a `stream_subject` table and a `teacher_id` on that.
// Let's assume the table is `teacher_assignments` with user_id, stream_id, subject_id.
$sql = "SELECT
            st.name AS stream_name,
            sub.name AS subject_name,
            cl.name as class_level_name
        FROM teacher_assignments ta
        JOIN streams st ON ta.stream_id = st.id
        JOIN subjects sub ON ta.subject_id = sub.id
        JOIN class_levels cl ON st.class_level_id = cl.id
        WHERE ta.user_id = ?
        ORDER BY cl.name, st.name, sub.name";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $assignments[] = $row;
    }
    $stmt->close();
}
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">My Teaching Assignments</h6>
    </div>
    <div class="card-body">
        <?php if (empty($assignments)): ?>
            <div class="alert alert-info">You have not been assigned to any classes or subjects yet.</div>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($assignments as $assignment): ?>
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1"><?php echo htmlspecialchars($assignment['subject_name']); ?></h5>
                        </div>
                        <p class="mb-1">
                            <span class="fw-bold">Class:</span> <?php echo htmlspecialchars($assignment['class_level_name']); ?> - <?php echo htmlspecialchars($assignment['stream_name']); ?>
                        </p>
                        <small>
                            <a href="class_attendance.php?stream_id=...">Take Attendance</a> |
                            <a href="marks_entry.php?stream_id=...&subject_id=...">Enter Marks</a>
                        </small>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
