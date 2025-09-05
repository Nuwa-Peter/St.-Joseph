<?php

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../src/includes/header.php';

$name = $code = "";
$name_err = $code_err = "";
$id = 0;

if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id = trim($_GET["id"]);

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
    $id = $_POST["id"];

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
                echo "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }

    if (empty($name_err) && empty($code_err)) {
        $sql = "UPDATE subjects SET name = ?, code = ?, updated_at = NOW() WHERE id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssi", $name, $code, $id);

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

<h2>Edit Subject</h2>
<p>Please edit the input values and submit to update the subject.</p>
<form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
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
    <input type="hidden" name="id" value="<?php echo $id; ?>"/>
    <div class="form-group">
        <input type="submit" class="btn btn-primary" value="Submit">
        <a href="subjects.php" class="btn btn-default">Cancel</a>
    </div>
</form>

<?php
require_once __DIR__ . '/../../src/includes/footer.php';
?>
