<?php
require_once __DIR__ . '/../../config.php';

// Role-based access control
$authorized_roles = ['bursar', 'headteacher', 'root'];
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], $authorized_roles)) {
    header("location: dashboard.php?unauthorized=true");
    exit;
}

require_once __DIR__ . '/../../src/includes/header.php';

$report_data = [];
$report_type = $_POST['report_type'] ?? '';
$report_title = '';
$report_total = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- Fee Collection Report ---
    if ($report_type === 'fee_collection') {
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $report_title = "Fee Collection Report from " . htmlspecialchars($start_date) . " to " . htmlspecialchars($end_date);

        $sql = "SELECT p.payment_date, p.amount, p.payment_method, i.id as invoice_id, u.first_name, u.last_name
                FROM payments p
                JOIN invoices i ON p.invoice_id = i.id
                JOIN users u ON i.student_id = u.id
                WHERE p.payment_date BETWEEN ? AND ?
                ORDER BY p.payment_date ASC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        $report_data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Calculate total
        foreach($report_data as $row) {
            $report_total += $row['amount'];
        }
    }
    // --- Outstanding Fees Report ---
    elseif ($report_type === 'outstanding_fees') {
        $report_title = "Outstanding Fees Report";
        $sql = "SELECT u.id, u.first_name, u.last_name, u.lin,
                       SUM(i.total_amount) as total_due,
                       SUM(i.amount_paid) as total_paid,
                       (SUM(i.total_amount) - SUM(i.amount_paid)) as balance
                FROM users u
                JOIN invoices i ON u.id = i.student_id
                WHERE u.role = 'student' AND u.status = 'active' AND i.status != 'cancelled'
                GROUP BY u.id, u.first_name, u.last_name, u.lin
                HAVING balance > 0
                ORDER BY u.last_name, u.first_name";

        $result = $conn->query($sql);
        $report_data = $result->fetch_all(MYSQLI_ASSOC);

        // Calculate total
        foreach($report_data as $row) {
            $report_total += $row['balance'];
        }
    }
}

?>
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #report-results, #report-results * {
            visibility: visible;
        }
        #report-results {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .no-print {
            display: none;
        }
    }
</style>

<div class="container-fluid">
    <h2 class="text-primary my-4 no-print">Financial Reports</h2>

    <div class="card mb-4 no-print">
        <div class="card-header">
            Report Generator
        </div>
        <div class="card-body">
            <div class="accordion" id="reportsAccordion">
                <!-- Fee Collection Report Form -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            <i class="bi bi-cash-stack me-2"></i> Fee Collection Report
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#reportsAccordion">
                        <div class="accordion-body">
                            <form action="finance_reports.php" method="post">
                                <input type="hidden" name="report_type" value="fee_collection">
                                <div class="row">
                                    <div class="col-md-5">
                                        <label for="start_date" class="form-label">Start Date</label>
                                        <input type="date" class="form-control" name="start_date" required>
                                    </div>
                                    <div class="col-md-5">
                                        <label for="end_date" class="form-label">End Date</label>
                                        <input type="date" class="form-control" name="end_date" required>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary w-100">Generate</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Outstanding Fees Report Form -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                           <i class="bi bi-person-exclamation me-2"></i> Outstanding Fees Report
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#reportsAccordion">
                        <div class="accordion-body">
                             <form action="finance_reports.php" method="post">
                                <input type="hidden" name="report_type" value="outstanding_fees">
                                <p>This report lists all active students with an outstanding balance.</p>
                                <button type="submit" class="btn btn-primary">Generate Outstanding Fees Report</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($report_data)): ?>
    <div id="report-results">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><?php echo htmlspecialchars($report_title); ?></h4>
                <button class="btn btn-secondary no-print" onclick="window.print();"><i class="bi bi-printer me-2"></i>Print Report</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <?php if ($report_type === 'fee_collection'): ?>
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Student</th>
                                    <th>Invoice #</th>
                                    <th>Payment Method</th>
                                    <th class="text-end">Amount (UGX)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($report_data as $row): ?>
                                <tr>
                                    <td><?php echo date("d-M-Y", strtotime($row['payment_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                    <td>#<?php echo $row['invoice_id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                                    <td class="text-end"><?php echo number_format($row['amount'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-group-divider">
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Total Collected:</td>
                                    <td class="text-end fw-bold">UGX <?php echo number_format($report_total, 2); ?></td>
                                </tr>
                            </tfoot>
                        <?php elseif ($report_type === 'outstanding_fees'): ?>
                             <thead class="table-light">
                                <tr>
                                    <th>Student Name</th>
                                    <th>LIN</th>
                                    <th class="text-end">Total Billed</th>
                                    <th class="text-end">Total Paid</th>
                                    <th class="text-end">Outstanding Balance (UGX)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($report_data as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['lin'] ?? 'N/A'); ?></td>
                                    <td class="text-end"><?php echo number_format($row['total_due'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($row['total_paid'], 2); ?></td>
                                    <td class="text-end fw-bold"><?php echo number_format($row['balance'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                             <tfoot class="table-group-divider">
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Total Outstanding:</td>
                                    <td class="text-end fw-bold">UGX <?php echo number_format($report_total, 2); ?></td>
                                </tr>
                            </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
        <div class="alert alert-warning">No data found for the selected criteria.</div>
    <?php endif; ?>
</div>

<?php
$conn->close();
require_once __DIR__ . '/../../src/includes/footer.php';
?>
