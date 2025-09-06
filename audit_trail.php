<?php
require_once 'config.php';

// This page is for the root super-administrator only.
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] !== 'root') {
    header("location: " . dashboard_url()); // Redirect non-root users
    exit;
}

// --- Fetch Audit Logs ---
$filter_action = $_GET['action'] ?? '';
$sql = "SELECT al.id, al.action, al.auditable_type, al.auditable_id, al.old_values, al.new_values, al.created_at, u.first_name, u.last_name
        FROM audit_logs al
        LEFT JOIN users u ON al.user_id = u.id";

$params = [];
$types = '';
if (!empty($filter_action)) {
    $sql .= " WHERE al.action = ?";
    $params[] = $filter_action;
    $types .= 's';
}

$sql .= " ORDER BY al.created_at DESC LIMIT 100"; // Limit to 100 for performance

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$logs = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <h1 class="my-4"><i class="bi bi-shield-lock-fill me-2"></i>System Audit Trail</h1>
    <p>This page shows a log of all significant actions taken within the system. Showing the last 100 entries.</p>

    <div class="card shadow-sm">
        <div class="card-header">
            <i class="bi bi-list-ul me-2"></i>Audit Logs
        </div>
        <div class="card-body">
            <!-- Filter Form -->
            <form action="<?php echo audit_url(); ?>" method="get" class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="action" class="form-label">Filter by Action</label>
                    <select name="action" id="action" class="form-select">
                        <option value="">All Actions</option>
                        <option value="created" <?php if ($filter_action == 'created') echo 'selected'; ?>>Created</option>
                        <option value="updated" <?php if ($filter_action == 'updated') echo 'selected'; ?>>Updated</option>
                        <option value="deleted" <?php if ($filter_action == 'deleted') echo 'selected'; ?>>Deleted</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Record Type</th>
                            <th>Record ID</th>
                            <th>Timestamp</th>
                            <th>Changes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No audit logs found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($log['first_name'] . ' ' . $log['last_name']); ?></td>
                                    <td>
                                        <?php
                                        $action = htmlspecialchars($log['action']);
                                        $badge_class = 'bg-secondary';
                                        if ($action === 'created') $badge_class = 'bg-success';
                                        if ($action === 'updated') $badge_class = 'bg-info text-dark';
                                        if ($action === 'deleted') $badge_class = 'bg-danger';
                                        echo "<span class='badge " . $badge_class . "'>" . ucfirst($action) . "</span>";
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars(basename(str_replace('\\', '/', $log['auditable_type']))); ?></td>
                                    <td><?php echo htmlspecialchars($log['auditable_id']); ?></td>
                                    <td><?php echo date("d-M-Y H:i:s", strtotime($log['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#logDetailsModal"
                                                data-old='<?php echo htmlspecialchars($log['old_values']); ?>'
                                                data-new='<?php echo htmlspecialchars($log['new_values']); ?>'>
                                            View Details
                                        </button>
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

<!-- Log Details Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1" aria-labelledby="logDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="logDetailsModalLabel">Log Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Old Values</h6>
                <pre id="old-values-pre" class="bg-light p-2 rounded"></pre>
            </div>
            <div class="col-md-6">
                <h6>New Values</h6>
                <pre id="new-values-pre" class="bg-light p-2 rounded"></pre>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var logDetailsModal = document.getElementById('logDetailsModal');
    logDetailsModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var oldValuesStr = button.getAttribute('data-old') || '{}';
        var newValuesStr = button.getAttribute('data-new') || '{}';

        var oldPre = logDetailsModal.querySelector('#old-values-pre');
        var newPre = logDetailsModal.querySelector('#new-values-pre');

        try {
            // Prettify the JSON string
            var oldValues = JSON.parse(oldValuesStr);
            var newValues = JSON.parse(newValuesStr);
            oldPre.textContent = JSON.stringify(oldValues, null, 2);
            newPre.textContent = JSON.stringify(newValues, null, 2);
        } catch (e) {
            // If JSON is invalid, show the raw string
            oldPre.textContent = oldValuesStr;
            newPre.textContent = newValuesStr;
            console.error("Error parsing JSON for audit log details:", e);
        }
    });
});
</script>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
