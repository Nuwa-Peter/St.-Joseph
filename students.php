<?php
session_start();
require_once 'config.php';
require_once 'includes/header.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Fetch all students with their class and stream
$sql = "
    SELECT
        u.id,
        u.first_name,
        u.last_name,
        u.lin,
        u.student_type,
        s.name AS stream_name,
        cl.name AS class_level_name
    FROM users u
    LEFT JOIN stream_user su ON u.id = su.user_id
    LEFT JOIN streams s ON su.stream_id = s.id
    LEFT JOIN class_levels cl ON s.class_level_id = cl.id
    WHERE u.role = 'student'
    ORDER BY u.last_name, u.first_name
";

$result = $conn->query($sql);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>All Students</h2>
    <div>
        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#exportModal">
            Export to PDF
        </button>
        <a href="student_create.php" class="btn btn-success">Add New Student</a>
        <a href="student_import_export.php" class="btn btn-info">Import / Export Students</a>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">Export Students to PDF</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="student_export_pdf.php" method="get" target="_blank">
                <div class="modal-body">
                    <p>Select a filter for the PDF export. Leave blank to export all students.</p>
                    <div class="mb-3">
                        <label for="class_level_id" class="form-label">Filter by Class</label>
                        <select name="class_level_id" id="class_level_id" class="form-select">
                            <option value="">All Classes</option>
                            <?php
                            $class_sql = "SELECT id, name FROM class_levels ORDER BY name";
                            $class_result = $conn->query($class_sql);
                            while($class = $class_result->fetch_assoc()){
                                echo "<option value='{$class['id']}'>".htmlspecialchars($class['name'])."</option>";
                            }
                            ?>
                        </select>
                    </div>
                     <div class="mb-3">
                        <label for="stream_id" class="form-label">Filter by Stream</label>
                        <select name="stream_id" id="stream_id" class="form-select">
                            <option value="">All Streams</option>
                             <?php
                            $stream_sql = "SELECT s.id, s.name, cl.name as class_name FROM streams s JOIN class_levels cl ON s.class_level_id = cl.id ORDER BY cl.name, s.name";
                            $stream_result = $conn->query($stream_sql);
                            while($stream = $stream_result->fetch_assoc()){
                                echo "<option value='{$stream['id']}'>".htmlspecialchars($stream['class_name'] . ' ' . $stream['name'])."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <small class="text-muted">Note: Filtering by stream will override class filter.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Export PDF</button>
                </div>
            </form>
        </div>
    </div>
</div>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>LIN</th>
            <th>Class</th>
            <th>Type</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row["first_name"] . ' ' . $row["last_name"]); ?></td>
                    <td><?php echo htmlspecialchars($row["lin"]); ?></td>
                    <td><?php echo htmlspecialchars($row["class_level_name"] . ' ' . $row["stream_name"]); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($row["student_type"])); ?></td>
                    <td>
                        <a href="student_view.php?id=<?php echo $row["id"]; ?>" class="btn btn-sm btn-info">View</a>
                        <a href="student_edit.php?id=<?php echo $row["id"]; ?>" class="btn btn-sm btn-primary">Edit</a>
                        <a href="user_delete.php?id=<?php echo $row["id"]; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="text-center">No students found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
