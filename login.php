<?php
session_start();
require_once 'config.php';
require_once 'includes/header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT id, name, email, password FROM users WHERE email = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);

        if ($stmt->execute()) {
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($id, $name, $email, $hashed_password);
                if ($stmt->fetch()) {
                    if (password_verify($password, $hashed_password)) {
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["name"] = $name;

                        header("location: dashboard.php");
                    } else {
                        echo "<div class='alert alert-danger'>The password you entered was not valid.</div>";
                    }
                }
            } else {
                echo "<div class='alert alert-danger'>No account found with that email.</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Oops! Something went wrong. Please try again later.</div>";
        }

        $stmt->close();
    }
}
?>

<div class="container mt-5">
    <h2>Login</h2>
    <form action="login.php" method="post">
        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
    <p>Don't have an account? <a href="register.php">Register here</a>.</p>
</div>

<?php
$conn->close();
?>
