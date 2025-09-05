<?php
require_once 'config.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], ['root', 'headteacher'])) {
    header("location: login.php");
    exit;
}

$errors = [];
$success_message = "";
$paper_id = $_GET['id'] ?? null;
$subject_name = $exam_type = $name = "";

if (!$paper_id) {
    header("location: set_exam.php?error=No ID provided");
    exit;
}

// Fetch the existing paper details along with the subject name
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
        header("location: set_exam.php?error=Exam not found");
        exit;
    }
    $stmt_fetch->close();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $exam_type = trim($_POST['exam_type']);
    $name = trim($_POST['name']);
    $paper_id = trim($_POST['paper_id']); // from hidden field

    if (empty($exam_type)) $errors['exam_type'] = "Please select an exam type.";
    if (empty($name)) $errors['name'] = "Please enter an exam name.";

    // Check for duplicate paper name within the same subject, excluding the current paper
    $sql_check = "SELECT id FROM papers WHERE subject_id = ? AND name = ? AND id != ?";
    if ($stmt_check = $conn->prepare($sql_check)) {
        $stmt_check->bind_param("isi", $subject_id, $name, $paper_id);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            $errors['name'] = "Another exam with this name already exists for this subject.";
        }
        $stmt_check->close();
    }

    if (empty($errors)) {
        $sql_update = "UPDATE papers SET exam_type = ?, name = ?, updated_at = NOW() WHERE id = ?";
        if ($stmt_update = $conn->prepare($sql_update)) {
            $stmt_update->bind_param("ssi", $exam_type, $name, $paper_id);
            if ($stmt_update->execute()) {
                header("location: set_exam.php?success=Exam updated successfully");
                exit;
            } else {
                $errors['db'] = "Database error: Could not update the exam.";
            }
            $stmt_update->close();
        }
    }
}

$exam_types = ['AOI', 'CA', 'Beginning of Term', 'Midterm', 'End of Term'];
require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Edit Examination</h2>
    <a href="set_exam.php" class="btn btn-secondary">Back to Exams List</a>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Editing Exam for <?php echo htmlspecialchars($subject_name); ?></div>
            <div class="card-body">
                <?php if(isset($errors['db'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($errors['db']); ?></div><?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $paper_id; ?>" method="post">
                    <input type="hidden" name="paper_id" value="<?php echo $paper_id; ?>">

                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($subject_name); ?>" readonly>
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
                        <input type="text" name="name" id="name" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($name); ?>" required>
                        <?php if(isset($errors['name'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['name']); ?></div><?php endif; ?>
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
$conn->close();
require_once 'includes/footer.php';
?>
