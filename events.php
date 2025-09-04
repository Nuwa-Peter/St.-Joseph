<?php
require_once 'config.php';
require_once 'includes/header.php';

// Authorization check: only admins can manage events
$admin_roles = ['headteacher', 'root', 'director'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $admin_roles)) {
    header("location: dashboard.php");
    exit;
}

$events = [];
$sql = "SELECT e.id, e.title, e.start_date, e.end_date, e.event_type, u.username as creator
        FROM events e
        LEFT JOIN users u ON e.created_by = u.id
        ORDER BY e.start_date DESC";

if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}

?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage School Events</h2>
        <a href="event_create.php" class="btn btn-primary"><i class="bi bi-plus-circle-fill me-2"></i>Create New Event</a>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (empty($events)): ?>
                <div class="alert alert-info">No events have been created yet.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $event): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($event['title']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($event['event_type']); ?></span></td>
                                    <td><?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($event['start_date']))); ?></td>
                                    <td><?php echo $event['end_date'] ? htmlspecialchars(date('F j, Y, g:i a', strtotime($event['end_date']))) : 'N/A'; ?></td>
                                    <td><?php echo htmlspecialchars($event['creator']); ?></td>
                                    <td>
                                        <a href="event_edit.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                                        <a href="event_delete.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this event?');"><i class="bi bi-trash-fill"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
