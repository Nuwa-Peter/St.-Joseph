<?php
require_once 'config.php';

// Authorization check
$allowed_roles = ['admin', 'headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: " . login_url());
    exit;
}

$errors = [];
$first_name = $last_name = $lin = $phone_number = $date_of_birth = $gender = $student_type = $class_level_id = $stream_id = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $lin = trim($_POST['lin']);
    $phone_number = trim($_POST['phone_number']);
    $date_of_birth = trim($_POST['date_of_birth']);
    $gender = trim($_POST['gender']);
    $student_type = trim($_POST['student_type']);
    $class_level_id = trim($_POST['class_level_id']);
    $stream_id = trim($_POST['stream_id']);

    if (empty($first_name)) $errors['first_name'] = "First name is required.";
    if (empty($last_name)) $errors['last_name'] = "Last name is required.";
    if (empty($class_level_id)) $errors['class_level_id'] = "Class is required.";
    if (empty($stream_id)) $errors['stream_id'] = "Stream is required.";

    $base_username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $first_name . '.' . $last_name));
    $username = $base_username;
    $i = 1;
    while(true) {
        $sql_check = "SELECT id FROM users WHERE username = ?";
        if($stmt_check = $conn->prepare($sql_check)){
            $stmt_check->bind_param("s", $username);
            $stmt_check->execute();
            $stmt_check->store_result();
            if($stmt_check->num_rows == 0) { $stmt_check->close(); break; }
            $stmt_check->close();
            $username = $base_username . $i++;
        }
    }

    if (empty($errors)) {
        $conn->begin_transaction();
        try {
            $default_password = password_hash('password123', PASSWORD_DEFAULT);
            $role = 'student';
            $email = null;
            $photo_path = null;

            if (!empty($_POST['cropped_photo_data'])) {
                $data = $_POST['cropped_photo_data'];
                list($type, $data) = explode(';', $data);
                list(, $data) = explode(',', $data);
                $data = base64_decode($data);
                $target_dir = "uploads/photos/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
                $photo_path = $target_dir . $username . '_' . uniqid() . '.png';
                file_put_contents($photo_path, $data);
            } elseif (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
                $target_dir = "uploads/photos/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
                $file_ext = pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION);
                $photo_path = $target_dir . $username . '_' . uniqid() . '.' . $file_ext;
                move_uploaded_file($_FILES["photo"]["tmp_name"], $photo_path);
            }

            $sql_user = "INSERT INTO users (first_name, last_name, username, lin, email, password, role, gender, phone_number, date_of_birth, student_type, photo, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $stmt_user = $conn->prepare($sql_user);
            $stmt_user->bind_param("ssssssssssss", $first_name, $last_name, $username, $lin, $email, $default_password, $role, $gender, $phone_number, $date_of_birth, $student_type, $photo_path);
            $stmt_user->execute();
            $new_user_id = $stmt_user->insert_id;
            $stmt_user->close();

            $sql_stream = "INSERT INTO stream_user (user_id, stream_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";
            $stmt_stream = $conn->prepare($sql_stream);
            $stmt_stream->bind_param("ii", $new_user_id, $stream_id);
            $stmt_stream->execute();
            $stmt_stream->close();

            $conn->commit();
            $_SESSION['success_message'] = "Student created successfully.";
            header("location: " . students_url());
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $errors['db'] = "Database error: " . $e->getMessage();
        }
    }
}

