<?php
require_once __DIR__ . '/../../config.php';

// Authorization check
$allowed_roles = ['teacher', 'headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: " . dashboard_url());
    exit;
}

$errors = [];
$assignment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($assignment_id === 0) {
    header("location: " . assignments_url());
    exit;
}

// Fetch existing assignment data
$sql = "SELECT * FROM assignments WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $assignment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $assignment = $result->fetch_assoc();
        // Security check: Only owner or admin can edit
        if ($_SESSION['role'] === 'teacher' && $assignment['teacher_id'] != $_SESSION['id']) {
            header("location: " . assignments_url() . "?error=unauthorized");
            exit;
        }
        $title = $assignment['title'];
        $description = $assignment['description'];
        $stream_id = $assignment['stream_id'];
        $subject_id = $assignment['subject_id'];
        $due_date = date('Y-m-d\TH:i', strtotime($assignment['due_date']));
    } else {
        header("location: " . assignments_url() . "?error=notfound");
        exit;
    }
    $stmt->close();
}

// Fetch streams and subjects for dropdowns
$streams = $conn->query("SELECT s.id, s.name, cl.name as class_level_name FROM streams s JOIN class_levels cl ON s.class_level_id = cl.id ORDER BY cl.name, s.name")->fetch_all(MYSQLI_ASSOC);
$subjects = $conn->query("SELECT id, name FROM subjects ORDER BY name")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $stream_id = trim($_POST['stream_id']);
    $subject_id = trim($_POST['subject_id']);
    $due_date = trim($_POST['due_date']);

    // Validation
    if (empty($title)) $errors['title'] = "Title is required.";
    // ... add other validation as needed

    if (empty($errors)) {
        $sql_update = "UPDATE assignments SET title=?, description=?, stream_id=?, subject_id=?, due_date=?, updated_at=NOW() WHERE id=?";
        if ($stmt_update = $conn->prepare($sql_update)) {
            $stmt_update->bind_param("ssiisi", $title, $description, $stream_id, $subject_id, $due_date, $assignment_id);
            if ($stmt_update->execute()) {
                header("location: " . assignments_url());
                exit();
            } else {
                $errors['db'] = "Database error: " . $stmt_update->error;
            }
            $stmt_update->close();
        }
    }
}

require_once __DIR__ . '/../../src/includes/header.php';
?>

<div class="container mt-4">
    <h2>Edit Assignment</h2>
    <a href="<?php echo assignments_url(); ?>" class="btn btn-secondary mb-3">Back to Assignments</a>

    <form action="<?php echo assignment_edit_url($assignment_id); ?>" method="post">
        <?php if(isset($errors['db'])): ?><div class="alert alert-danger"><?php echo $errors['db']; ?></div><?php endif; ?>

        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" name="title" class="form-control <?php echo isset($errors['title']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($title); ?>">
            <?php if(isset($errors['title'])): ?><div class="invalid-feedback"><?php echo $errors['title']; ?></div><?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description / Instructions</label>
            <textarea name="description" class="form-control" rows="5"><?php echo htmlspecialchars($description); ?></textarea>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="stream_id" class="form-label">Class/Stream</label>
                <select name="stream_id" class="form-select">
                    <?php foreach ($streams as $stream): ?>
                        <option value="<?php echo $stream['id']; ?>" <?php echo ($stream_id == $stream['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($stream['class_level_name'] . ' - ' . $stream['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="subject_id" class="form-label">Subject</label>
                <select name="subject_id" class="form-select">
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo $subject['id']; ?>" <?php echo ($subject_id == $subject['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($subject['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label for="due_date" class="form-label">Due Date</label>
            <input type="datetime-local" name="due_date" class="form-control" value="<?php echo htmlspecialchars($due_date); ?>">
        </div>

        <button type="submit" class="btn btn-primary">Update Assignment</button>
    </form>
</div>

<?php
$conn->close();
require_once __DIR__ . '/../../src/includes/footer.php';
?>
