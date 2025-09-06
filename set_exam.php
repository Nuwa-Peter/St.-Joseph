<?php
require_once 'config.php';

// Authorization check
$allowed_roles = ['admin', 'headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: " . login_url());
    exit;
}

$errors = [];
$subject_id = $exam_type = $name = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject_id = trim($_POST['subject_id']);
    $exam_type = trim($_POST['exam_type']);
    $name = trim($_POST['name']);

    if (empty($subject_id)) $errors['subject_id'] = "Please select a subject.";
    if (empty($exam_type)) $errors['exam_type'] = "Please select an exam type.";
    if (empty($name)) $errors['name'] = "Please enter an exam name.";

    // Check for duplicate paper name within the same subject
    if(empty($errors)) {
        $sql_check = "SELECT id FROM papers WHERE subject_id = ? AND name = ?";
        if ($stmt_check = $conn->prepare($sql_check)) {
            $stmt_check->bind_param("is", $subject_id, $name);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                $errors['name'] = "An exam with this name already exists for the selected subject.";
            }
            $stmt_check->close();
        }
    }

    if (empty($errors)) {
        $sql = "INSERT INTO papers (subject_id, exam_type, name, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iss", $subject_id, $exam_type, $name);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Exam '" . htmlspecialchars($name) . "' has been set successfully!";
            } else {
                $_SESSION['error_message'] = "Database error: Could not create the exam.";
            }
            $stmt->close();
        }
    } else {
        $_SESSION['form_errors'] = $errors;
    }
    header("Location: " . set_exam_url());
    exit();
}

// Fetch session messages
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
if(isset($_SESSION['form_errors'])) {
    $errors = array_merge($errors, $_SESSION['form_errors']);
    unset($_SESSION['form_errors']);
}
unset($_SESSION['success_message'], $_SESSION['error_message']);


// Fetch subjects for the dropdown
$subjects_sql = "SELECT id, name FROM subjects ORDER BY name ASC";
$subjects_result = $conn->query($subjects_sql);

// Fetch existing papers to display in a list
$papers_sql = "SELECT p.id, p.name, p.exam_type, s.name as subject_name FROM papers p JOIN subjects s ON p.subject_id = s.id ORDER BY s.name, p.name";
$papers_result = $conn->query($papers_sql);

$exam_types = ['AOI', 'CA', 'Beginning of Term', 'Midterm', 'End of Term'];

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center my-4">
        <h2 class="text-primary"><i class="bi bi-journal-plus me-2"></i>Set Examinations</h2>
    </div>

    <?php if ($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
    <?php if ($error_message): ?><div class="alert alert-danger"><?php echo $error_message; ?></div><?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header">Create New Exam</div>
                <div class="card-body">
                    <form action="<?php echo set_exam_url(); ?>" method="post">
                        <div class="mb-3">
                            <label for="subject_id" class="form-label">Subject</label>
                            <select name="subject_id" id="subject_id" class="form-select <?php echo isset($errors['subject_id']) ? 'is-invalid' : ''; ?>" required>
                                <option value="">Select Subject...</option>
                                <?php if($subjects_result) { while($subject = $subjects_result->fetch_assoc()): ?>
                                    <option value="<?php echo $subject['id']; ?>" <?php echo ($subject_id == $subject['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($subject['name']); ?></option>
                                <?php endwhile; } ?>
                            </select>
                            <?php if(isset($errors['subject_id'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['subject_id']); ?></div><?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="exam_type" class="form-label">Exam Type</label>
                            <select name="exam_type" id="exam_type" class="form-select <?php echo isset($errors['exam_type']) ? 'is-invalid' : ''; ?>" required>
                                <option value="">Select Type...</option>
                                <?php foreach($exam_types as $type): ?>
                                    <option value="<?php echo $type; ?>" <?php echo ($exam_type == $type) ? 'selected' : ''; ?>><?php echo htmlspecialchars($type); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if(isset($errors['exam_type'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['exam_type']); ?></div><?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Exam Name</label>
                            <input type="text" name="name" id="name" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($name); ?>" placeholder="e.g., Term 1 Midterm" required>
                            <?php if(isset($errors['name'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['name']); ?></div><?php endif; ?>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Set Exam</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">Existing Exams</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Subject</th>
                                    <th>Exam Name</th>
                                    <th>Exam Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($papers_result && $papers_result->num_rows > 0): ?>
                                    <?php while($paper = $papers_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($paper['subject_name']); ?></td>
                                            <td><?php echo htmlspecialchars($paper['name']); ?></td>
                                            <td><?php echo htmlspecialchars($paper['exam_type']); ?></td>
                                            <td>
                                                <a href="<?php echo exam_edit_url($paper['id']); ?>" class="btn btn-sm btn-primary">Edit</a>
                                                <a href="<?php echo exam_delete_url($paper['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure? Deleting an exam will also delete all marks entered for it.');">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center">No exams have been set yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