$class_levels_sql = "SELECT id, name FROM class_levels ORDER BY name ASC";
$class_levels_result = $conn->query($class_levels_sql);

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
        <h2 class="mb-3 mb-md-0">Add New Student</h2>
        <a href="<?php echo student_import_export_url(); ?>" class="btn btn-outline-primary">
            <i class="bi bi-file-earmark-spreadsheet-fill me-2"></i>Add Students in Bulk
        </a>
    </div>

    <form id="student-form" action="<?php echo student_create_url(); ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="cropped_photo_data" id="cropped-photo-data">
        <div class="card shadow-sm">
            <div class="card-header">Student Details</div>
            <div class="card-body">
                 <?php if(isset($errors['db'])): ?><div class="alert alert-danger"><?php echo $errors['db']; ?></div><?php endif; ?>
                <div class="row">
                    <div class="col-md-6 mb-3"><label>First Name</label><input type="text" name="first_name" class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($first_name); ?>"><?php if(isset($errors['first_name'])): ?><div class="invalid-feedback"><?php echo $errors['first_name']; ?></div><?php endif; ?></div>
                    <div class="col-md-6 mb-3"><label>Surname</label><input type="text" name="last_name" class="form-control <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($last_name); ?>"><?php if(isset($errors['last_name'])): ?><div class="invalid-feedback"><?php echo $errors['last_name']; ?></div><?php endif; ?></div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label for="lin" class="form-label">LIN (Learner ID Number)</label><input type="text" name="lin" id="lin" class="form-control" value="<?php echo htmlspecialchars($lin); ?>"><div id="lin-feedback" class="invalid-feedback"></div></div>
                    <div class="col-md-6 mb-3"><label>Parent/Guardian Phone</label><input type="text" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($phone_number); ?>"></div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label>Date of Birth</label><input type="date" name="date_of_birth" class="form-control" value="<?php echo htmlspecialchars($date_of_birth); ?>"></div>
                    <div class="col-md-6 mb-3"><label>Gender</label><select name="gender" class="form-select"><option value="Male" <?php echo ($gender == 'Male') ? 'selected' : ''; ?>>Male</option><option value="Female" <?php echo ($gender == 'Female') ? 'selected' : ''; ?>>Female</option></select></div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3"><label>Student Type</label><select name="student_type" class="form-select"><option value="day" <?php echo ($student_type == 'day') ? 'selected' : ''; ?>>Day</option><option value="boarding" <?php echo ($student_type == 'boarding') ? 'selected' : ''; ?>>Boarding</option></select></div>
                    <div class="col-md-4 mb-3"><label>Class</label><select name="class_level_id" id="class_level_id" class="form-select <?php echo isset($errors['class_level_id']) ? 'is-invalid' : ''; ?>"><option value="">Select a class...</option><?php if($class_levels_result) { mysqli_data_seek($class_levels_result, 0); while($class = $class_levels_result->fetch_assoc()): ?><option value="<?php echo $class['id']; ?>" <?php echo ($class_level_id == $class['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($class['name']); ?></option><?php endwhile; } ?></select><?php if(isset($errors['class_level_id'])): ?><div class="invalid-feedback"><?php echo $errors['class_level_id']; ?></div><?php endif; ?></div>
                    <div class="col-md-4 mb-3"><label>Stream</label><select name="stream_id" id="stream_id" class="form-select <?php echo isset($errors['stream_id']) ? 'is-invalid' : ''; ?>" disabled><option value="">Select a class first...</option></select><?php if(isset($errors['stream_id'])): ?><div class="invalid-feedback"><?php echo $errors['stream_id']; ?></div><?php endif; ?></div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-3"><label class="form-label">Student Photo</label><div class="input-group"><input type="file" name="photo" class="form-control" id="photo-input" accept="image/*"><button class="btn btn-outline-secondary" type="button" id="webcam-button">Take Photo</button></div><img id="preview" src="#" alt="Image Preview" class="mt-2" style="display:none; max-width: 150px;"/></div>
                </div>
            </div>
        </div>
        <div class="mt-3"><button type="submit" id="submit-button" class="btn btn-primary">Add Student</button> <a href="<?php echo students_url(); ?>" class="btn btn-secondary">Cancel</a></div>
    </form>
</div>

<!-- Modals and other scripts -->
<?php require_once 'includes/footer.php'; ?>
<script src="<?php echo url('assets/libs/cropperjs/cropper.min.js'); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const classSelect = document.getElementById('class_level_id');
    const streamSelect = document.getElementById('stream_id');

    classSelect.addEventListener('change', function() {
        const classId = this.value;
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
                        streamSelect.appendChild(option);
                    });
                    streamSelect.disabled = false;
                });
        } else {
            streamSelect.innerHTML = '<option value="">Select a class first...</option>';
        }
    });

    const linInput = document.getElementById('lin');
    const linFeedback = document.getElementById('lin-feedback');
    let debounceTimer;
    linInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const lin = this.value;
        if (lin.length < 3) { linInput.classList.remove('is-invalid', 'is-valid'); return; }
        debounceTimer = setTimeout(() => {
            fetch(`<?php echo url('api/check_lin'); ?>?lin=${encodeURIComponent(lin)}`)
                .then(response => response.json())
                .then(data => {
                    linInput.classList.toggle('is-invalid', !data.unique);
                    linInput.classList.toggle('is-valid', data.unique);
                    if(!data.unique) linFeedback.textContent = 'This LIN is already in use.';
                });
        }, 500);
    });
    // Cropper and webcam JS here...
});
</script>
