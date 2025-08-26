<?php
session_start();
require_once 'config.php';
require_once 'includes/header.php';

// Redirect if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// A parent should not be on this dashboard
if ($_SESSION['role'] === 'parent') {
    header("location: parent_dashboard.php");
    exit;
}

$user_role = $_SESSION['role'];
$admin_roles = ['root', 'headteacher', 'director']; // Assuming director is an admin role

?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        <span class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION["name"]); ?>!</span>
    </div>

    <?php
    // --- WIDGET AREA ---
    // Display widgets based on user role

    if (in_array($user_role, $admin_roles)) {
        // --- Admin Dashboard ---
        echo '<h4 class="mb-3">School Overview</h4>';
        include 'widgets/admin_stats.php';

        // You can include other admin-specific widgets here
        // include 'widgets/recent_activity.php';

    } elseif ($user_role === 'teacher') {
        // --- Teacher Dashboard ---
        include 'widgets/teacher_classes.php';
        // You can include other teacher-specific widgets here

    } elseif ($user_role === 'student') {
        // --- Student Dashboard ---
        include 'widgets/student_grades_summary.php';
        // You can include other student-specific widgets here

    } else {
        // --- Default Dashboard for other roles ---
        echo "<p>Your dashboard is being prepared. Please check back later.</p>";
    }
    ?>

</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
