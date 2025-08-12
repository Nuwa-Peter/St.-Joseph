<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'config.php';
require_once 'includes/header.php';

$class_level_id = 0;
$class_level_name = "";

if (isset($_GET["class_level_id"]) && !empty(trim($_GET["class_level_id"]))) {
    $class_level_id = trim($_GET["class_level_id"]);

    // Get class level name
    $sql_class_level = "SELECT name FROM class_levels WHERE id = ?";
    if ($stmt_class_level = $conn->prepare($sql_class_level)) {
        $stmt_class_level->bind_param("i", $class_level_id);
        if ($stmt_class_level->execute()) {
            $result_class_level = $stmt_class_level->get_result();
            if ($result_class_level->num_rows == 1) {
                $row_class_level = $result_class_level->fetch_assoc();
                $class_level_name = $row_class_level["name"];
            } else {
                echo "Class level not found.";
                exit();
            }
        }
        $stmt_class_level->close();
    }


    $sql = "SELECT id, name FROM streams WHERE class_level_id = ? ORDER BY name ASC";
    $result = $conn->prepare($sql);
    $result->bind_param("i", $class_level_id);
    $result->execute();
    $streams = $result->get_result();

} else {
    echo "No Class Level ID specified.";
    exit();
}
?>

<h2>Streams for <?php echo htmlspecialchars($class_level_name); ?></h2>
<a href="stream_create.php?class_level_id=<?php echo $class_level_id; ?>" class="btn btn-success mb-3">Create Stream</a>
<a href="class_levels.php" class="btn btn-secondary mb-3">Back to Class Levels</a>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Name</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($streams->num_rows > 0): ?>
            <?php while($row = $streams->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row["name"]); ?></td>
                    <td>
                        <a href="stream_edit.php?id=<?php echo $row["id"]; ?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="stream_delete.php?id=<?php echo $row["id"]; ?>&class_level_id=<?php echo $class_level_id; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this stream?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="2" class="text-center">No streams found for this class level.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
