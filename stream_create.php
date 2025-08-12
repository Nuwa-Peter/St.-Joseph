<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'config.php';
require_once 'includes/header.php';

$name = "";
$name_err = "";
$class_level_id = 0;

if (isset($_GET["class_level_id"]) && !empty(trim($_GET["class_level_id"]))) {
    $class_level_id = trim($_GET["class_level_id"]);
} else {
    echo "Class Level ID is missing.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $class_level_id = $_POST["class_level_id"];
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
                echo "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }

    if (empty($name_err)) {
        $sql = "INSERT INTO streams (name, class_level_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("si", $name, $class_level_id);

            if ($stmt->execute()) {
                header("location: streams.php?class_level_id=" . $class_level_id);
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

<h2>Create Stream</h2>
<p>Please fill this form to create a new stream.</p>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?class_level_id=<?php echo $class_level_id; ?>" method="post">
    <div class="form-group <?php echo (!empty($name_err)) ? 'has-error' : ''; ?>">
        <label>Name</label>
        <input type="text" name="name" class="form-control" value="<?php echo $name; ?>">
        <span class="help-block"><?php echo $name_err; ?></span>
    </div>
    <input type="hidden" name="class_level_id" value="<?php echo $class_level_id; ?>"/>
    <div class="form-group">
        <input type="submit" class="btn btn-primary" value="Submit">
        <a href="streams.php?class_level_id=<?php echo $class_level_id; ?>" class="btn btn-default">Cancel</a>
    </div>
</form>

<?php
require_once 'includes/footer.php';
?>
