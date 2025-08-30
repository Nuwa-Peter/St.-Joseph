<?php
// This widget requires the $conn database connection and the logged-in parent's ID.
$parent_id = $_SESSION['id'];
$children = [];

// Fetch associated children
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

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">My Children</h6>
    </div>
    <div class="card-body">
        <?php if (empty($children)): ?>
            <div class="alert alert-info">You do not have any children linked to your account. An administrator can link them for you from your user profile.</div>
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
