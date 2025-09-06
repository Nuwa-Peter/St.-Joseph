<?php
require_once 'config.php';

// Authorization check
$allowed_roles = ['admin', 'headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: " . login_url());
    exit;
}

$errors = [];
$student_id = $_REQUEST['id'] ?? 0;
if(!$student_id) {
    $_SESSION['error_message'] = "No student ID specified.";
    header("location: " . students_url());
    exit();
}

// --- HANDLE POST REQUEST (FORM SUBMISSION) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username_readonly']); // Username is not editable
    $lin = trim($_POST['lin']);
    $phone_number = trim($_POST['phone_number']);
    $date_of_birth = trim($_POST['date_of_birth']);
    $gender = trim($_POST['gender']);
    $student_type = trim($_POST['student_type']);
    $class_level_id = trim($_POST['class_level_id']);
    $stream_id = trim($_POST['stream_id']);
    $photo_current = $_POST['photo_current'];

    if (empty($first_name)) $errors['first_name'] = "First name is required.";
    if (empty($last_name)) $errors['last_name'] = "Last name is required.";
    if (empty($class_level_id)) $errors['class_level_id'] = "Class is required.";
    if (empty($stream_id)) $errors['stream_id'] = "Stream is required.";

    if (empty($errors)) {
        $conn->begin_transaction();
        try {
            $photo_path = $photo_current;
            if (!empty($_POST['cropped_photo_data'])) {
                $data = $_POST['cropped_photo_data'];
                list($type, $data) = explode(';', $data);
                list(, $data)      = explode(',', $data);
                $data = base64_decode($data);
                $target_dir = "uploads/photos/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
                $photo_path = $target_dir . $username . '_' . uniqid() . '.png';
                file_put_contents($photo_path, $data);
            }

            $sql_user = "UPDATE users SET first_name=?, last_name=?, lin=?, gender=?, phone_number=?, date_of_birth=?, student_type=?, photo=?, updated_at=NOW() WHERE id=?";
            $stmt_user = $conn->prepare($sql_user);
            $stmt_user->bind_param("ssssssssi", $first_name, $last_name, $lin, $gender, $phone_number, $date_of_birth, $student_type, $photo_path, $student_id);
            $stmt_user->execute();
            $stmt_user->close();

            $sql_stream = "INSERT INTO stream_user (user_id, stream_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW()) ON DUPLICATE KEY UPDATE stream_id=VALUES(stream_id), updated_at=NOW()";
            $stmt_stream = $conn->prepare($sql_stream);
            $stmt_stream->bind_param("ii", $student_id, $stream_id);
            $stmt_stream->execute();
            $stmt_stream->close();

            $conn->commit();
            $_SESSION['success_message'] = "Student details updated successfully.";
            header("location: " . student_view_url($student_id));
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error_message'] = "Database error: " . $e->getMessage();
        }
    } else {
        $_SESSION['form_errors'] = $errors;
    }
    header("Location: " . student_edit_url($student_id));
    exit();
}

// --- HANDLE GET REQUEST (DISPLAY FORM) ---
$sql = "SELECT u.*, s.class_level_id, su.stream_id FROM users u LEFT JOIN stream_user su ON u.id = su.user_id LEFT JOIN streams s ON su.stream_id = s.id WHERE u.id = ? AND u.role = 'student'";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $student = $result->fetch_assoc();
    } else {
        $_SESSION['error_message'] = "Student not found.";
        header("location: " . students_url());
        exit();
    }
    $stmt->close();
}

$class_levels_sql = "SELECT id, name FROM class_levels ORDER BY name ASC";
$class_levels_result = $conn->query($class_levels_sql);

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <h2>Edit Student</h2>
    <form id="student-form" action="<?php echo student_edit_url($student_id); ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $student_id; ?>">
        <input type="hidden" name="username_readonly" value="<?php echo htmlspecialchars($student['username']); ?>">
        <input type="hidden" name="photo_current" value="<?php echo htmlspecialchars($student['photo']); ?>">
        <input type="hidden" name="cropped_photo_data" id="cropped-photo-data">
        <div class="card shadow-sm">
            <div class="card-header">Student Details</div>
            <div class="card-body">
                <!-- Form fields here, pre-filled with $student data -->
                 <div class="row">
                    <div class="col-md-4 mb-3"><label>First Name</label><input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($student['first_name']); ?>"></div>
                    <div class="col-md-4 mb-3"><label>Surname</label><input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($student['last_name']); ?>"></div>
                    <div class="col-md-4 mb-3"><label>Username</label><input type="text" class="form-control" value="<?php echo htmlspecialchars($student['username']); ?>" readonly></div>
                </div>
                <!-- ... other fields ... -->
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" id="submit-button" class="btn btn-primary">Update Student</button>
            <a href="<?php echo student_view_url($student_id); ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<!-- Modals and Scripts -->
<?php require_once 'includes/footer.php'; ?>
<script src="<?php echo url('assets/libs/cropperjs/cropper.min.js'); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const classSelect = document.getElementById('class_level_id');
    const streamSelect = document.getElementById('stream_id');
    const selectedStreamId = '<?php echo $student["stream_id"]; ?>';

    function fetchStreams(classId, preselectStreamId) {
        streamSelect.innerHTML = '<option value="">Loading...</option>';
        streamSelect.disabled = true;

        if (classId) {
            fetch(`<?php echo url('api/get_streams'); ?>?class_level_id=${classId}`)
                .then(response => response.json())
                .then(data => {
                    streamSelect.innerHTML = '<option value="">Select a stream...</option>';
                    data.forEach(stream => {
                        const option = document.createElement('option');
                        option.value = stream.id;
                        option.textContent = stream.name;
                        if (stream.id == preselectStreamId) {
                            option.selected = true;
                        }
                        streamSelect.appendChild(option);
                    });
                    streamSelect.disabled = false;
                });
        } else {
            streamSelect.innerHTML = '<option value="">Select a class first...</option>';
        }
    }

    if (classSelect.value) {
        fetchStreams(classSelect.value, selectedStreamId);
    }

    classSelect.addEventListener('change', function() {
        fetchStreams(this.value, null);
    });

    // LIN and Photo scripts...
});
</script>
