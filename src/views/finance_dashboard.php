<?php
require_once __DIR__ . '/../../config.php';

// Role-based access control
$authorized_roles = ['bursar', 'headteacher', 'root'];
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], $authorized_roles)) {
    header("location: dashboard.php?unauthorized=true");
    exit;
}

require_once __DIR__ . '/../../src/includes/header.php';

// --- Data Fetching for Dashboard ---
$errors = [];

// 1. Main Financial Summary
$summary = [
    'total_billed' => 0,
    'total_collected' => 0,
    'total_outstanding' => 0,
];
$sql_summary = "SELECT SUM(total_amount) as total_billed, SUM(amount_paid) as total_collected FROM invoices WHERE status != 'cancelled'";
if ($result = $conn->query($sql_summary)) {
    $row = $result->fetch_assoc();
    $summary['total_billed'] = $row['total_billed'] ?? 0;
    $summary['total_collected'] = $row['total_collected'] ?? 0;
    $summary['total_outstanding'] = $summary['total_billed'] - $summary['total_collected'];
} else {
    $errors[] = "Could not fetch financial summary: " . $conn->error;
}


// 2. Student Payment Status Counts
$student_counts = [
    'with_balance' => 0,
    'fully_paid' => 0,
    'total_students' => 0,
];

// This query is complex, so we'll do it carefully
$sql_student_counts = "SELECT
    COUNT(CASE WHEN balance > 0 THEN 1 END) as students_with_balance,
    COUNT(CASE WHEN balance <= 0 AND total_due > 0 THEN 1 END) as students_fully_paid,
    COUNT(*) as total_students
FROM (
    SELECT
        u.id,
        IFNULL(SUM(i.total_amount), 0) as total_due,
        (IFNULL(SUM(i.total_amount), 0) - IFNULL(SUM(i.amount_paid), 0)) AS balance
    FROM users u
    LEFT JOIN invoices i ON u.id = i.student_id AND i.status != 'cancelled'
    WHERE u.role = 'student' AND u.status = 'active'
    GROUP BY u.id
) as ledger";

if ($result_students = $conn->query($sql_student_counts)) {
    $counts = $result_students->fetch_assoc();
    $student_counts['with_balance'] = $counts['students_with_balance'] ?? 0;
    $student_counts['fully_paid'] = $counts['students_fully_paid'] ?? 0;
    $student_counts['total_students'] = $counts['total_students'] ?? 0;
} else {
     $errors[] = "Could not fetch student payment counts: " . $conn->error;
}


// 3. Recent Payments
$recent_payments = [];
$sql_recent = "SELECT
                    p.amount,
                    p.payment_date,
                    CONCAT(u.first_name, ' ', u.last_name) as student_name
                FROM payments p
                JOIN invoices i ON p.invoice_id = i.id
                JOIN users u ON i.student_id = u.id
                ORDER BY p.payment_date DESC, p.id DESC
                LIMIT 5";
if ($result_recent = $conn->query($sql_recent)) {
    $recent_payments = $result_recent->fetch_all(MYSQLI_ASSOC);
} else {
    $errors[] = "Could not fetch recent payments: " . $conn->error;
}

?>

<div class="container-fluid">
    <h2 class="text-primary my-4">Finance Dashboard</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error) echo "<p class='mb-0'>" . htmlspecialchars($error) . "</p>"; ?>
        </div>
    <?php endif; ?>

    <!-- Metric Cards -->
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card text-white bg-primary shadow">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-journal-text me-2"></i>Total Billed</h5>
                    <p class="card-text fs-4 fw-bold">UGX <?php echo number_format($summary['total_billed'], 0); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card text-white bg-success shadow">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-check-circle-fill me-2"></i>Total Collected</h5>
                    <p class="card-text fs-4 fw-bold">UGX <?php echo number_format($summary['total_collected'], 0); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card text-white bg-danger shadow">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>Total Outstanding</h5>
                    <p class="card-text fs-4 fw-bold">UGX <?php echo number_format($summary['total_outstanding'], 0); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Financial Summary Chart -->
        <div class="col-lg-7 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <i class="bi bi-bar-chart-line-fill me-2"></i>Financial Summary
                </div>
                <div class="card-body">
                    <p class="text-muted">A visual breakdown of collected vs. outstanding fees.</p>
                    <?php
                        $collected_percent = ($summary['total_billed'] > 0) ? ($summary['total_collected'] / $summary['total_billed']) * 100 : 0;
                        $outstanding_percent = 100 - $collected_percent;
                    ?>
                    <div class="progress" style="height: 40px; font-size: 1rem;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $collected_percent; ?>%;" aria-valuenow="<?php echo $collected_percent; ?>" aria-valuemin="0" aria-valuemax="100">
                            <?php echo number_format($collected_percent, 1); ?>%
                        </div>
                        <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo $outstanding_percent; ?>%;" aria-valuenow="<?php echo $outstanding_percent; ?>" aria-valuemin="0" aria-valuemax="100">
                             <?php echo number_format($outstanding_percent, 1); ?>%
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <span><i class="bi bi-square-fill text-success"></i> Collected</span>
                        <span><i class="bi bi-square-fill text-danger"></i> Outstanding</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Payment Status -->
        <div class="col-lg-5 mb-4">
             <div class="card h-100">
                <div class="card-header">
                    <i class="bi bi-people-fill me-2"></i>Student Payment Status
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Students with Outstanding Balance
                            <span class="badge bg-danger rounded-pill fs-6"><?php echo $student_counts['with_balance']; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Students Fully Paid
                            <span class="badge bg-success rounded-pill fs-6"><?php echo $student_counts['fully_paid']; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Active Students
                            <span class="badge bg-primary rounded-pill fs-6"><?php echo $student_counts['total_students']; ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-clock-history me-2"></i>Recent Payments
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Payment Date</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_payments)): ?>
                            <?php foreach($recent_payments as $payment): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($payment['student_name']); ?></td>
                                    <td><?php echo date("d-M-Y", strtotime($payment['payment_date'])); ?></td>
                                    <td class="text-end">UGX <?php echo number_format($payment['amount'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center">No recent payments found.</td>
                            </tr>
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
