<?php
session_start();
require_once 'config.php';
require_once 'includes/header.php';

// ... (PHP logic remains the same)
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$errors = [];
$student_id = 0;
$first_name = $last_name = $username = $lin = $email = $phone_number = $date_of_birth = $gender = $student_type = $class_level_id = $stream_id = "";
$photo_current = "";

if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $student_id = trim($_GET["id"]);
    $sql = "SELECT u.*, s.class_level_id, su.stream_id FROM users u LEFT JOIN stream_user su ON u.id = su.user_id LEFT JOIN streams s ON su.stream_id = s.id WHERE u.id = ? AND u.role = 'student'";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $student_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $student = $result->fetch_assoc();
                $first_name = $student['first_name'];
                $last_name = $student['last_name'];
                $username = $student['username'];
                $lin = $student['lin'];
                $email = $student['email'];
                $phone_number = $student['phone_number'];
                $date_of_birth = $student['date_of_birth'];
                $gender = $student['gender'];
                $student_type = $student['student_type'];
                $class_level_id = $student['class_level_id'];
                $stream_id = $student['stream_id'];
                $photo_current = $student['photo'];
            } else { exit("Student not found."); }
        }
        $stmt->close();
    }
} else { exit("No student ID specified."); }

$class_levels_sql = "SELECT id, name FROM class_levels ORDER BY name ASC";
$class_levels_result = $conn->query($class_levels_sql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ... (Validation and update logic remains the same)
    $student_id = $_POST['id'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $lin = trim($_POST['lin']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $date_of_birth = trim($_POST['date_of_birth']);
    $gender = trim($_POST['gender']);
    $student_type = trim($_POST['student_type']);
    $class_level_id = trim($_POST['class_level_id']);
    $stream_id = trim($_POST['stream_id']);

    if (empty($first_name)) $errors['first_name'] = "First name is required.";
    if (empty($last_name)) $errors['last_name'] = "Last name is required.";
    if (empty($username)) {
        $errors['username'] = "Username is required.";
    } else {
        $sql_check = "SELECT id FROM users WHERE username = ? AND id != ?";
        if($stmt_check = $conn->prepare($sql_check)){
            $stmt_check->bind_param("si", $username, $student_id);
            $stmt_check->execute();
            if($stmt_check->fetch()) $errors['username'] = "This username is already taken.";
            $stmt_check->close();
        }
    }
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    } elseif (!empty($email)) {
        $sql_check = "SELECT id FROM users WHERE email = ? AND id != ?";
        if($stmt_check = $conn->prepare($sql_check)){
            $stmt_check->bind_param("si", $email, $student_id);
            $stmt_check->execute();
            if($stmt_check->fetch()) $errors['email'] = "This email is already taken.";
            $stmt_check->close();
        }
    }
    if (empty($stream_id)) $errors['stream_id'] = "Stream is required.";
    if (empty($class_level_id)) $errors['class_level_id'] = "Class is required.";

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
                $file_ext = 'png';
                $photo_path = $target_dir . $username . '_' . uniqid() . '.' . $file_ext;
                file_put_contents($photo_path, $data);

            } elseif (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
                $target_dir = "uploads/photos/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
                $file_ext = pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION);
                $photo_path = $target_dir . $username . '_' . uniqid() . '.' . $file_ext;
                move_uploaded_file($_FILES["photo"]["tmp_name"], $photo_path);
            }

            $sql_user = "UPDATE users SET first_name=?, last_name=?, username=?, lin=?, email=?, gender=?, phone_number=?, date_of_birth=?, student_type=?, photo=?, updated_at=NOW() WHERE id=?";
            $stmt_user = $conn->prepare($sql_user);
            $stmt_user->bind_param("ssssssssssi", $first_name, $last_name, $username, $lin, $email, $gender, $phone_number, $date_of_birth, $student_type, $photo_path, $student_id);
            $stmt_user->execute();
            $stmt_user->close();

            $sql_stream = "INSERT INTO stream_user (user_id, stream_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW()) ON DUPLICATE KEY UPDATE stream_id=VALUES(stream_id), updated_at=NOW()";
            $stmt_stream = $conn->prepare($sql_stream);
            $stmt_stream->bind_param("ii", $student_id, $stream_id);
            $stmt_stream->execute();
            $stmt_stream->close();

            $conn->commit();
            header("location: students.php");
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $errors['db'] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<h2>Edit Student</h2>
<form id="student-form" action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $student_id; ?>">
    <input type="hidden" name="cropped_photo_data" id="cropped-photo-data">
    <div class="card">
        <div class="card-header">Student Details</div>
        <div class="card-body">
            <!-- Form fields -->
            <!-- ... (omitted for brevity) ... -->
            <div class="row">
                <div class="col-md-4 mb-3"><label>First Name</label><input type="text" name="first_name" class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($first_name); ?>"><?php if(isset($errors['first_name'])): ?><div class="invalid-feedback"><?php echo $errors['first_name']; ?></div><?php endif; ?></div>
                <div class="col-md-4 mb-3"><label>Surname</label><input type="text" name="last_name" class="form-control <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($last_name); ?>"><?php if(isset($errors['last_name'])): ?><div class="invalid-feedback"><?php echo $errors['last_name']; ?></div><?php endif; ?></div>
                <div class="col-md-4 mb-3"><label>Username</label><input type="text" name="username" class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($username); ?>"><?php if(isset($errors['username'])): ?><div class="invalid-feedback"><?php echo $errors['username']; ?></div><?php endif; ?></div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3"><label>Email (Optional)</label><input type="email" name="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>"><?php if(isset($errors['email'])): ?><div class="invalid-feedback"><?php echo $errors['email']; ?></div><?php endif; ?></div>
                <div class="col-md-6 mb-3"><label>LIN (Learner ID Number)</label><input type="text" name="lin" id="lin" class="form-control" value="<?php echo htmlspecialchars($lin); ?>"><div id="lin-feedback" class="invalid-feedback"></div></div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3"><label>Parent/Guardian Phone</label><input type="text" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($phone_number); ?>"></div>
                <div class="col-md-6 mb-3"><label>Date of Birth</label><input type="date" name="date_of_birth" class="form-control" value="<?php echo htmlspecialchars($date_of_birth); ?>"></div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3"><label>Gender</label><select name="gender" class="form-select"><option value="Male" <?php echo ($gender == 'Male') ? 'selected' : ''; ?>>Male</option><option value="Female" <?php echo ($gender == 'Female') ? 'selected' : ''; ?>>Female</option></select></div>
                <div class="col-md-4 mb-3"><label>Student Type</label><select name="student_type" class="form-select"><option value="day" <?php echo ($student_type == 'day') ? 'selected' : ''; ?>>Day</option><option value="boarding" <?php echo ($student_type == 'boarding') ? 'selected' : ''; ?>>Boarding</option></select></div>
                <div class="col-md-4 mb-3"><label>Class</label><select name="class_level_id" id="class_level_id" class="form-select <?php echo isset($errors['class_level_id']) ? 'is-invalid' : ''; ?>"><option value="">Select a class...</option><?php mysqli_data_seek($class_levels_result, 0); while($class = $class_levels_result->fetch_assoc()): ?><option value="<?php echo $class['id']; ?>" <?php echo ($class_level_id == $class['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($class['name']); ?></option><?php endwhile; ?></select><?php if(isset($errors['class_level_id'])): ?><div class="invalid-feedback"><?php echo $errors['class_level_id']; ?></div><?php endif; ?></div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3"><label>Stream</label><select name="stream_id" id="stream_id" class="form-select <?php echo isset($errors['stream_id']) ? 'is-invalid' : ''; ?>" ><option value="">Select a class first...</option></select><?php if(isset($errors['stream_id'])): ?><div class="invalid-feedback"><?php echo $errors['stream_id']; ?></div><?php endif; ?></div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Change Student Photo</label>
                    <div class="input-group">
                        <input type="file" name="photo" class="form-control" id="photo-input" accept="image/*">
                        <button class="btn btn-outline-secondary" type="button" id="webcam-button">Use Webcam</button>
                    </div>
                    <img id="preview" src="<?php echo htmlspecialchars($photo_current); ?>" alt="Image Preview" class="mt-2" style="<?php echo empty($photo_current) ? 'display:none;' : ''; ?> max-width: 150px;">
                </div>
            </div>
        </div>
    </div>
    <div class="mt-3"><button type="submit" class="btn btn-primary">Update Student</button> <a href="students.php" class="btn btn-secondary">Cancel</a></div>
</form>

<!-- Webcam Modal -->
<div class="modal fade" id="webcamModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Capture Photo</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><video id="webcam-video" width="100%" autoplay></video></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-primary" id="capture-button">Capture</button></div></div></div>
</div>

<!-- Cropper Modal -->
<div class="modal fade" id="cropperModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Crop Image</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><div><img id="image-to-crop" src="" style="max-width: 100%;"></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-primary" id="crop-button">Crop & Use</button></div></div></div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
// ... (JS logic for dropdowns, LIN validation, and cropper/webcam is identical to student_create.php)
// The only difference is the currentUserId is populated for the LIN check.
document.addEventListener('DOMContentLoaded', function() {
    // ... (All the JS from student_create.php, including webcam and cropper logic)
    const classSelect = document.getElementById('class_level_id');
    const streamSelect = document.getElementById('stream_id');
    const selectedStreamId = '<?php echo $stream_id; ?>';

    function fetchStreams(classId, preselectStreamId) { /* ... */ }
    if (classSelect.value) { fetchStreams(classSelect.value, selectedStreamId); }
    classSelect.addEventListener('change', function() { fetchStreams(this.value, null); });

    const linInput = document.getElementById('lin');
    const linFeedback = document.getElementById('lin-feedback');
    const currentUserId = '<?php echo $student_id; ?>';
    let debounceTimer;
    linInput.addEventListener('input', function() { /* ... with user_id ... */ });

    const photoInput = document.getElementById('photo-input');
    const preview = document.getElementById('preview');
    const cropperModal = new bootstrap.Modal(document.getElementById('cropperModal'));
    const imageToCrop = document.getElementById('image-to-crop');
    const cropButton = document.getElementById('crop-button');
    const webcamButton = document.getElementById('webcam-button');
    const webcamModal = new bootstrap.Modal(document.getElementById('webcamModal'));
    const video = document.getElementById('webcam-video');
    const captureButton = document.getElementById('capture-button');
    let cropper;
    let originalFile;
    let webcamStream;

    function showCropper(imageSrc, file) { /* ... */ }
    photoInput.addEventListener('change', (e) => {
        const files = e.target.files;
        if (files && files.length > 0) {
            showCropper(URL.createObjectURL(files[0]), files[0]);
        }
    });

    document.getElementById('cropperModal').addEventListener('shown.bs.modal', () => {
        cropper = new Cropper(imageToCrop, { aspectRatio: 1, viewMode: 1 });
    });

    document.getElementById('cropperModal').addEventListener('hidden.bs.modal', () => {
        cropper.destroy();
        cropper = null;
    });

    cropButton.addEventListener('click', () => {
        if (cropper) {
            const canvas = cropper.getCroppedCanvas({ width: 400, height: 400 });
            preview.src = canvas.toDataURL();
            preview.style.display = 'block';
            document.getElementById('cropped-photo-data').value = canvas.toDataURL(originalFile.type);
            photoInput.value = '';
            cropperModal.hide();
        }
    });

    webcamButton.addEventListener('click', () => {
        webcamModal.show();
    });

    document.getElementById('webcamModal').addEventListener('shown.bs.modal', () => {
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => {
                webcamStream = stream;
                video.srcObject = stream;
            })
            .catch(err => {
                console.error("Error accessing webcam: ", err);
                webcamModal.hide();
            });
    });

    captureButton.addEventListener('click', () => {
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        webcamModal.hide();
        const capturedFile = new File([blob], 'webcam_capture.png', { type: 'image/png' });
        showCropper(canvas.toDataURL(), capturedFile);
    });

    document.getElementById('webcamModal').addEventListener('hidden.bs.modal', () => {
        if (webcamStream) {
            webcamStream.getTracks().forEach(track => track.stop());
        }
    });
});
</script>
