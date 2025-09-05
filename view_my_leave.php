<?php
require_once 'config.php';

// Ensure user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION['id'];

// Fetch leave requests for the current user
$sql = "SELECT start_date, end_date, reason, status, requested_at FROM leave_requests WHERE user_id = ? ORDER BY requested_at DESC";
$leave_requests = [];
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $leave_requests = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

require_once 'includes/header.php';
?>

<div class="container">
    <h1 class="my-4">My Leave Requests</h1>
    <div class="card">
        <div class="card-header">
            <i class="bi bi-calendar-check me-2"></i>Your Submitted Requests
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Requested On</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Reason</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($leave_requests)): ?>
                            <tr>
                                <td colspan="5" class="text-center">You have not made any leave requests.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($leave_requests as $request): ?>
                                <tr>
                                    <td><?php echo date("d-M-Y H:i", strtotime($request['requested_at'])); ?></td>
                                    <td><?php echo date("d-M-Y", strtotime($request['start_date'])); ?></td>
                                    <td><?php echo date("d-M-Y", strtotime($request['end_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($request['reason']); ?></td>
                                    <td>
                                        <?php
                                        $status = htmlspecialchars($request['status']);
                                        $badge_class = 'bg-secondary';
                                        if ($status === 'approved') {
                                            $badge_class = 'bg-success';
                                        } elseif ($status === 'rejected') {
                                            $badge_class = 'bg-danger';
                                        } elseif ($status === 'pending') {
                                            $badge_class = 'bg-warning text-dark';
                                        }
                                        echo "<span class='badge " . $badge_class . "'>" . ucfirst($status) . "</span>";
                                        ?>
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
require_once 'includes/footer.php';
?>
