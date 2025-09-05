<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../src/includes/url_helper.php';

// Authorization check
$allowed_roles = ['teacher', 'headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: " . login_url());
    exit;
}

$name = $code = "";
$name_err = $code_err = "";
$db_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter a subject name.";
    } else {
        $sql = "SELECT id FROM subjects WHERE name = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_name);
            $param_name = trim($_POST["name"]);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $name_err = "This subject already exists.";
                } else {
                    $name = trim($_POST["name"]);
                }
            } else {
                $db_err = "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }

    // Validate code
    if (empty(trim($_POST["code"]))) {
        $code_err = "Please enter a subject code.";
    } else {
        $sql = "SELECT id FROM subjects WHERE code = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_code);
            $param_code = trim($_POST["code"]);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $code_err = "This subject code already exists.";
                } else {
                    $code = trim($_POST["code"]);
                }
            } else {
                $db_err = "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }

    if (empty($name_err) && empty($code_err) && empty($db_err)) {
        $sql = "INSERT INTO subjects (name, code, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ss", $name, $code);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Subject created successfully.";
                header("location: " . subjects_url());
                exit();
            } else {
                $db_err = "Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
}

require_once __DIR__ . '/../../src/includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Create Subject</h2>
        <a href="<?php echo subjects_url(); ?>" class="btn btn-secondary">Back to Subjects</a>
    </div>

    <?php if(!empty($db_err)): ?>
        <div class="alert alert-danger"><?php echo $db_err; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <p>Please fill this form to create a new subject.</p>
            <form action="<?php echo subject_create_url(); ?>" method="post">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" name="name" id="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>">
                    <div class="invalid-feedback"><?php echo $name_err; ?></div>
                </div>
                <div class="mb-3">
                    <label for="code" class="form-label">Code</label>
                    <input type="text" name="code" id="code" class="form-control <?php echo (!empty($code_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $code; ?>">
                    <div class="invalid-feedback"><?php echo $code_err; ?></div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Create Subject</button>
                    <a href="<?php echo subjects_url(); ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once __DIR__ . '/../../src/includes/footer.php';
?>
