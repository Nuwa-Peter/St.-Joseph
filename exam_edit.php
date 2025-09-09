<?php
// All dependencies are loaded by index.php

// Authentication and Authorization
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: " . login_url());
    exit;
}
if (!in_array($_SESSION['role'], ['admin', 'headteacher', 'root', 'director'])) {
    $_SESSION['error_message'] = "You are not authorized to access this page.";
    header("location: " . dashboard_url());
    exit;
}

$paper_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$paper_id) {
    $_SESSION['error_message'] = "Invalid or missing exam ID.";
    header("location: " . set_exam_url());
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verify_csrf_token();

    if ($paper_id != $_POST['paper_id']) {
        $_SESSION['error_message'] = "Mismatched exam ID.";
        header("location: " . set_exam_url());
        exit;
    }

    $exam_type = trim($_POST['exam_type']);
    $name = trim($_POST['name']);
    $subject_id = trim($_POST['subject_id']);
    $errors = [];

    if (empty($exam_type)) $errors[] = "Please select an exam type.";
    if (empty($name)) $errors[] = "Please enter an exam name.";

    // Check for duplicate paper name within the same subject, excluding the current paper
    if(empty($errors)) {
        $sql_check = "SELECT id FROM papers WHERE subject_id = ? AND name = ? AND id != ?";
        if ($stmt_check = $conn->prepare($sql_check)) {
            $stmt_check->bind_param("isi", $subject_id, $name, $paper_id);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                $errors[] = "Another exam with this name already exists for this subject.";
            }
            $stmt_check->close();
        }
    }

    if (empty($errors)) {
        $sql_update = "UPDATE papers SET exam_type = ?, name = ?, updated_at = NOW() WHERE id = ?";
        if ($stmt_update = $conn->prepare($sql_update)) {
            $stmt_update->bind_param("ssi", $exam_type, $name, $paper_id);
            if ($stmt_update->execute()) {
                $_SESSION['success_message'] = "Exam updated successfully.";
                header("location: " . set_exam_url());
                exit;
            } else {
                $_SESSION['error_message'] = "Database error: Could not update the exam.";
            }
            $stmt_update->close();
        }
    } else {
        $_SESSION['error_message'] = implode("<br>", $errors);
        $_SESSION['form_data'] = $_POST;
    }

    header("location: " . exam_edit_url($paper_id));
    exit;
}

// Fetch the existing paper details for the form
$sql_fetch = "SELECT p.name, p.exam_type, p.subject_id, s.name as subject_name FROM papers p JOIN subjects s ON p.subject_id = s.id WHERE p.id = ?";
if ($stmt_fetch = $conn->prepare($sql_fetch)) {
    $stmt_fetch->bind_param("i", $paper_id);
    $stmt_fetch->execute();
    $result = $stmt_fetch->get_result();
    if ($paper = $result->fetch_assoc()) {
        $subject_id = $paper['subject_id'];
        $subject_name = $paper['subject_name'];
        $exam_type = $paper['exam_type'];
        $name = $paper['name'];
    } else {
        $_SESSION['error_message'] = "Exam not found.";
        header("location: " . set_exam_url());
        exit;
    }
    $stmt_fetch->close();
}

// Page setup
require_once __DIR__ . '/includes/header.php';

// Retrieve and clear any form data or messages from session
$form_data = $_SESSION['form_data'] ?? [];
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['form_data'], $_SESSION['error_message']);

$exam_type_value = $form_data['exam_type'] ?? $exam_type;
$name_value = $form_data['name'] ?? $name;
$exam_types = ['AOI', 'CA', 'Beginning of Term', 'Midterm', 'End of Term'];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Edit Examination</h2>
    <a href="<?php echo set_exam_url(); ?>" class="btn btn-secondary">Back to Exams List</a>
</div>

<?php if ($error_message): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Editing Exam for <?php echo htmlspecialchars($subject_name); ?></div>
            <div class="card-body">
                <form action="<?php echo exam_edit_url($paper_id); ?>" method="post">
                    <?php echo csrf_input(); ?>
                    <input type="hidden" name="paper_id" value="<?php echo $paper_id; ?>">
                    <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">

                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($subject_name); ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="exam_type" class="form-label">Exam Type</label>
                        <select name="exam_type" id="exam_type" class="form-select" required>
                            <option value="">Select Type...</option>
                            <?php foreach($exam_types as $type): ?>
                                <option value="<?php echo $type; ?>" <?php echo ($exam_type_value == $type) ? 'selected' : ''; ?>><?php echo htmlspecialchars($type); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Exam Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($name_value); ?>" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
