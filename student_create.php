<?php
session_start();
require_once 'config.php';
require_once 'includes/header.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$errors = [];
$first_name = $last_name = $username = $lin = $email = $phone_number = $date_of_birth = $gender = $student_type = $stream_id = "";

$streams_sql = "SELECT s.id, s.name, cl.name as class_name FROM streams s JOIN class_levels cl ON s.class_level_id = cl.id ORDER BY cl.name, s.name";
$streams_result = $conn->query($streams_sql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $lin = trim($_POST['lin']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $date_of_birth = trim($_POST['date_of_birth']);
    $gender = trim($_POST['gender']);
    $student_type = trim($_POST['student_type']);
    $stream_id = trim($_POST['stream_id']);

    if (empty($first_name)) $errors['first_name'] = "First name is required.";
    if (empty($last_name)) $errors['last_name'] = "Last name is required.";
    if (empty($username)) {
        $errors['username'] = "Username is required.";
    } else {
        $sql = "SELECT id FROM users WHERE username = ?";
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows > 0) $errors['username'] = "This username is already taken.";
            $stmt->close();
        }
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    } elseif (!empty($email)) {
        $sql = "SELECT id FROM users WHERE email = ?";
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows > 0) $errors['email'] = "This email is already taken.";
            $stmt->close();
        }
    }

    if (empty($stream_id)) $errors['stream_id'] = "Class/Stream is required.";

    if (empty($errors)) {
        $conn->begin_transaction();
        try {
            $default_password = password_hash('password123', PASSWORD_DEFAULT);
            $role = 'student';

            $photo_path = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
                $target_dir = "uploads/photos/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
                $photo_path = $target_dir . uniqid() . '-' . basename($_FILES["photo"]["name"]);
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
            header("location: students.php");
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $errors['db'] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<h2>Add New Student</h2>
<p>Fill out the form to register a new student.</p>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
    <div class="card">
        <div class="card-header">Student Details</div>
        <div class="card-body">
            <?php if(isset($errors['db'])): ?><div class="alert alert-danger"><?php echo $errors['db']; ?></div><?php endif; ?>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($first_name); ?>">
                    <?php if(isset($errors['first_name'])): ?><div class="invalid-feedback"><?php echo $errors['first_name']; ?></div><?php endif; ?>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="last_name" class="form-label">Surname</label>
                    <input type="text" name="last_name" class="form-control <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($last_name); ?>">
                    <?php if(isset($errors['last_name'])): ?><div class="invalid-feedback"><?php echo $errors['last_name']; ?></div><?php endif; ?>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($username); ?>">
                    <?php if(isset($errors['username'])): ?><div class="invalid-feedback"><?php echo $errors['username']; ?></div><?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email (Optional)</label>
                    <input type="email" name="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>">
                     <?php if(isset($errors['email'])): ?><div class="invalid-feedback"><?php echo $errors['email']; ?></div><?php endif; ?>
                </div>
                 <div class="col-md-6 mb-3">
                    <label for="lin" class="form-label">LIN (Learner ID Number)</label>
                    <input type="text" name="lin" class="form-control" value="<?php echo htmlspecialchars($lin); ?>">
                </div>
            </div>
            <div class="row">
                 <div class="col-md-6 mb-3">
                    <label for="phone_number" class="form-label">Parent/Guardian Phone</label>
                    <input type="text" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($phone_number); ?>">
                </div>
                 <div class="col-md-6 mb-3">
                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control" value="<?php echo htmlspecialchars($date_of_birth); ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="gender" class="form-label">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="Male" <?php echo ($gender == 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($gender == 'Female') ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="student_type" class="form-label">Student Type</label>
                    <select name="student_type" class="form-select">
                        <option value="day" <?php echo ($student_type == 'day') ? 'selected' : ''; ?>>Day</option>
                        <option value="boarding" <?php echo ($student_type == 'boarding') ? 'selected' : ''; ?>>Boarding</option>
                    </select>
                </div>
                 <div class="col-md-4 mb-3">
                    <label for="stream_id" class="form-label">Class / Stream</label>
                    <select name="stream_id" class="form-select <?php echo isset($errors['stream_id']) ? 'is-invalid' : ''; ?>">
                        <option value="">Select a class...</option>
                        <?php while($stream = $streams_result->fetch_assoc()): ?>
                            <option value="<?php echo $stream['id']; ?>" <?php echo ($stream_id == $stream['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($stream['class_name'] . ' ' . $stream['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <?php if(isset($errors['stream_id'])): ?><div class="invalid-feedback"><?php echo $errors['stream_id']; ?></div><?php endif; ?>
                </div>
            </div>
             <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="photo" class="form-label">Student Photo</label>
                    <input type="file" name="photo" class="form-control" id="photo">
                </div>
            </div>
        </div>
    </div>
    <div class="mt-3">
        <button type="submit" class="btn btn-primary">Add Student</button>
        <a href="students.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php require_once 'includes/footer.php'; ?>
