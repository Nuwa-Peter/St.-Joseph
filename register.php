<?php
require_once 'config.php';
require_once 'includes/url_helper.php';
require_once 'includes/csrf_helper.php';

$registration_success = false;
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verify_csrf_token();

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Basic validation
    if (empty($name) || empty($email) || empty($password)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sss", $name, $email, $hashed_password);

            if ($stmt->execute()) {
                $registration_success = true;
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_message = "Error: " . $conn->error;
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container mt-5">
    <h2>Register</h2>

    <?php if ($registration_success): ?>
        <div class='alert alert-success'>Registration successful. You can now <a href='<?php echo login_url(); ?>'>login</a>.</div>
    <?php elseif (!empty($error_message)): ?>
        <div class='alert alert-danger'><?php echo $error_message; ?></div>
    <?php endif; ?>

    <?php if (!$registration_success): ?>
    <form action="<?php echo url('register'); ?>" method="post">
        <?php echo csrf_input(); ?>
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Register</button>
    </form>
    <?php endif; ?>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
