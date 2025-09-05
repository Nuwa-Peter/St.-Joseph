<?php
require_once __DIR__ . '/../../config.php';

// Ensure user is an admin
$admin_roles = ['root', 'headteacher', 'director', 'dos', 'deputy headteacher'];
if (!isset($_SESSION["loggedin"]) || !in_array($_SESSION['role'], $admin_roles)) {
    header("location: dashboard.php");
    exit;
}

// Fetch all leave requests
$sql = "SELECT lr.id, lr.start_date, lr.end_date, lr.reason, lr.status, lr.requested_at, u.first_name, u.last_name
        FROM leave_requests lr
        JOIN users u ON lr.user_id = u.id
        ORDER BY lr.requested_at DESC";
$all_requests = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../../src/includes/header.php';
?>

<div class="container">
    <h1 class="my-4">Manage Leave Requests</h1>
    <div class="card">
        <div class="card-header">
            <i class="bi bi-calendar-event me-2"></i>All Submitted Requests
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Requester</th>
                            <th>Requested On</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Duration (Days)</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($all_requests)): ?>
                            <tr>
                                <td colspan="8" class="text-center">No leave requests found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($all_requests as $request): ?>
                                <?php
                                    $start = new DateTime($request['start_date']);
                                    $end = new DateTime($request['end_date']);
                                    $duration = $start->diff($end)->days + 1; // Add 1 to include the start day
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></td>
                                    <td><?php echo date("d-M-Y H:i", strtotime($request['requested_at'])); ?></td>
                                    <td><?php echo date("d-M-Y", strtotime($request['start_date'])); ?></td>
                                    <td><?php echo date("d-M-Y", strtotime($request['end_date'])); ?></td>
                                    <td><?php echo $duration; ?></td>
                                    <td><?php echo htmlspecialchars($request['reason']); ?></td>
                                    <td>
                                        <?php
                                        $status = htmlspecialchars($request['status']);
                                        $badge_class = 'bg-secondary';
                                        if ($status === 'approved') $badge_class = 'bg-success';
                                        if ($status === 'rejected') $badge_class = 'bg-danger';
                                        if ($status === 'pending') $badge_class = 'bg-warning text-dark';
                                        echo "<span class='badge " . $badge_class . "'>" . ucfirst($status) . "</span>";
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($request['status'] === 'pending'): ?>
                                            <form action="<?php echo url('leave/update'); ?>" method="post" class="d-inline">
                                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                <button type="submit" name="status" value="approved" class="btn btn-sm btn-success" title="Approve"><i class="bi bi-check-lg"></i></button>
                                                <button type="submit" name="status" value="rejected" class="btn btn-sm btn-danger" title="Reject"><i class="bi bi-x-lg"></i></button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once __DIR__ . '/../../src/includes/footer.php';
?>
