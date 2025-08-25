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
        <div class="input-group mb-3">
            <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
            <div class="form-floating">
                <input type="text" name="login_identifier" class="form-control <?php echo (!empty($login_err)) ? 'is-invalid' : ''; ?>" id="login_identifier" placeholder="Username or Email" value="<?php echo htmlspecialchars($login_identifier); ?>" required>
                <label for="login_identifier">Username or Email</label>
            </div>
        </div>
        <div class="input-group mb-3">
            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
            <div class="form-floating">
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" id="password" placeholder="Password" required>
                <label for="password">Password</label>
            </div>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg">Login</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/layouts/guest_footer.php'; ?>
