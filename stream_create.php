<?php
require_once 'config.php';

// Authorization check
$allowed_roles = ['admin', 'headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: " . login_url());
    exit;
}

$name = "";
$name_err = "";
$db_err = "";
$class_level_id = 0;

// Get class_level_id from GET or POST for context
if (isset($_REQUEST["class_level_id"]) && !empty(trim($_REQUEST["class_level_id"]))) {
    $class_level_id = trim($_REQUEST["class_level_id"]);
} else {
    $_SESSION['error_message'] = "A class level must be specified to create a stream.";
    header("location: " . classes_url());
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter a stream name.";
    } else {
        $sql = "SELECT id FROM streams WHERE name = ? AND class_level_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("si", $param_name, $class_level_id);
            $param_name = trim($_POST["name"]);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $name_err = "This stream already exists for this class level.";
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
        $sql = "INSERT INTO streams (name, class_level_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("si", $name, $class_level_id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Stream created successfully.";
                header("location: " . streams_url(['class_level_id' => $class_level_id]));
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
        <h2>Create Stream</h2>
        <a href="<?php echo streams_url(['class_level_id' => $class_level_id]); ?>" class="btn btn-secondary">Back to Streams</a>
    </div>

    <?php if(!empty($name_err) || !empty($db_err)): ?>
        <div class="alert alert-danger">
            <?php echo $name_err; ?>
            <?php echo $db_err; ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <p>Please fill this form to create a new stream for the selected class.</p>
            <form action="<?php echo stream_create_url(); ?>" method="post">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" name="name" id="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($name); ?>">
                    <div class="invalid-feedback"><?php echo $name_err; ?></div>
                </div>
                <input type="hidden" name="class_level_id" value="<?php echo $class_level_id; ?>"/>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Create Stream</button>
                    <a href="<?php echo streams_url(['class_level_id' => $class_level_id]); ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
$conn->close();
?>
