<?php
session_start();
require_once 'config.php';

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: dashboard.php");
    exit;
}

$login_identifier = $password = "";
$login_err = $password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["login_identifier"]))) {
        $login_err = "Please enter username or email.";
    } else {
        $login_identifier = trim($_POST["login_identifier"]);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty($login_err) && empty($password_err)) {
        // Prepare a select statement
        $sql = "SELECT id, username, first_name, last_name, email, password, role FROM users WHERE username = ? OR email = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ss", $login_identifier, $login_identifier);

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id, $username, $first_name, $last_name, $email, $hashed_password, $role);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            $name = $first_name . ' ' . $last_name;
                            $initials = '';
                            $parts = explode(' ', $name);
                            foreach ($parts as $part) {
                                $initials .= strtoupper(substr($part, 0, 1));
                            }

                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["name"] = $name;
                            $_SESSION["initials"] = $initials;
                            $_SESSION["role"] = $role;

                            header("location: dashboard.php");
                        } else {
                            $login_err = "Invalid username/email or password.";
                        }
                    }
                } else {
                    $login_err = "Invalid username/email or password.";
                }
            } else {
                $login_err = "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - School Management System</title>
    <link rel="icon" type="image/png" href="images/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/custom.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 2rem;
            background-color: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="login-container text-center">
        <img src="images/logo.png" alt="Logo" class="logo">
        <h2>Login</h2>
        <p>Please fill in your credentials to login.</p>
        <?php
        if(!empty($login_err) || !empty($password_err)){
            echo '<div class="alert alert-danger">' . ($login_err ?: $password_err) . '</div>';
        }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-floating mb-3">
                <input type="text" name="login_identifier" class="form-control <?php echo (!empty($login_err)) ? 'is-invalid' : ''; ?>" id="login_identifier" placeholder="Username or Email" value="<?php echo htmlspecialchars($login_identifier); ?>">
                <label for="login_identifier">Username or Email</label>
            </div>
            <div class="form-floating mb-3">
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" id="password" placeholder="Password">
                <label for="password">Password</label>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
