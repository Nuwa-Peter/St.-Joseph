<?php
require_once 'config.php';
require_once 'includes/url_helper.php';

// Authorization check
$allowed_roles = ['headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: " . login_url());
    exit;
}

$name = "";
$name_err = "";
$db_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter a class name.";
    } else {
        $sql = "SELECT id FROM class_levels WHERE name = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_name);
            $param_name = trim($_POST["name"]);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows >= 1) {
                    $name_err = "This class already exists.";
                } else {
                    $name = trim($_POST["name"]);
                }
            } else {
                $db_err = "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }

    if (empty($name_err) && empty($db_err)) {
        $sql = "INSERT INTO class_levels (name, created_at, updated_at) VALUES (?, NOW(), NOW())";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $name);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Class level created successfully.";
                header("location: " . class_levels_url());
                exit();
            } else {
                $db_err = "Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Create Class Level</h2>
        <a href="<?php echo class_levels_url(); ?>" class="btn btn-secondary">Back to Class Levels</a>
    </div>

    <?php if(!empty($db_err)): ?>
        <div class="alert alert-danger"><?php echo $db_err; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <p>Please fill this form to create a new class level.</p>
            <form action="<?php echo class_level_create_url(); ?>" method="post">
                <div class="mb-3">
                    <label for="name" class="form-label">Class Name</label>
                    <input type="text" name="name" id="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>" required>
                    <div class="invalid-feedback"><?php echo $name_err; ?></div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Create Class Level</button>
                    <a href="<?php echo class_levels_url(); ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
