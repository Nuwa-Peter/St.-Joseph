<?php
// All dependencies are loaded by index.php
require_once __DIR__ . '/includes/header.php';

// Authentication and Authorization
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: " . login_url());
    exit;
}
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Marks Entry</h2>
    </div>
    <div class="alert alert-info">
        This feature is under construction. Please check back later.
    </div>
    <a href="<?php echo dashboard_url(); ?>" class="btn btn-primary">Back to Dashboard</a>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
