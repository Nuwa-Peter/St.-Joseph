<?php
// All dependencies like config, session, and helpers are now loaded by index.php
// before the router includes this file.

// Authentication and Authorization
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    // url_helper is loaded in index.php, so login_url() is available
    header("location: " . login_url());
    exit;
}
if (!in_array($_SESSION['role'], ['admin', 'headteacher', 'root', 'director'])) {
    $_SESSION['error_message'] = "You are not authorized to access this page.";
    header("location: " . dashboard_url());
    exit;
}

// Handle POST request for creating a new exam
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // csrf_helper is loaded in index.php
    verify_csrf_token();

    $subject_id = trim($_POST['subject_id']);
    $exam_type = trim($_POST['exam_type']);
    $name = trim($_POST['name']);
    $errors = [];

    if (empty($subject_id)) $errors[] = "Please select a subject.";
    if (empty($exam_type)) $errors[] = "Please select an exam type.";
    if (empty($name)) $errors[] = "Please enter an exam name.";

    // Check for duplicate paper name within the same subject
    if (empty($errors)) {
        // $conn is available globally from the router's scope
        $sql_check = "SELECT id FROM papers WHERE subject_id = ? AND name = ?";
        if ($stmt_check = $conn->prepare($sql_check)) {
            $stmt_check->bind_param("is", $subject_id, $name);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                $errors[] = "An exam with this name already exists for the selected subject.";
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
        $_SESSION['error_message'] = implode("<br>", $errors);
        // Persist form data on error
        $_SESSION['form_data'] = $_POST;
    }

    header("Location: " . set_exam_url());
    exit;
}

// Include header
require_once __DIR__ . '/includes/header.php';

// Retrieve and clear any form data or messages from session
$form_data = $_SESSION['form_data'] ?? [];
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['form_data'], $_SESSION['success_message'], $_SESSION['error_message']);

// Fetch data for the page
$subjects = $conn->query("SELECT id, name FROM subjects ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$papers_sql = "SELECT p.id, p.name, p.exam_type, s.name as subject_name FROM papers p JOIN subjects s ON p.subject_id = s.id ORDER BY s.name, p.name";
$papers = $conn->query($papers_sql)->fetch_all(MYSQLI_ASSOC);
$exam_types = ['AOI', 'CA', 'Beginning of Term', 'Midterm', 'End of Term'];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Set Examinations</h2>
</div>

<?php if ($success_message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>
<?php if ($error_message): ?>
    <div class="alert alert-danger"><?php echo $error_message; // Already includes HTML, so no htmlspecialchars ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Create New Exam</div>
            <div class="card-body">
                <form action="<?php echo set_exam_url(); ?>" method="post">
                    <?php generate_csrf_token_form(); ?>
                    <div class="mb-3">
                        <label for="subject_id" class="form-label">Subject</label>
                        <select name="subject_id" id="subject_id" class="form-select" required>
                            <option value="">Select Subject...</option>
                            <?php foreach($subjects as $subject): ?>
                                <option value="<?php echo $subject['id']; ?>" <?php echo (($form_data['subject_id'] ?? '') == $subject['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($subject['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="exam_type" class="form-label">Exam Type</label>
                        <select name="exam_type" id="exam_type" class="form-select" required>
                            <option value="">Select Type...</option>
                            <?php foreach($exam_types as $type): ?>
                                <option value="<?php echo $type; ?>" <?php echo (($form_data['exam_type'] ?? '') == $type) ? 'selected' : ''; ?>><?php echo htmlspecialchars($type); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Exam Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>" placeholder="e.g., Term 1 Midterm" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Set Exam</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Existing Exams</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Exam Name</th>
                                <th>Exam Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($papers)): ?>
                                <?php foreach($papers as $paper): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($paper['subject_name']); ?></td>
                                        <td><?php echo htmlspecialchars($paper['name']); ?></td>
                                        <td><?php echo htmlspecialchars($paper['exam_type']); ?></td>
                                        <td>
                                            <a href="<?php echo exam_edit_url($paper['id']); ?>" class="btn btn-sm btn-primary">Edit</a>
                                            <a href="<?php echo exam_delete_url($paper['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure? Deleting an exam will also delete all marks entered for it.');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
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

<?php
require_once __DIR__ . '/includes/footer.php';
?>
