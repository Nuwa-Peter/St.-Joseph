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

// Check for ID in GET request first
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id = trim($_GET["id"]);
}
// If it's a POST request, the ID will be in the POST data
elseif (isset($_POST["id"]) && !empty(trim($_POST["id"]))) {
    $id = trim($_POST["id"]);
}
// If no ID, redirect
else {
    $_SESSION['error_message'] = "No Class Level ID specified.";
    header("location: " . classes_url());
    exit();
}

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter a class level name.";
    } else {
        // Check if the new name already exists for a different ID
        $sql = "SELECT id FROM class_levels WHERE name = ? AND id != ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("si", $param_name, $id);
            $param_name = trim($_POST["name"]);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $name_err = "This class level name is already taken.";
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
        $sql = "UPDATE class_levels SET name = ?, updated_at = NOW() WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("si", $name, $id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Class level updated successfully.";
                header("location: " . classes_url());
                exit();
            } else {
                $db_err = "Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
} else {
    // If not a POST request, fetch the current data for the form
    $sql = "SELECT name FROM class_levels WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                $name = $row["name"];
            } else {
                $_SESSION['error_message'] = "No record found with that ID.";
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

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Class Level</h2>
        <a href="<?php echo classes_url(); ?>" class="btn btn-secondary">Back to Class Levels</a>
    </div>

    <?php if(!empty($db_err)): ?>
        <div class="alert alert-danger"><?php echo $db_err; ?></div>
    <?php endif; ?>
     <?php if(!empty($name_err)): ?>
        <div class="alert alert-danger"><?php echo $name_err; ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <p>Please edit the input values and submit to update the class.</p>
            <form action="<?php echo class_edit_url($id); ?>" method="post">
                <div class="mb-3">
                    <label for="name" class="form-label">Class Name</label>
                    <input type="text" name="name" id="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($name); ?>">
                    <div class="invalid-feedback"><?php echo $name_err; ?></div>
                </div>
                <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Update Class Level</button>
                    <a href="<?php echo classes_url(); ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
$conn->close();
?>
