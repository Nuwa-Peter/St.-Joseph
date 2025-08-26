<?php
// This widget requires the $conn database connection and the logged-in user's ID.
$student_id = $_SESSION['id'];
$recent_grades = [];

// Fetch the 10 most recently entered marks for the student.
$sql = "SELECT
            s.name AS subject_name,
            p.name AS paper_name,
            m.score
        FROM marks m
        JOIN papers p ON m.paper_id = p.id
        JOIN subjects s ON p.subject_id = s.id
        WHERE m.user_id = ?
        ORDER BY m.updated_at DESC
        LIMIT 10";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $recent_grades[] = $row;
    }
    $stmt->close();
}
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">My Recent Grades</h6>
    </div>
    <div class="card-body">
        <?php if (empty($recent_grades)): ?>
            <div class="alert alert-info">No grades have been recorded for you yet.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Paper/Exam</th>
                            <th>Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_grades as $grade): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($grade['subject_name']); ?></td>
                                <td><?php echo htmlspecialchars($grade['paper_name']); ?></td>
                                <td><strong><?php echo htmlspecialchars($grade['score']); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <a href="#">View All My Grades &rarr;</a>
        <?php endif; ?>
    </div>
</div>
