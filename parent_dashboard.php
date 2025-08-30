<?php
session_start();
require_once 'config.php';
require_once 'includes/header.php';

// Authorization Check
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] !== 'parent') {
    header("location: login.php");
    exit;
}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Parent Dashboard</h1>
        <span class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION["name"]); ?>!</span>
    </div>

    <?php
    // --- WIDGET AREA ---
    // Include parent-specific widgets
    include 'widgets/parent_children.php';

    // You could also include general widgets like announcements here
    // include 'widgets/announcements_widget.php';
    ?>

</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
