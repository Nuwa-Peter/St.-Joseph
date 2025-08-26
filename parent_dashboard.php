<?php
session_start();
require_once 'config.php';
require_once 'includes/header.php';

// 1. Authorization Check
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] !== 'parent') {
    // Redirect to login if not logged in or not a parent
    header("location: login.php");
    exit;
}

// 2. Get Parent ID
$parent_id = $_SESSION['id'];
$children = [];

// 3. Fetch associated children
$sql = "SELECT u.id, u.first_name, u.last_name, u.photo, st.name as stream_name, cl.name as class_level_name
        FROM parent_student ps
        JOIN users u ON ps.student_id = u.id
        LEFT JOIN stream_user su ON u.id = su.user_id
        LEFT JOIN streams st ON su.stream_id = st.id
        LEFT JOIN class_levels cl ON st.class_level_id = cl.id
        WHERE ps.parent_id = ?";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $parent_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $children[] = $row;
    }
    $stmt->close();
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Parent Dashboard</h2>
        <h4>Welcome, <?php echo htmlspecialchars($_SESSION["name"]); ?>!</h4>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">My Children</h5>
        </div>
        <div class="card-body">
            <?php if (empty($children)): ?>
                <div class="alert alert-info">You do not have any children linked to your account. Please contact the school administration.</div>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($children as $child): ?>
                        <a href="view_child_details.php?student_id=<?php echo $child['id']; ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <?php if (!empty($child['photo'])): ?>
                                    <img src="<?php echo htmlspecialchars($child['photo']); ?>" alt="Photo of <?php echo htmlspecialchars($child['first_name']); ?>" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="avatar-initials-lg"><?php echo htmlspecialchars(strtoupper(substr($child['first_name'], 0, 1) . substr($child['last_name'], 0, 1))); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1"><?php echo htmlspecialchars($child['first_name'] . ' ' . $child['last_name']); ?></h6>
                                <small class="text-muted"><?php echo htmlspecialchars($child['class_level_name'] . ' - ' . $child['stream_name']); ?></small>
                            </div>
                            <div class="ms-auto">
                                <i class="bi bi-chevron-right"></i>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
