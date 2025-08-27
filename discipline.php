<?php
session_start();
require_once 'config.php';

$success_message = "";
$error_message = "";

// Role-based access control
$allowed_roles = ['root', 'headteacher', 'teacher'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: dashboard.php");
    exit;
}

// Handle form submission for adding a new log
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_log'])) {
    $student_id = trim($_POST['student_id']);
    $type = trim($_POST['type']);
    $log_date = trim($_POST['log_date']);
    $description = trim($_POST['description']);
    $recorded_by_id = $_SESSION['id']; // Get the logged-in user's ID

    // Basic validation
    if (empty($student_id) || empty($type) || empty($log_date) || empty($description)) {
        $error_message = "All fields are required.";
    } else {
        $sql_insert = "INSERT INTO discipline_logs (user_id, recorded_by_id, type, log_date, description, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";

        if ($stmt = $conn->prepare($sql_insert)) {
            $stmt->bind_param("iisss", $student_id, $recorded_by_id, $type, $log_date, $description);

            if ($stmt->execute()) {
                $success_message = "Discipline log added successfully.";
            } else {
                $error_message = "Error adding log: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_message = "Error preparing statement: " . $conn->error;
        }
    }
}

require_once 'includes/header.php';

// Fetch all students for the dropdown
$students = [];
$sql_students = "SELECT id, first_name, last_name FROM users WHERE role = 'student' AND status = 'active' ORDER BY last_name, first_name";
$result_students = $conn->query($sql_students);
if ($result_students && $result_students->num_rows > 0) {
    while ($row = $result_students->fetch_assoc()) {
        $students[] = $row;
    }
}

// Fetch all discipline logs
$logs = [];
$sql_logs = "
    SELECT
        dl.id,
        dl.type,
        dl.log_date,
        dl.description,
        CONCAT(student.first_name, ' ', student.last_name) AS student_name,
        CONCAT(recorder.first_name, ' ', recorder.last_name) AS recorded_by_name,
        dl.created_at
    FROM
        discipline_logs dl
    JOIN
        users student ON dl.user_id = student.id
    JOIN
        users recorder ON dl.recorded_by_id = recorder.id
    ORDER BY
        dl.log_date DESC, dl.created_at DESC";
$result_logs = $conn->query($sql_logs);
if ($result_logs && $result_logs->num_rows > 0) {
    while ($row = $result_logs->fetch_assoc()) {
        $logs[] = $row;
    }
}

// The connection will be closed in the backend logic part, for now, it's here.
// $conn->close();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-person-exclamation me-2"></i>Discipline Logs</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLogModal">
            <i class="bi bi-plus-circle-fill me-2"></i>Add New Log
        </button>
    </div>

    <!-- Display success/error messages -->
    <?php if(!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if(!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Student</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Recorded By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($logs)): ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(date('M j, Y', strtotime($log['log_date']))); ?></td>
                                    <td><?php echo htmlspecialchars($log['student_name']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $log['type'] === 'commendation' ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo htmlspecialchars(ucfirst($log['type'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo nl2br(htmlspecialchars($log['description'])); ?></td>
                                    <td><?php echo htmlspecialchars($log['recorded_by_name']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No discipline logs found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add New Log Modal -->
<div class="modal fade" id="addLogModal" tabindex="-1" aria-labelledby="addLogModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="discipline.php" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="addLogModalLabel">Add New Discipline Log</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="student_id" class="form-label">Student</label>
                        <select class="form-select" id="student_id" name="student_id" required>
                            <option value="" disabled selected>Select a student...</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['id']; ?>">
                                    <?php echo htmlspecialchars($student['last_name'] . ', ' . $student['first_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="type" class="form-label">Log Type</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="incident">Incident</option>
                            <option value="commendation">Commendation</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="log_date" class="form-label">Date of Occurrence</label>
                        <input type="date" class="form-control" id="log_date" name="log_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_log" class="btn btn-primary">Save Log</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
