<?php
require_once 'config.php';
require_once 'config.php';

$success_message = "";
$error_message = "";

// Handle Unregister form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['unregister_student'])) {
    $student_id = $_POST['student_id'];
    $reason = trim($_POST['reason']);

    if (empty($reason)) {
        $error_message = "A reason is required to unregister a student.";
    } else {
        $stmt = $conn->prepare("UPDATE users SET status = 'unregistered', status_change_reason = ?, status_changed_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $reason, $student_id);
        if ($stmt->execute()) {
            $success_message = "Student has been successfully unregistered.";
        } else {
            $error_message = "Failed to unregister student.";
        }
        $stmt->close();
    }
}

require_once 'includes/header.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$sql = "
    SELECT
        u.id, u.first_name, u.last_name, u.lin, u.student_type, u.photo,
        s.name AS stream_name, cl.name AS class_level_name
    FROM users u
    LEFT JOIN stream_user su ON u.id = su.user_id
    LEFT JOIN streams s ON su.stream_id = s.id
    LEFT JOIN class_levels cl ON s.class_level_id = cl.id
    WHERE u.role = 'student' AND u.status = 'active'
    ORDER BY u.last_name, u.first_name
";
$result = $conn->query($sql);
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
    <h2 class="mb-3 mb-md-0">All Students</h2>
    <div class="btn-toolbar" role="toolbar">
        <div class="btn-group me-2" role="group">
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#exportModal"><i class="bi bi-file-earmark-pdf-fill me-2"></i>Export to PDF</button>
        </div>
        <div class="btn-group" role="group">
            <a href="<?php echo student_import_export_url(); ?>" class="btn btn-info"><i class="bi bi-file-earmark-spreadsheet-fill me-2"></i>Import / Export</a>
            <a href="<?php echo student_create_url(); ?>" class="btn btn-success"><i class="bi bi-person-plus-fill me-2"></i>Add New Student</a>
        </div>
    </div>
</div>

<?php if($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
<?php if($error_message): ?><div class="alert alert-danger"><?php echo $error_message; ?></div><?php endif; ?>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="exportModalLabel">Export Students to PDF</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form action="student_export_pdf.php" method="get" target="_blank">
                <div class="modal-body">
                    <p>Select a filter for the PDF export. Leave blank to export all students.</p>
                    <div class="mb-3">
                        <label for="class_level_id_export" class="form-label">Filter by Class</label>
                        <select name="class_level_id" id="class_level_id_export" class="form-select">
                            <option value="">All Classes</option>
                            <?php
                            $class_sql = "SELECT id, name FROM class_levels ORDER BY name";
                            $class_result = $conn->query($class_sql);
                            while($class = $class_result->fetch_assoc()){ echo "<option value='{$class['id']}'>".htmlspecialchars($class['name'])."</option>"; }
                            ?>
                        </select>
                    </div>
                     <div class="mb-3">
                        <label for="stream_id_export" class="form-label">Filter by Stream</label>
                        <select name="stream_id" id="stream_id_export" class="form-select">
                            <option value="">All Streams</option>
                             <?php
                            $stream_sql = "SELECT s.id, s.name, cl.name as class_name FROM streams s JOIN class_levels cl ON s.class_level_id = cl.id ORDER BY cl.name, s.name";
                            $stream_result = $conn->query($stream_sql);
                            while($stream = $stream_result->fetch_assoc()){ echo "<option value='{$stream['id']}'>".htmlspecialchars($stream['class_name'] . ' ' . $stream['name'])."</option>"; }
                            ?>
                        </select>
                    </div>
                    <small class="text-muted">Note: Filtering by stream will override class filter.</small>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-danger"><i class="bi bi-file-earmark-pdf-fill me-2"></i>Export PDF</button></div>
            </form>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
    <thead>
        <tr>
            <th style="width: 5%;">Photo</th>
            <th>Name</th>
            <th>LIN</th>
            <th>Class</th>
            <th>Type</th>
            <th style="width: 15%;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?php if (!empty($row['photo']) && file_exists($row['photo'])): ?>
                            <img src="<?php echo htmlspecialchars($row['photo']); ?>" alt="Photo" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                        <?php else:
                            $name = $row["first_name"] . ' ' . $row["last_name"];
                            $initials = '';
                            $parts = explode(' ', $name);
                            foreach ($parts as $part) { $initials .= strtoupper(substr($part, 0, 1)); }
                        ?>
                            <div class="avatar-initials" style="width: 50px; height: 50px; font-size: 1.2rem; margin: 0 auto;"><?php echo htmlspecialchars($initials); ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($row["first_name"] . ' ' . $row["last_name"]); ?></td>
                    <td><?php echo htmlspecialchars($row["lin"]); ?></td>
                    <td><?php echo htmlspecialchars($row["class_level_name"] . ' ' . $row["stream_name"]); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($row["student_type"])); ?></td>
                    <td>
                        <a href="<?php echo student_view_url($row["id"]); ?>" class="btn btn-sm btn-info">View</a>
                        <a href="<?php echo student_edit_url($row["id"]); ?>" class="btn btn-sm btn-primary">Edit</a>
                        <button type="button" class="btn btn-sm btn-danger unregister-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#unregisterModal"
                                data-student-id="<?php echo $row["id"]; ?>"
                                data-student-name="<?php echo htmlspecialchars($row["first_name"] . ' ' . $row["last_name"]); ?>">
                            Unregister
                        </button>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="text-center">No active students found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>

<!-- Unregister Modal -->
<div class="modal fade" id="unregisterModal" tabindex="-1" aria-labelledby="unregisterModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="students.php" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="unregisterModalLabel">Unregister Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to unregister <strong id="student-name-modal"></strong>?</p>
                    <input type="hidden" name="student_id" id="student-id-modal">
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason for Unregistering</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="unregister_student" class="btn btn-danger">Confirm Unregister</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const unregisterModal = document.getElementById('unregisterModal');
    unregisterModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const studentId = button.getAttribute('data-student-id');
        const studentName = button.getAttribute('data-student-name');

        const modalTitle = unregisterModal.querySelector('.modal-title');
        const modalStudentName = unregisterModal.querySelector('#student-name-modal');
        const modalStudentIdInput = unregisterModal.querySelector('#student-id-modal');

        modalTitle.textContent = 'Unregister ' + studentName;
        modalStudentName.textContent = studentName;
        modalStudentIdInput.value = studentId;
    });
});
</script>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
