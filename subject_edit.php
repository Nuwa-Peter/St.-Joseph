<?php
require_once 'config.php'; // Must be first

// Authorization check
$allowed_roles = ['admin', 'headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: " . login_url());
    exit;
}

$name = $code = "";
$name_err = $code_err = "";
$db_err = "";
$id = 0;

// Determine the subject ID from GET or POST
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id = trim($_GET["id"]);
} elseif (isset($_POST["id"]) && !empty(trim($_POST["id"]))) {
    $id = trim($_POST["id"]);
} else {
    $_SESSION['error_message'] = "No Subject ID specified.";
    header("location: " . subjects_url());
    exit();
}

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter a subject name.";
    } else {
        $sql = "SELECT id FROM subjects WHERE name = ? AND id != ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("si", $param_name, $id);
            $param_name = trim($_POST["name"]);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $name_err = "This subject name already exists.";
                } else {
                    $name = trim($_POST["name"]);
                }
            } else {
                $db_err = "Oops! Something went wrong.";
            }
            $stmt->close();
        }
    }

    // Validate code
    if (empty(trim($_POST["code"]))) {
        $code_err = "Please enter a subject code.";
    } else {
        $sql = "SELECT id FROM subjects WHERE code = ? AND id != ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("si", $param_code, $id);
            $param_code = trim($_POST["code"]);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $code_err = "This subject code already exists.";
                } else {
                    $code = trim($_POST["code"]);
                }
            } else {
                $db_err = "Oops! Something went wrong.";
            }
            $stmt->close();
        }
    }

    if (empty($name_err) && empty($code_err) && empty($db_err)) {
        $sql = "UPDATE subjects SET name = ?, code = ?, updated_at = NOW() WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssi", $name, $code, $id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Subject updated successfully.";
                header("location: " . subjects_url());
                exit();
            } else {
                $db_err = "Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
} else {
    // If not a POST request, fetch the current data for the form
    $sql = "SELECT name, code FROM subjects WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                $name = $row["name"];
                $code = $row["code"];
            } else {
                $_SESSION['error_message'] = "No subject found with that ID.";
                header("location: " . subjects_url());
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Oops! Something went wrong fetching data.";
            header("location: " . subjects_url());
            exit();
        }
        $stmt->close();
    }
}

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Subject</h2>
        <a href="<?php echo subjects_url(); ?>" class="btn btn-secondary">Back to Subjects</a>
    </div>

    <?php if(!empty($db_err)): ?>
        <div class="alert alert-danger"><?php echo $db_err; ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <p>Please edit the input values and submit to update the subject.</p>
            <form action="<?php echo subject_edit_url($id); ?>" method="post">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" name="name" id="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($name); ?>">
                    <div class="invalid-feedback"><?php echo $name_err; ?></div>
                </div>
                <div class="mb-3">
                    <label for="code" class="form-label">Code</label>
                    <input type="text" name="code" id="code" class="form-control <?php echo (!empty($code_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($code); ?>">
                    <div class="invalid-feedback"><?php echo $code_err; ?></div>
                </div>
                <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Update Subject</button>
                    <a href="<?php echo subjects_url(); ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
$conn->close();
?>
