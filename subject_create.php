<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'config.php';
require_once 'includes/header.php';

$name = $code = "";
$name_err = $code_err = "";

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
                echo "Oops! Something went wrong. Please try again later.";
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
                echo "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }

    if (empty($name_err) && empty($code_err)) {
        $sql = "INSERT INTO subjects (name, code, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ss", $name, $code);

            if ($stmt->execute()) {
                header("location: subjects.php");
                exit();
            } else {
                echo "Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }

    $conn->close();
}
?>

<h2>Create Subject</h2>
<p>Please fill this form to create a new subject.</p>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div class="form-group <?php echo (!empty($name_err)) ? 'has-error' : ''; ?>">
        <label>Name</label>
        <input type="text" name="name" class="form-control" value="<?php echo $name; ?>">
        <span class="help-block"><?php echo $name_err; ?></span>
    </div>
    <div class="form-group <?php echo (!empty($code_err)) ? 'has-error' : ''; ?>">
        <label>Code</label>
        <input type="text" name="code" class="form-control" value="<?php echo $code; ?>">
        <span class="help-block"><?php echo $code_err; ?></span>
    </div>
    <div class="form-group">
        <input type="submit" class="btn btn-primary" value="Submit">
        <a href="subjects.php" class="btn btn-default">Cancel</a>
    </div>
</form>

<?php
require_once 'includes/footer.php';
?>
