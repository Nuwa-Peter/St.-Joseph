<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Add role check for admin access
// if ($_SESSION['role'] !== 'root' && $_SESSION['role'] !== 'headteacher') {
//     header("location: dashboard.php");
//     exit;
// }

require_once 'config.php';
require_once 'includes/header.php';

$first_name = $last_name = $email = $role = $status = "";
$user_id = $_GET['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $status = $_POST['status'];
    $user_id = $_POST['id'];

    $sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, role = ?, status = ? WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssssi", $first_name, $last_name, $email, $role, $status, $user_id);

        if ($stmt->execute()) {
            header("location: users.php");
        } else {
            echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
        }

        $stmt->close();
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
} else {
    $sql = "SELECT first_name, last_name, email, role, status FROM users WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $stmt->bind_result($first_name, $last_name, $email, $role, $status);
            $stmt->fetch();
        }
        $stmt->close();
    }
}
?>

<h2>Edit User</h2>
<form action="user_edit.php" method="post">
    <input type="hidden" name="id" value="<?php echo $user_id; ?>">
    <div class="mb-3">
        <label for="first_name" class="form-label">First Name</label>
        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required>
    </div>
    <div class="mb-3">
        <label for="last_name" class="form-label">Last Name</label>
        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
    </div>
    <div class="mb-3">
        <label for="role" class="form-label">Role</label>
        <select class="form-select" id="role" name="role" required>
            <option value="student" <?php if($role == 'student') echo 'selected'; ?>>Student</option>
            <option value="teacher" <?php if($role == 'teacher') echo 'selected'; ?>>Teacher</option>
            <option value="parent" <?php if($role == 'parent') echo 'selected'; ?>>Parent</option>
            <option value="bursar" <?php if($role == 'bursar') echo 'selected'; ?>>Bursar</option>
            <option value="librarian" <?php if($role == 'librarian') echo 'selected'; ?>>Librarian</option>
            <option value="headteacher" <?php if($role == 'headteacher') echo 'selected'; ?>>Head Teacher</option>
            <option value="root" <?php if($role == 'root') echo 'selected'; ?>>Root</option>
        </select>
    </div>
    <div class="mb-3">
        <label for="status" class="form-label">Status</label>
        <select class="form-select" id="status" name="status" required>
            <option value="active" <?php if($status == 'active') echo 'selected'; ?>>Active</option>
            <option value="inactive" <?php if($status == 'inactive') echo 'selected'; ?>>Inactive</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Update</button>
    <a href="users.php" class="btn btn-secondary">Cancel</a>
</form>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
