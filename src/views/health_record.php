<?php
require_once __DIR__ . '/../../config.php';

// Access control
$allowed_roles = ['root', 'headteacher', 'nurse']; // Added 'nurse' for future use
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: dashboard.php");
    exit;
}

// Get Student ID
if (!isset($_GET['student_id']) || empty($_GET['student_id'])) {
    header("location: students.php");
    exit;
}
$student_id = (int)$_GET['student_id'];

$success_message = "";
$error_message = "";

// Handle form submission (UPSERT logic)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_health_record'])) {
    $allergies = trim($_POST['allergies']);
    $chronic_conditions = trim($_POST['chronic_conditions']);
    $emergency_contact_name = trim($_POST['emergency_contact_name']);
    $emergency_contact_phone = trim($_POST['emergency_contact_phone']);
    $notes = trim($_POST['notes']);

    // Check if a record already exists
    $sql_check = "SELECT id FROM health_records WHERE user_id = ?";
    if ($stmt_check = $conn->prepare($sql_check)) {
        $stmt_check->bind_param("i", $student_id);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            // Update existing record
            $sql_update = "UPDATE health_records SET allergies = ?, chronic_conditions = ?, emergency_contact_name = ?, emergency_contact_phone = ?, notes = ?, updated_at = NOW() WHERE user_id = ?";
            if ($stmt_update = $conn->prepare($sql_update)) {
                $stmt_update->bind_param("sssssi", $allergies, $chronic_conditions, $emergency_contact_name, $emergency_contact_phone, $notes, $student_id);
                if ($stmt_update->execute()) {
                    $success_message = "Health record updated successfully.";
                } else {
                    $error_message = "Error updating record: " . $stmt_update->error;
                }
                $stmt_update->close();
            }
        } else {
            // Insert new record
            $sql_insert = "INSERT INTO health_records (user_id, allergies, chronic_conditions, emergency_contact_name, emergency_contact_phone, notes, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
            if ($stmt_insert = $conn->prepare($sql_insert)) {
                $stmt_insert->bind_param("isssss", $student_id, $allergies, $chronic_conditions, $emergency_contact_name, $emergency_contact_phone, $notes);
                if ($stmt_insert->execute()) {
                    $success_message = "Health record created successfully.";
                } else {
                    $error_message = "Error creating record: " . $stmt_insert->error;
                }
                $stmt_insert->close();
            }
        }
        $stmt_check->close();
    }
}

require_once __DIR__ . '/../../src/includes/header.php';

// Fetch student details
$student_name = '';
$sql_student = "SELECT first_name, last_name FROM users WHERE id = ?";
if ($stmt_student = $conn->prepare($sql_student)) {
    $stmt_student->bind_param("i", $student_id);
    $stmt_student->execute();
    $result_student = $stmt_student->get_result();
    if ($row = $result_student->fetch_assoc()) {
        $student_name = $row['first_name'] . ' ' . $row['last_name'];
    } else {
        echo "<div class='container mt-4'><div class='alert alert-danger'>Student not found.</div></div>";
        require_once __DIR__ . '/../../src/includes/footer.php';
        exit;
    }
    $stmt_student->close();
}

// Fetch existing health record
$health_record = null;
$sql_health = "SELECT * FROM health_records WHERE user_id = ?";
if ($stmt_health = $conn->prepare($sql_health)) {
    $stmt_health->bind_param("i", $student_id);
    $stmt_health->execute();
    $result_health = $stmt_health->get_result();
    if ($result_health->num_rows === 1) {
        $health_record = $result_health->fetch_assoc();
    }
    $stmt_health->close();
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0"><i class="bi bi-heart-pulse-fill me-2"></i>Health Record for <?php echo htmlspecialchars($student_name); ?></h2>
            <a href="student_view.php?id=<?php echo $student_id; ?>" class="text-decoration-none text-muted-hover"><i class="bi bi-arrow-left-circle me-1"></i>Back to Student Profile</a>
        </div>
    </div>

    <?php if(!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert"><?php echo $success_message; ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
    <?php endif; ?>
    <?php if(!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert"><?php echo $error_message; ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form action="health_record.php?student_id=<?php echo $student_id; ?>" method="post">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="allergies" class="form-label">Allergies</label>
                        <textarea class="form-control" id="allergies" name="allergies" rows="3" placeholder="e.g., Peanuts, Pollen, Penicillin"><?php echo htmlspecialchars($health_record['allergies'] ?? ''); ?></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="chronic_conditions" class="form-label">Chronic Conditions</label>
                        <textarea class="form-control" id="chronic_conditions" name="chronic_conditions" rows="3" placeholder="e.g., Asthma, Diabetes"><?php echo htmlspecialchars($health_record['chronic_conditions'] ?? ''); ?></textarea>
                    </div>
                </div>
                <hr>
                <h5>Emergency Contact</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="emergency_contact_name" class="form-label">Contact Name</label>
                        <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name" value="<?php echo htmlspecialchars($health_record['emergency_contact_name'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="emergency_contact_phone" class="form-label">Contact Phone</label>
                        <input type="text" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone" value="<?php echo htmlspecialchars($health_record['emergency_contact_phone'] ?? ''); ?>" required>
                    </div>
                </div>
                <hr>
                 <div class="mb-3">
                    <label for="notes" class="form-label">Additional Medical Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="4" placeholder="Any other relevant medical information..."><?php echo htmlspecialchars($health_record['notes'] ?? ''); ?></textarea>
                </div>
                <button type="submit" name="save_health_record" class="btn btn-primary"><i class="bi bi-save-fill me-1"></i>Save Health Record</button>
            </form>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once __DIR__ . '/../../src/includes/footer.php';
?>
