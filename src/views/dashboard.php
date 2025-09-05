<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../src/includes/header.php';

// Redirect if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: " . login_url());
    exit;
}

// A parent should not be on this dashboard
if ($_SESSION['role'] === 'parent') {
    header("location: " . parent_dashboard_url());
    exit;
}

$user_role = $_SESSION['role'];
$admin_roles = ['root', 'headteacher', 'director']; // Director has full admin access

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
        include __DIR__ . '/../../widgets/admin_stats.php';

        // You can include other admin-specific widgets here
        // include __DIR__ . '/../../widgets/recent_activity.php';

    } elseif ($user_role === 'teacher') {
        // --- Teacher Dashboard ---
        echo '<div class="row">';
        echo '<div class="col-lg-8">';
        include __DIR__ . '/../../widgets/teacher_classes.php';
        echo '</div>';
        echo '<div class="col-lg-4">';
        include __DIR__ . '/../../widgets/upcoming_assignments.php';
        echo '</div>';
        echo '</div>';

    } elseif ($user_role === 'student') {
        // --- Student Dashboard ---
        echo '<div class="row">';
        echo '<div class="col-lg-8">';
        include __DIR__ . '/../../widgets/student_grades_summary.php';
        echo '</div>';
        echo '<div class="col-lg-4">';
        include __DIR__ . '/../../widgets/upcoming_assignments.php';
        echo '</div>';
        echo '</div>';

    } else {
        // --- Default Dashboard for other roles ---
        echo "<p>Your dashboard is being prepared. Please check back later.</p>";
    }
    ?>

</div>

<?php
$conn->close();
require_once __DIR__ . '/../../src/includes/footer.php';
?>
