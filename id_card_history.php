<?php
require_once 'config.php';
require_once 'includes/header.php';

// Ensure user is an admin
$admin_roles = ['root', 'headteacher'];
if (!isset($_SESSION["loggedin"]) || !in_array($_SESSION['role'], $admin_roles)) {
    header("location: dashboard.php");
    exit;
}

// Fetch ID card logs from the database
$sql = "
    SELECT
        log.id,
        log.issued_at,
        student.id AS student_id,
        student.first_name AS student_first_name,
        student.last_name AS student_last_name,
        student.unique_id AS student_unique_id,
        issuer.first_name AS issuer_first_name,
        issuer.last_name AS issuer_last_name
    FROM
        id_card_logs AS log
    JOIN
        users AS student ON log.student_id = student.id
    JOIN
        users AS issuer ON log.issued_by_user_id = issuer.id
    ORDER BY
        log.issued_at DESC
";

$logs = [];
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
}

$conn->close();
?>

<div class="container-fluid">
    <h2 class="my-4">ID Card Issuance History</h2>

    <div class="card">
        <div class="card-header">
            <i class="bi bi-clock-history me-2"></i>Log of Generated ID Cards
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Log ID</th>
                            <th>Student Name</th>
                            <th>Student ID</th>
                            <th>Issued By</th>
                            <th>Date Issued</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($logs)): ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($log['id']); ?></td>
                                    <td>
                                        <a href="student_view.php?id=<?php echo htmlspecialchars($log['student_id'] ?? ''); ?>">
                                            <?php echo htmlspecialchars($log['student_first_name'] . ' ' . $log['student_last_name']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($log['student_unique_id']); ?></td>
                                    <td><?php echo htmlspecialchars($log['issuer_first_name'] . ' ' . $log['issuer_last_name']); ?></td>
                                    <td><?php echo date("F j, Y, g:i a", strtotime($log['issued_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No ID cards have been logged yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
