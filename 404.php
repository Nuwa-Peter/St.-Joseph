<?php
// Prevent direct access to this file
if (!defined('APP_RAN')) {
    die("Direct access is not permitted.");
}

// All initial setup (session, config, helpers) is done in index.php.
// When this file is included by the router, $conn is available globally.
require_once __DIR__ . '/includes/header.php';
?>

<div class="container text-center mt-5">
    <div class="alert alert-danger" role="alert">
        <h1 class="display-1">404</h1>
        <h2>Page Not Found</h2>
    </div>
    <p class="lead">Sorry, the page you are looking for does not exist or has been moved.</p>
    <p>You can return to the dashboard or login page by clicking the button below.</p>
    <a href="<?php echo (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) ? dashboard_url() : login_url(); ?>" class="btn btn-primary mt-3">
        <?php echo (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) ? 'Go to Dashboard' : 'Go to Login'; ?>
    </a>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
