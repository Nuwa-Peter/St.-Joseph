<?php
session_start();
require_once 'config.php';

// Redirect if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$errors = [];
$first_name = $last_name = $username = $email = $role = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = trim($_POST['role']);

    // Validate form fields
    if (empty($first_name)) $errors['first_name'] = "First name is required.";
    if (empty($last_name)) $errors['last_name'] = "Last name is required.";
    if (empty($password)) $errors['password'] = "Password is required.";
    if (empty($role)) $errors['role'] = "Role is required.";

    // Validate username
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

    // Validate email
    if (empty($email)) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    } else {
        $sql = "SELECT id FROM users WHERE email = ?";
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows > 0) $errors['email'] = "This email is already taken.";
            $stmt->close();
        }
    }

    // Check input errors before inserting in database
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (first_name, last_name, username, email, password, role, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssss", $first_name, $last_name, $username, $email, $hashed_password, $role);
            if ($stmt->execute()) {
                // Redirect to user list page on success
                header("location: users.php");
                exit();
            } else {
                $errors['db'] = "Database error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

require_once 'includes/header.php';
?>

<h2>Create User</h2>
<form action="user_create.php" method="post">
    <?php if(isset($errors['db'])): ?><div class="alert alert-danger"><?php echo $errors['db']; ?></div><?php endif; ?>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="first_name" class="form-label">First Name</label>
            <input type="text" name="first_name" class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($first_name); ?>">
            <?php if(isset($errors['first_name'])): ?><div class="invalid-feedback"><?php echo $errors['first_name']; ?></div><?php endif; ?>
        </div>
        <div class="col-md-6 mb-3">
            <label for="last_name" class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($last_name); ?>">
            <?php if(isset($errors['last_name'])): ?><div class="invalid-feedback"><?php echo $errors['last_name']; ?></div><?php endif; ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($username); ?>">
            <?php if(isset($errors['username'])): ?><div class="invalid-feedback"><?php echo $errors['username']; ?></div><?php endif; ?>
        </div>
        <div class="col-md-6 mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>">
            <?php if(isset($errors['email'])): ?><div class="invalid-feedback"><?php echo $errors['email']; ?></div><?php endif; ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>">
            <?php if(isset($errors['password'])): ?><div class="invalid-feedback"><?php echo $errors['password']; ?></div><?php endif; ?>
        </div>
        <div class="col-md-6 mb-3">
            <label for="role" class="form-label">Role</label>
            <select name="role" class="form-select <?php echo isset($errors['role']) ? 'is-invalid' : ''; ?>">
                <option value="">Select a role...</option>
                <option value="student" <?php echo ($role == 'student') ? 'selected' : ''; ?>>Student</option>
                <option value="teacher" <?php echo ($role == 'teacher') ? 'selected' : ''; ?>>Teacher</option>
                <option value="parent" <?php echo ($role == 'parent') ? 'selected' : ''; ?>>Parent</option>
                <option value="bursar" <?php echo ($role == 'bursar') ? 'selected' : ''; ?>>Bursar</option>
                <option value="librarian" <?php echo ($role == 'librarian') ? 'selected' : ''; ?>>Librarian</option>
                <option value="headteacher" <?php echo ($role == 'headteacher') ? 'selected' : ''; ?>>Head Teacher</option>
                <option value="root" <?php echo ($role == 'root') ? 'selected' : ''; ?>>Root</option>
            </select>
            <?php if(isset($errors['role'])): ?><div class="invalid-feedback"><?php echo $errors['role']; ?></div><?php endif; ?>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Create User</button>
    <a href="users.php" class="btn btn-secondary">Cancel</a>
</form>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
