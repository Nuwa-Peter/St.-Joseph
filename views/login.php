<?php require_once __DIR__ . '/layouts/guest_header.php'; ?>

<div class="login-container text-center">
    <img src="images/logo.png" alt="Logo" class="logo mb-4">
    <h2>Login</h2>
    <p>Please fill in your credentials to login.</p>
    <?php
    if(!empty($login_err) || !empty($password_err)){
        echo '<div class="alert alert-danger">' . ($login_err ?: $password_err) . '</div>';
    }
    ?>
    <form action="login.php" method="post">
        <div class="form-floating mb-3">
            <input type="text" name="login_identifier" class="form-control <?php echo (!empty($login_err)) ? 'is-invalid' : ''; ?>" id="login_identifier" placeholder="Username or Email" value="<?php echo htmlspecialchars($login_identifier); ?>" required>
            <label for="login_identifier">Username or Email</label>
        </div>
        <div class="form-floating mb-3">
            <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" id="password" placeholder="Password" required>
            <label for="password">Password</label>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg">Login</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/layouts/guest_footer.php'; ?>
