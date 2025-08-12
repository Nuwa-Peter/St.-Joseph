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
$id = 0;

if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id = trim($_GET["id"]);

    $sql = "SELECT name FROM class_levels WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                $name = $row["name"];
            } else {
                echo "No record found with that ID.";
                exit();
            }
        } else {
            echo "Oops! Something went wrong. Please try again later.";
            exit();
        }
        $stmt->close();
    }
} else {
    echo "ID parameter is missing.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter a class level name.";
    } else {
        $sql = "SELECT id FROM class_levels WHERE name = ? AND id != ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("si", $param_name, $id);
            $param_name = trim($_POST["name"]);

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    $name_err = "This class level already exists.";
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
        $sql = "UPDATE class_levels SET name = ?, updated_at = NOW() WHERE id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("si", $name, $id);

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

?>

<h2>Edit Class Level</h2>
<p>Please edit the input values and submit to update the class level.</p>
<form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
    <div class="form-group <?php echo (!empty($name_err)) ? 'has-error' : ''; ?>">
        <label>Name</label>
        <input type="text" name="name" class="form-control" value="<?php echo $name; ?>">
        <span class="help-block"><?php echo $name_err; ?></span>
    </div>
    <input type="hidden" name="id" value="<?php echo $id; ?>"/>
    <div class="form-group">
        <input type="submit" class="btn btn-primary" value="Submit">
        <a href="class_levels.php" class="btn btn-default">Cancel</a>
    </div>
</form>

<?php
require_once 'includes/footer.php';
?>
