<?php
session_start();
require_once 'config.php';
require_once 'includes/header.php';

// 1. Authorization Check: Ensure user is a logged-in parent
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] !== 'parent') {
    header("location: login.php");
    exit;
}

// 2. Get Student ID and Verify Parent-Child Link
if (!isset($_GET['student_id']) || !is_numeric($_GET['student_id'])) {
    echo "<div class='alert alert-danger'>Invalid student ID.</div>";
    require_once 'includes/footer.php';
    exit;
}
$student_id = $_GET['student_id'];
$parent_id = $_SESSION['id'];

$auth_sql = "SELECT COUNT(*) FROM parent_student WHERE parent_id = ? AND student_id = ?";
$auth_stmt = $conn->prepare($auth_sql);
$auth_stmt->bind_param("ii", $parent_id, $student_id);
$auth_stmt->execute();
$auth_stmt->bind_result($count);
$auth_stmt->fetch();
$auth_stmt->close();

if ($count == 0) {
    echo "<div class='alert alert-danger'>You are not authorized to view this student's records.</div>";
    require_once 'includes/footer.php';
    exit;
}

// 3. Fetch Student's Details
$student_sql = "SELECT first_name, last_name FROM users WHERE id = ?";
$student_stmt = $conn->prepare($student_sql);
$student_stmt->bind_param("i", $student_id);
$student_stmt->execute();
$student_result = $student_stmt->get_result();
$student = $student_result->fetch_assoc();
$student_stmt->close();

// 4. Fetch Grades Data
$grades = [];
$grades_sql = "
    SELECT s.name AS subject_name, p.name AS paper_name, m.score, p.exam_type
    FROM marks m
    JOIN papers p ON m.paper_id = p.id
    JOIN subjects s ON p.subject_id = s.id
    WHERE m.user_id = ?
    ORDER BY s.name, p.exam_type, p.name
";
if($grades_stmt = $conn->prepare($grades_sql)) {
    $grades_stmt->bind_param("i", $student_id);
    $grades_stmt->execute();
    $grades_result = $grades_stmt->get_result();
    while($row = $grades_result->fetch_assoc()) {
        $grades[$row['subject_name']][] = $row;
    }
    $grades_stmt->close();
}

// 5. Fetch Attendance Data
$attendance = [];
$attendance_sql = "SELECT date, status, check_in, check_out FROM attendances WHERE user_id = ? ORDER BY date DESC";
if($att_stmt = $conn->prepare($attendance_sql)) {
    $att_stmt->bind_param("i", $student_id);
    $att_stmt->execute();
    $attendance_result = $att_stmt->get_result();
    while($row = $attendance_result->fetch_assoc()) {
        $attendance[] = $row;
    }
    $att_stmt->close();
}
?>

<div class="container mt-4">
    <h3>Viewing Records for <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h3>
    <a href="parent_dashboard.php" class="btn btn-secondary mb-4"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="grades-tab" data-bs-toggle="tab" data-bs-target="#grades" type="button" role="tab" aria-controls="grades" aria-selected="true">Grades</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance" type="button" role="tab" aria-controls="attendance" aria-selected="false">Attendance</button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="myTabContent">
        <!-- Grades Tab Pane -->
        <div class="tab-pane fade show active" id="grades" role="tabpanel" aria-labelledby="grades-tab">
            <div class="card mt-3">
                <div class="card-header">Academic Performance</div>
                <div class="card-body">
                    <?php if (empty($grades)): ?>
                        <div class="alert alert-info">No grades have been recorded for this student yet.</div>
                    <?php else: ?>
                        <?php foreach ($grades as $subject => $marks): ?>
                            <h5 class="mt-4"><?php echo htmlspecialchars($subject); ?></h5>
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Exam / Paper</th>
                                        <th>Type</th>
                                        <th>Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($marks as $mark): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($mark['paper_name']); ?></td>
                                            <td><?php echo htmlspecialchars($mark['exam_type']); ?></td>
                                            <td><?php echo htmlspecialchars($mark['score']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Attendance Tab Pane -->
        <div class="tab-pane fade" id="attendance" role="tabpanel" aria-labelledby="attendance-tab">
            <div class="card mt-3">
                <div class="card-header">Attendance History</div>
                <div class="card-body">
                    <?php if (empty($attendance)): ?>
                        <div class="alert alert-info">No attendance records found for this student.</div>
                    <?php else: ?>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendance as $att_record): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($att_record['date']); ?></td>
                                        <td><span class="badge bg-<?php echo $att_record['status'] == 'present' ? 'success' : 'danger'; ?>"><?php echo htmlspecialchars(ucfirst($att_record['status'])); ?></span></td>
                                        <td><?php echo htmlspecialchars($att_record['check_in'] ? date('h:i A', strtotime($att_record['check_in'])) : 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($att_record['check_out'] ? date('h:i A', strtotime($att_record['check_out'])) : 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
