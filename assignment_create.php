<?php
require_once 'config.php';

// Authorization check
$allowed_roles = ['teacher', 'headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: dashboard.php");
    exit;
}

$errors = [];
$title = $description = $stream_id = $subject_id = $due_date = "";

// Fetch streams and subjects for dropdowns
$streams = $conn->query("SELECT s.id, s.name, cl.name as class_level_name FROM streams s JOIN class_levels cl ON s.class_level_id = cl.id ORDER BY cl.name, s.name")->fetch_all(MYSQLI_ASSOC);
$subjects = $conn->query("SELECT id, name FROM subjects ORDER BY name")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $stream_id = trim($_POST['stream_id']);
    $subject_id = trim($_POST['subject_id']);
    $due_date = trim($_POST['due_date']);
    $teacher_id = $_SESSION['id'];
    $file_path = null;

    // Validation
    if (empty($title)) $errors['title'] = "Title is required.";
    if (empty($stream_id)) $errors['stream_id'] = "Class/Stream is required.";
    if (empty($subject_id)) $errors['subject_id'] = "Subject is required.";
    if (empty($due_date)) $errors['due_date'] = "Due date is required.";

    // Handle file upload
    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] == 0) {
        $target_dir = "uploads/assignments/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $filename = uniqid() . '_' . basename($_FILES["assignment_file"]["name"]);
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES["assignment_file"]["tmp_name"], $target_file)) {
            $file_path = $target_file;
        } else {
            $errors['file'] = "Sorry, there was an error uploading your file.";
        }
    }

    if (empty($errors)) {
        $sql = "INSERT INTO assignments (title, description, stream_id, subject_id, teacher_id, due_date, file_path, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssiiiss", $title, $description, $stream_id, $subject_id, $teacher_id, $due_date, $file_path);
            if ($stmt->execute()) {
                header("location: assignments.php");
                exit();
            } else {
                $errors['db'] = "Database error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <h2>Create New Assignment</h2>
    <a href="assignments.php" class="btn btn-secondary mb-3">Back to Assignments</a>

    <form action="assignment_create.php" method="post" enctype="multipart/form-data">
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
                <select name="stream_id" class="form-select <?php echo isset($errors['stream_id']) ? 'is-invalid' : ''; ?>">
                    <option value="">Select a class...</option>
                    <?php foreach ($streams as $stream): ?>
                        <option value="<?php echo $stream['id']; ?>" <?php echo ($stream_id == $stream['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($stream['class_level_name'] . ' - ' . $stream['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if(isset($errors['stream_id'])): ?><div class="invalid-feedback"><?php echo $errors['stream_id']; ?></div><?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label for="subject_id" class="form-label">Subject</label>
                <select name="subject_id" class="form-select <?php echo isset($errors['subject_id']) ? 'is-invalid' : ''; ?>">
                    <option value="">Select a subject...</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo $subject['id']; ?>" <?php echo ($subject_id == $subject['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($subject['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if(isset($errors['subject_id'])): ?><div class="invalid-feedback"><?php echo $errors['subject_id']; ?></div><?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="due_date" class="form-label">Due Date</label>
                <input type="datetime-local" name="due_date" class="form-control <?php echo isset($errors['due_date']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($due_date); ?>">
                <?php if(isset($errors['due_date'])): ?><div class="invalid-feedback"><?php echo $errors['due_date']; ?></div><?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label for="assignment_file" class="form-label">Attach File (Optional)</label>
                <input type="file" name="assignment_file" class="form-control <?php echo isset($errors['file']) ? 'is-invalid' : ''; ?>">
                <?php if(isset($errors['file'])): ?><div class="invalid-feedback"><?php echo $errors['file']; ?></div><?php endif; ?>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Create Assignment</button>
    </form>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
