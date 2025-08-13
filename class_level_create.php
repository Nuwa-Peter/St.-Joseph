<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$name = "";
$name_err = "";

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
                if ($stmt->num_rows == 1) {
                    $name_err = "This class already exists.";
                } else {
                    $name = trim($_POST["name"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }

    if (empty($name_err)) {
        $sql = "INSERT INTO class_levels (name, created_at, updated_at) VALUES (?, NOW(), NOW())";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $name);
            if ($stmt->execute()) {
                header("location: class_levels.php");
                exit();
            } else {
                echo "Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
    $conn->close();
}

require_once 'includes/header.php';
?>

<h2>Create Class</h2>
<p>Please fill this form to create a new class.</p>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div class="form-group <?php echo (!empty($name_err)) ? 'has-error' : ''; ?>">
        <label>Class Name</label>
        <input type="text" name="name" class="form-control" value="<?php echo $name; ?>">
        <span class="help-block"><?php echo $name_err; ?></span>
    </div>
    <div class="form-group">
        <input type="submit" class="btn btn-primary" value="Submit">
        <a href="class_levels.php" class="btn btn-default">Cancel</a>
    </div>
</form>

<?php
require_once 'includes/footer.php';
?>
