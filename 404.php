<?php
// Set a specific response code
http_response_code(404);

// The main entry `index.php` handles all the initial setup like session, config, and helpers.
// However, if a user somehow lands here directly, we might need some basic setup.
// For a routed 404, header and footer should be included.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';
require_once 'includes/header.php';
?>

<div class="container text-center mt-5">
    <div class="alert alert-danger" role="alert">
        <h1 class="display-1">404</h1>
        <h2>Page Not Found</h2>
    </div>
    <p class="lead">Sorry, the page you are looking for does not exist or has been moved.</p>
    <p>You can return to the dashboard by clicking the button below.</p>
    <a href="<?php echo (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) ? dashboard_url() : login_url(); ?>" class="btn btn-primary mt-3">
        <?php echo (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) ? 'Go to Dashboard' : 'Go to Login'; ?>
    </a>
</div>

<?php
require_once 'includes/footer.php';
?>
