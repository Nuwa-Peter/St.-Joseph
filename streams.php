<?php
require_once 'config.php';

// Authorization check
$allowed_roles = ['admin', 'headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: " . login_url());
    exit;
}

$class_level_id = $_GET["class_level_id"] ?? 0;
if (!$class_level_id) {
    $_SESSION['error_message'] = "No Class Level ID specified.";
    header("location: " . classes_url());
    exit();
}

// Get class level name
$stmt_class_level = $conn->prepare("SELECT name FROM class_levels WHERE id = ?");
$stmt_class_level->bind_param("i", $class_level_id);
$stmt_class_level->execute();
$result_class_level = $stmt_class_level->get_result();
if ($result_class_level->num_rows == 1) {
    $class_level_name = $result_class_level->fetch_assoc()["name"];
} else {
    $_SESSION['error_message'] = "Class Level not found.";
    header("location: " . classes_url());
    exit();
}
$stmt_class_level->close();

// Get all streams for the class level, and the assigned class teacher's name
$sql = "SELECT s.id, s.name, s.class_teacher_id, CONCAT(u.first_name, ' ', u.last_name) as teacher_name
        FROM streams s
        LEFT JOIN users u ON s.class_teacher_id = u.id
        WHERE s.class_level_id = ?
        ORDER BY s.name ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $class_level_id);
$stmt->execute();
$streams_result = $stmt->get_result();
$streams = $streams_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get a list of all teachers to populate the dropdown
$teacher_sql = "SELECT id, first_name, last_name FROM users WHERE role IN ('teacher', 'class teacher', 'headteacher', 'root') ORDER BY last_name ASC";
$teacher_result = $conn->query($teacher_sql);
$teachers = $teacher_result->fetch_all(MYSQLI_ASSOC);

// Fetch session messages
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center my-4">
        <h2 class="text-primary">Manage Streams for <?php echo htmlspecialchars($class_level_name); ?></h2>
        <div>
            <a href="<?php echo stream_create_url(['class_level_id' => $class_level_id]); ?>" class="btn btn-success">Create Stream</a>
            <a href="<?php echo classes_url(); ?>" class="btn btn-secondary">Back to Classes</a>
        </div>
    </div>

    <?php if ($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
    <?php if ($error_message): ?><div class="alert alert-danger"><?php echo $error_message; ?></div><?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Stream Name</th>
                            <th>Class Teacher</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($streams)): ?>
                            <?php foreach($streams as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row["name"]); ?></td>
                                    <td>
                                        <form action="<?php echo assign_class_teacher_url(); ?>" method="post" class="d-flex align-items-center">
                                            <input type="hidden" name="stream_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="class_level_id" value="<?php echo $class_level_id; ?>">
                                            <select name="teacher_id" class="form-select form-select-sm me-2">
                                                <option value="">None</option>
                                                <?php foreach($teachers as $teacher): ?>
                                                    <option value="<?php echo $teacher['id']; ?>" <?php if($row['class_teacher_id'] == $teacher['id']) echo 'selected'; ?>>
                                                        <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                                        </form>
                                    </td>
                                    <td>
                                        <a href="<?php echo stream_edit_url($row["id"]); ?>" class="btn btn-primary btn-sm">Edit Name</a>
                                        <a href="<?php echo stream_delete_url($row["id"], ['class_level_id' => $class_level_id]); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this stream?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center">No streams found for this class level.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
