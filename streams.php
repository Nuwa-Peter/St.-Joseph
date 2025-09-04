<?php

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'config.php';

$class_level_id = 0;
$class_level_name = "";

if (isset($_GET["class_level_id"]) && !empty(trim($_GET["class_level_id"]))) {
    $class_level_id = trim($_GET["class_level_id"]);

    // Get class level name
    $sql_class_level = "SELECT name FROM class_levels WHERE id = ?";
    if ($stmt_class_level = $conn->prepare($sql_class_level)) {
        $stmt_class_level->bind_param("i", $class_level_id);
        $stmt_class_level->execute();
        $result_class_level = $stmt_class_level->get_result();
        if ($result_class_level->num_rows == 1) {
            $row_class_level = $result_class_level->fetch_assoc();
            $class_level_name = $row_class_level["name"];
        } else {
            // Handle error
        }
        $stmt_class_level->close();
    }

    // Get all streams for the class level, and the assigned class teacher's name
    $sql = "SELECT s.id, s.name, s.class_teacher_id, CONCAT(u.first_name, ' ', u.last_name) as teacher_name
            FROM streams s
            LEFT JOIN users u ON s.class_teacher_id = u.id
            WHERE s.class_level_id = ?
            ORDER BY s.name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $class_level_id);
    $stmt->execute();
    $streams = $stmt->get_result();

    // Get a list of all teachers to populate the dropdown
    $teacher_sql = "SELECT id, first_name, last_name FROM users WHERE role IN ('teacher', 'class teacher', 'headteacher', 'root') ORDER BY last_name ASC";
    $teacher_result = $conn->query($teacher_sql);
    $teachers = $teacher_result->fetch_all(MYSQLI_ASSOC);

} else {
    // Handle error
    exit("No Class Level ID specified.");
}

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <h2 class="my-4">Manage Streams for <?php echo htmlspecialchars($class_level_name); ?></h2>
    <a href="stream_create.php?class_level_id=<?php echo $class_level_id; ?>" class="btn btn-success mb-3">Create Stream</a>
    <a href="class_levels.php" class="btn btn-secondary mb-3">Back to Classes</a>

    <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Stream Name</th>
                        <th>Class Teacher</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($streams->num_rows > 0): ?>
                        <?php while($row = $streams->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row["name"]); ?></td>
                                <td>
                                    <form action="assign_class_teacher.php" method="post" class="d-flex align-items-center">
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
                                    <a href="stream_edit.php?id=<?php echo $row["id"]; ?>" class="btn btn-primary btn-sm">Edit Name</a>
                                    <a href="stream_delete.php?id=<?php echo $row["id"]; ?>&class_level_id=<?php echo $class_level_id; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this stream?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
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

<?php
$conn->close();
require_once 'includes/footer.php';
?>
