<?php
require_once 'config.php'; // Must be first to initialize session

// All logged-in users should be able to see announcements.
// Authorization for create/edit/delete is handled on those specific pages.
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: " . login_url());
    exit;
}

// Fetch announcements
$sql = "SELECT announcements.id, announcements.title, announcements.content, announcements.created_at, users.first_name, users.last_name
        FROM announcements
        JOIN users ON announcements.user_id = users.id
        ORDER BY announcements.created_at DESC";
$result = $conn->query($sql);

// Check for session flash messages
$success_message = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);

// Authorization check for allowing creation
$allowed_to_create = false;
if (isset($_SESSION['role'])) {
    $allowed_roles_for_creation = ['headteacher', 'root', 'admin'];
    if (in_array($_SESSION['role'], $allowed_roles_for_creation)) {
        $allowed_to_create = true;
    }
}


require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-megaphone-fill me-2"></i>Announcements</h1>
        <?php if ($allowed_to_create): ?>
            <a href="<?php echo announcement_create_url(); ?>" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Create Announcement</a>
        <?php endif; ?>
    </div>

    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="list-group">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <div class="list-group-item list-group-item-action flex-column align-items-start mb-3 border rounded">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1"><?php echo htmlspecialchars($row["title"]); ?></h5>
                                <small class="text-muted"><?php echo date("D, d M Y, g:i A", strtotime($row["created_at"])); ?></small>
                            </div>
                            <p class="mb-2"><?php echo nl2br(htmlspecialchars($row["content"])); ?></p>
                            <small class="text-muted">By <?php echo htmlspecialchars($row["first_name"] . ' ' . $row["last_name"]); ?></small>

                            <?php
                            // Show actions only to authorized users
                            $can_manage = false;
                            if (isset($_SESSION['role'])) {
                                $can_manage_roles = ['headteacher', 'root', 'admin'];
                                // Also allow the author to edit/delete their own announcement
                                if (in_array($_SESSION['role'], $can_manage_roles) || $_SESSION['id'] == $row['user_id']) {
                                    $can_manage = true;
                                }
                            }
                            ?>
                            <?php if ($can_manage): ?>
                            <div class="mt-2">
                                <a href="<?php echo url('announcement_edit.php', ['id' => $row['id']]); ?>" class="btn btn-outline-primary btn-sm">Edit</a>
                                <a href="<?php echo url('announcement_delete.php', ['id' => $row['id']]); ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete this announcement?');">Delete</a>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center p-4">
                        <p class="mb-0">No announcements found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
