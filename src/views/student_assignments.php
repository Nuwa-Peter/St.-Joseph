<?php

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once __DIR__ . '/../../config.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $stream_id = $_POST['stream_id'];

    // Use ON DUPLICATE KEY UPDATE to either assign a new student or update an existing one's stream.
    // This assumes a student can only be in one stream at a time, enforced by a unique key on user_id.
    $assign_sql = "INSERT INTO stream_user (user_id, stream_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW()) ON DUPLICATE KEY UPDATE stream_id = VALUES(stream_id), updated_at = NOW()";

    if($stmt = $conn->prepare($assign_sql)) {
        $stmt->bind_param("ii", $student_id, $stream_id);
        $stmt->execute();
        $stmt->close();
    }
    // Redirect to the same page to see the new assignment and prevent form resubmission
    header("location: student_assignments.php");
    exit();
}

require_once __DIR__ . '/../../src/includes/header.php';

// Fetch students and streams for dropdowns
$students_sql = "SELECT id, first_name, last_name FROM users WHERE role = 'student' ORDER BY first_name ASC";
$students_result = $conn->query($students_sql);

$streams_sql = "SELECT s.id, s.name, cl.name AS class_level_name FROM streams s JOIN class_levels cl ON s.class_level_id = cl.id ORDER BY cl.name, s.name ASC";
$streams_result = $conn->query($streams_sql);

// Fetch existing assignments
$assignments_sql = "
    SELECT
        su.id,
        u.first_name,
        u.last_name,
        st.name AS stream_name,
        cl.name AS class_level_name
    FROM stream_user su
    JOIN users u ON su.user_id = u.id
    JOIN streams st ON su.stream_id = st.id
    JOIN class_levels cl ON st.class_level_id = cl.id
    ORDER BY u.first_name
";
$assignments_result = $conn->query($assignments_sql);

?>

<h2>Assign Student to Stream</h2>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div class="row">
        <div class="col-md-4">
            <label for="student_id" class="form-label">Student</label>
            <select name="student_id" id="student_id" class="form-control" required>
                <option value="">Select Student</option>
                <?php while($student = $students_result->fetch_assoc()): ?>
                    <option value="<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label for="stream_id" class="form-label">Stream</label>
            <select name="stream_id" id="stream_id" class="form-control" required>
                <option value="">Select Stream</option>
                <?php while($stream = $streams_result->fetch_assoc()): ?>
                    <option value="<?php echo $stream['id']; ?>"><?php echo htmlspecialchars($stream['class_level_name'] . ' - ' . $stream['name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-4 align-self-end">
            <input type="submit" class="btn btn-primary" value="Assign">
        </div>
    </div>
</form>

<h3 class="mt-5">Current Student Assignments</h3>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Student</th>
            <th>Stream</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($assignments_result->num_rows > 0): ?>
            <?php while($row = $assignments_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row["first_name"] . ' ' . $row["last_name"]); ?></td>
                    <td><?php echo htmlspecialchars($row["class_level_name"] . ' - ' . $row["stream_name"]); ?></td>
                    <td>
                        <a href="student_assignment_delete.php?id=<?php echo $row["id"]; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to remove this student from the stream?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="3" class="text-center">No assignments found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>


<?php
$conn->close();
require_once __DIR__ . '/../../src/includes/footer.php';
?>
