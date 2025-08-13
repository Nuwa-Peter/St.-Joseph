<?php
session_start();
require_once 'config.php';
require_once 'includes/header.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$errors = [];
$student_id = 0;
$first_name = $last_name = $username = $lin = $email = $phone_number = $date_of_birth = $gender = $student_type = $class_level_id = $stream_id = "";

if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $student_id = trim($_GET["id"]);
    $sql = "
        SELECT u.*, s.class_level_id, su.stream_id
        FROM users u
        LEFT JOIN stream_user su ON u.id = su.user_id
        LEFT JOIN streams s ON su.stream_id = s.id
        WHERE u.id = ? AND u.role = 'student'";
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
            } else { exit("Student not found."); }
        }
        $stmt->close();
    }
} else { exit("No student ID specified."); }

$class_levels_sql = "SELECT id, name FROM class_levels ORDER BY name ASC";
$class_levels_result = $conn->query($class_levels_sql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ... (validation and update logic is largely the same as create, so we'll reuse it here)
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
            $photo_path = $student['photo'];
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
                $target_dir = "uploads/photos/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
                $photo_path = $target_dir . uniqid() . '-' . basename($_FILES["photo"]["name"]);
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
<form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $student_id; ?>">
    <div class="card">
        <div class="card-header">Student Details</div>
        <div class="card-body">
            <?php if(isset($errors['db'])): ?><div class="alert alert-danger"><?php echo $errors['db']; ?></div><?php endif; ?>
             <div class="row">
                <div class="col-md-4 mb-3"><label>First Name</label><input type="text" name="first_name" class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($first_name); ?>"><?php if(isset($errors['first_name'])): ?><div class="invalid-feedback"><?php echo $errors['first_name']; ?></div><?php endif; ?></div>
                <div class="col-md-4 mb-3"><label>Surname</label><input type="text" name="last_name" class="form-control <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($last_name); ?>"><?php if(isset($errors['last_name'])): ?><div class="invalid-feedback"><?php echo $errors['last_name']; ?></div><?php endif; ?></div>
                <div class="col-md-4 mb-3"><label>Username</label><input type="text" name="username" class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($username); ?>"><?php if(isset($errors['username'])): ?><div class="invalid-feedback"><?php echo $errors['username']; ?></div><?php endif; ?></div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3"><label>Email (Optional)</label><input type="email" name="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>"><?php if(isset($errors['email'])): ?><div class="invalid-feedback"><?php echo $errors['email']; ?></div><?php endif; ?></div>
                <div class="col-md-6 mb-3">
                    <label for="lin" class="form-label">LIN (Learner ID Number)</label>
                    <input type="text" name="lin" id="lin" class="form-control" value="<?php echo htmlspecialchars($lin); ?>">
                    <div id="lin-feedback" class="invalid-feedback"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3"><label>Parent/Guardian Phone</label><input type="text" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($phone_number); ?>"></div>
                <div class="col-md-6 mb-3"><label>Date of Birth</label><input type="date" name="date_of_birth" class="form-control" value="<?php echo htmlspecialchars($date_of_birth); ?>"></div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3"><label>Gender</label><select name="gender" class="form-select"><option value="Male" <?php echo ($gender == 'Male') ? 'selected' : ''; ?>>Male</option><option value="Female" <?php echo ($gender == 'Female') ? 'selected' : ''; ?>>Female</option></select></div>
                <div class="col-md-4 mb-3"><label>Student Type</label><select name="student_type" class="form-select"><option value="day" <?php echo ($student_type == 'day') ? 'selected' : ''; ?>>Day</option><option value="boarding" <?php echo ($student_type == 'boarding') ? 'selected' : ''; ?>>Boarding</option></select></div>
                <div class="col-md-4 mb-3"><label>Class</label><select name="class_level_id" id="class_level_id" class="form-select <?php echo isset($errors['class_level_id']) ? 'is-invalid' : ''; ?>"><option value="">Select a class...</option><?php while($class = $class_levels_result->fetch_assoc()): ?><option value="<?php echo $class['id']; ?>" <?php echo ($class_level_id == $class['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($class['name']); ?></option><?php endwhile; ?></select><?php if(isset($errors['class_level_id'])): ?><div class="invalid-feedback"><?php echo $errors['class_level_id']; ?></div><?php endif; ?></div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3"><label>Stream</label><select name="stream_id" id="stream_id" class="form-select <?php echo isset($errors['stream_id']) ? 'is-invalid' : ''; ?>" ><option value="">Select a class first...</option></select><?php if(isset($errors['stream_id'])): ?><div class="invalid-feedback"><?php echo $errors['stream_id']; ?></div><?php endif; ?></div>
                <div class="col-md-6 mb-3"><label>Change Student Photo</label><input type="file" name="photo" class="form-control"></div>
            </div>
        </div>
    </div>
    <div class="mt-3">
        <button type="submit" class="btn btn-primary">Update Student</button>
        <a href="students.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const classSelect = document.getElementById('class_level_id');
    const streamSelect = document.getElementById('stream_id');
    const selectedStreamId = '<?php echo $stream_id; ?>';

    function fetchStreams(classId, preselectStreamId) {
        streamSelect.innerHTML = '<option value="">Loading...</option>';
        streamSelect.disabled = true;

        if (classId) {
            fetch(`api_get_streams.php?class_level_id=${classId}`)
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
                })
                .catch(error => {
                    console.error('Error fetching streams:', error);
                    streamSelect.innerHTML = '<option value="">Error loading streams</option>';
                });
        } else {
            streamSelect.innerHTML = '<option value="">Select a class first...</option>';
        }
    }

    // Trigger on page load if a class is already selected
    if (classSelect.value) {
        fetchStreams(classSelect.value, selectedStreamId);
    }

    // Trigger on change
    classSelect.addEventListener('change', function() {
        fetchStreams(this.value, null); // Don't preselect when user changes class
    });

    // Live LIN validation
    const linInput = document.getElementById('lin');
    const linFeedback = document.getElementById('lin-feedback');
    const currentUserId = '<?php echo $student_id; ?>';
    let debounceTimer;

    linInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const lin = this.value;

        if (lin.length < 3) {
            linInput.classList.remove('is-invalid');
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`api_check_lin.php?lin=${encodeURIComponent(lin)}&user_id=${currentUserId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.unique) {
                        linInput.classList.remove('is-invalid');
                        linInput.classList.add('is-valid');
                        linFeedback.textContent = '';
                    } else {
                        linInput.classList.remove('is-valid');
                        linInput.classList.add('is-invalid');
                        linFeedback.textContent = 'This LIN is already in use.';
                    }
                })
                .catch(error => console.error('Error checking LIN:', error));
        }, 500);
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
