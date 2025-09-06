<?php
require_once 'config.php'; // Must be first

// Authorization check
$allowed_roles = ['admin', 'headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: " . login_url());
    exit;
}

$name = "";
$name_err = "";
$db_err = "";
$id = 0;
$class_level_id = 0;

// Determine the stream ID from GET or POST
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id = trim($_GET["id"]);
} elseif (isset($_POST["id"]) && !empty(trim($_POST["id"]))) {
    $id = trim($_POST["id"]);
} else {
    $_SESSION['error_message'] = "No Stream ID specified.";
    header("location: " . classes_url()); // Redirect to main classes view if no ID
    exit();
}

// Get class_level_id, which is needed for redirects and logic
if (isset($_REQUEST["class_level_id"]) && !empty(trim($_REQUEST["class_level_id"]))) {
    $class_level_id = trim($_REQUEST["class_level_id"]);
}

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter a stream name.";
    } else {
        $sql = "SELECT id FROM streams WHERE name = ? AND class_level_id = ? AND id != ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sii", $param_name, $class_level_id, $id);
            $param_name = trim($_POST["name"]);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $name_err = "This stream already exists for this class level.";
                } else {
                    $name = trim($_POST["name"]);
                }
            } else {
                $db_err = "Oops! Something went wrong.";
            }
            $stmt->close();
        }
    }

    if (empty($name_err) && empty($db_err)) {
        $sql = "UPDATE streams SET name = ?, updated_at = NOW() WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("si", $name, $id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Stream updated successfully.";
                header("location: " . streams_url(['class_level_id' => $class_level_id]));
                exit();
            } else {
                $db_err = "Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
} else {
    // If not a POST request, fetch the current data for the form
    $sql = "SELECT name, class_level_id FROM streams WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                $name = $row["name"];
                // Ensure class_level_id is set from the database record
                if (empty($class_level_id)) {
                    $class_level_id = $row["class_level_id"];
                }
            } else {
                $_SESSION['error_message'] = "No stream found with that ID.";
                header("location: " . classes_url());
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Oops! Something went wrong fetching data.";
            header("location: " . classes_url());
            exit();
        }
        $stmt->close();
    }
}

// Redirect back to main classes page if class_level_id is still not set
if (empty($class_level_id)) {
    $_SESSION['error_message'] = "Could not determine the class level for this stream.";
    header("location: " . classes_url());
    exit();
}

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Stream</h2>
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
            <form action="<?php echo stream_edit_url($id); ?>" method="post">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" name="name" id="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($name); ?>">
                    <div class="invalid-feedback"><?php echo $name_err; ?></div>
                </div>
                <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                <input type="hidden" name="class_level_id" value="<?php echo $class_level_id; ?>"/>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Update Stream</button>
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
