<?php
require_once __DIR__ . '/../../config.php';

// Role-based access control
$authorized_roles = ['bursar', 'headteacher', 'root'];
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], $authorized_roles)) {
    header("location: dashboard.php?unauthorized=true");
    exit;
}

require_once __DIR__ . '/../../src/includes/header.php';

// Fetch distinct academic years for the termly filter dropdown
$academic_years = [];
$sql_years = "SELECT DISTINCT academic_year FROM invoices ORDER BY academic_year DESC";
if ($result = $conn->query($sql_years)) {
    while($row = $result->fetch_assoc()) {
        $academic_years[] = $row['academic_year'];
    }
}

// --- Report Generation Logic ---
$transactions = [];
$report_title = '';
$start_date = '';
$end_date = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $report_type = $_POST['report_type'] ?? '';

    if ($report_type === 'date_range') {
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $report_title = "Accountability Report from " . htmlspecialchars($start_date) . " to " . htmlspecialchars($end_date);
    } elseif ($report_type === 'termly') {
        $academic_year_post = $_POST['academic_year'];
        $term_post = $_POST['term'];
        $report_title = "Accountability Report for " . htmlspecialchars($academic_year_post) . " - " . htmlspecialchars($term_post);

        // Approximate term dates
        $year_parts = explode('/', $academic_year_post);
        $start_year = intval($year_parts[0]);

        switch ($term_post) {
            case 'Term 1':
                $start_date = "$start_year-01-01";
                $end_date = "$start_year-04-30";
                break;
            case 'Term 2':
                $start_date = "$start_year-05-01";
                $end_date = "$start_year-08-31";
                break;
            case 'Term 3':
                $start_date = "$start_year-09-01";
                $end_date = "$start_year-12-31";
                break;
        }
    }

    if (!empty($start_date) && !empty($end_date)) {
        $sql = "(SELECT
                    p.payment_date as transaction_date,
                    'Income' as transaction_type,
                    CONCAT('Payment for Invoice #', p.invoice_id, ' by ', u.first_name, ' ', u.last_name) as description,
                    p.amount as amount_in,
                    0 as amount_out
                FROM payments p
                JOIN invoices i ON p.invoice_id = i.id
                JOIN users u ON i.student_id = u.id
                WHERE p.payment_date BETWEEN ? AND ?)
                UNION ALL
                (SELECT
                    e.expense_date as transaction_date,
                    'Expense' as transaction_type,
                    CONCAT(e.category, ': ', e.description) as description,
                    0 as amount_in,
                    e.amount as amount_out
                FROM expenses e
                WHERE e.expense_date BETWEEN ? AND ?)
                ORDER BY transaction_date ASC, transaction_type DESC";

        $stmt = $conn->prepare($sql);
        // Bind the same date range to both parts of the UNION query
        $stmt->bind_param("ssss", $start_date, $end_date, $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        $transactions = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}
?>

<div class="container-fluid">
    <h2 class="text-primary my-4">Accountability Ledger</h2>

    <div class="card mb-4 no-print">
        <div class="card-header">
            <i class="bi bi-filter-circle me-2"></i>Generate Report
        </div>
        <div class="card-body">
            <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <button class="nav-link active" id="nav-daterange-tab" data-bs-toggle="tab" data-bs-target="#nav-daterange" type="button" role="tab" aria-controls="nav-daterange" aria-selected="true">By Date Range</button>
                    <button class="nav-link" id="nav-termly-tab" data-bs-toggle="tab" data-bs-target="#nav-termly" type="button" role="tab" aria-controls="nav-termly" aria-selected="false">By Term</button>
                </div>
            </nav>
            <div class="tab-content p-3 border border-top-0" id="nav-tabContent">
                <!-- Date Range Filter -->
                <div class="tab-pane fade show active" id="nav-daterange" role="tabpanel" aria-labelledby="nav-daterange-tab">
                    <form action="<?php echo accountability_url(); ?>" method="post">
                        <input type="hidden" name="report_type" value="date_range">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" name="start_date" id="start_date" required>
                            </div>
                            <div class="col-md-4">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" name="end_date" id="end_date" required>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-auto">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-today">Today</button>
                            </div>
                             <div class="col-auto">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-this-week">This Week</button>
                            </div>
                             <div class="col-auto">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-this-month">This Month</button>
                            </div>
                             <div class="col-auto">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-this-year">This Year</button>
                            </div>
                             <div class="col-auto">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-last-year">Last Year</button>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- Termly Filter -->
                <div class="tab-pane fade" id="nav-termly" role="tabpanel" aria-labelledby="nav-termly-tab">
                    <form action="<?php echo accountability_url(); ?>" method="post">
                         <input type="hidden" name="report_type" value="termly">
                        <div class="row align-items-end">
                            <div class="col-md-5">
                                <label for="academic_year" class="form-label">Academic Year</label>
                                <select name="academic_year" class="form-select" required>
                                    <option value="">Select Year...</option>
                                    <?php foreach($academic_years as $year): ?>
                                        <option value="<?php echo htmlspecialchars($year); ?>"><?php echo htmlspecialchars($year); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label for="term" class="form-label">Term</label>
                                <select name="term" class="form-select" required>
                                    <option value="">Select Term...</option>
                                    <option value="Term 1">Term 1</option>
                                    <option value="Term 2">Term 2</option>
                                    <option value="Term 3">Term 3</option>
                                </select>
                            </div>
                             <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Results -->
    <div id="report-results">
        <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><?php echo $report_title; ?></h4>
                    <button class="btn btn-secondary no-print" onclick="window.print();"><i class="bi bi-printer me-2"></i>Print Report</button>
                </div>
                <div class="card-body">
                    <?php if (!empty($transactions)):
                        // Calculate totals for the summary
                        $total_income = array_sum(array_column($transactions, 'amount_in'));
                        $total_expenses = array_sum(array_column($transactions, 'amount_out'));
                        $net_profit = $total_income - $total_expenses;
                    ?>
                        <!-- Summary Cards -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-success">Total Income</h6>
                                        <p class="card-text fs-5 fw-bold">UGX <?php echo number_format($total_income, 2); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-danger">
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-danger">Total Expenses</h6>
                                        <p class="card-text fs-5 fw-bold">UGX <?php echo number_format($total_expenses, 2); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-primary">
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-primary">Net Profit / Loss</h6>
                                        <p class="card-text fs-5 fw-bold <?php echo ($net_profit >= 0) ? 'text-success' : 'text-danger'; ?>">
                                            UGX <?php echo number_format($net_profit, 2); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h5 class="mt-4 mb-3">Transaction Details</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Details</th>
                                        <th class="text-end">Income (UGX)</th>
                                        <th class="text-end">Expense (UGX)</th>
                                        <th class="text-end">Running Balance (UGX)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $running_balance = 0;
                                    foreach ($transactions as $tx):
                                        $running_balance += $tx['amount_in'];
                                        $running_balance -= $tx['amount_out'];
                                    ?>
                                    <tr>
                                        <td><?php echo date("d-M-Y", strtotime($tx['transaction_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($tx['description']); ?></td>
                                        <td class="text-end text-success">
                                            <?php if($tx['amount_in'] > 0) echo number_format($tx['amount_in'], 2); ?>
                                        </td>
                                        <td class="text-end text-danger">
                                            <?php if($tx['amount_out'] > 0) echo '(' . number_format($tx['amount_out'], 2) . ')'; ?>
                                        </td>
                                        <td class="text-end fw-bold"><?php echo number_format($running_balance, 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning mb-0">No transactions found for the selected period.</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date();
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');

    document.getElementById('btn-today').addEventListener('click', function() {
        const todayStr = new Date().toISOString().slice(0, 10);
        startDateInput.value = todayStr;
        endDateInput.value = todayStr;
    });

    document.getElementById('btn-this-week').addEventListener('click', function() {
        const today = new Date();
        const firstDayOfWeek = new Date(today.setDate(today.getDate() - today.getDay()));
        const lastDayOfWeek = new Date(today.setDate(today.getDate() - today.getDay() + 6));
        startDateInput.value = firstDayOfWeek.toISOString().slice(0, 10);
        endDateInput.value = lastDayOfWeek.toISOString().slice(0, 10);
    });

    document.getElementById('btn-this-month').addEventListener('click', function() {
        const today = new Date();
        const year = today.getFullYear();
        const month = today.getMonth();
        const firstDayOfMonth = new Date(year, month, 1);
        const lastDayOfMonth = new Date(year, month + 1, 0);
        startDateInput.value = firstDayOfMonth.toISOString().slice(0, 10);
        endDateInput.value = lastDayOfMonth.toISOString().slice(0, 10);
    });

    document.getElementById('btn-this-year').addEventListener('click', function() {
        const year = new Date().getFullYear();
        const firstDayOfYear = new Date(year, 0, 1);
        const lastDayOfYear = new Date(year, 11, 31);
        startDateInput.value = firstDayOfYear.toISOString().slice(0, 10);
        endDateInput.value = lastDayOfYear.toISOString().slice(0, 10);
    });

    document.getElementById('btn-last-year').addEventListener('click', function() {
        const year = new Date().getFullYear() - 1;
        const firstDayOfLastYear = new Date(year, 0, 1);
        const lastDayOfLastYear = new Date(year, 11, 31);
        startDateInput.value = firstDayOfLastYear.toISOString().slice(0, 10);
        endDateInput.value = lastDayOfLastYear.toISOString().slice(0, 10);
    });
});
</script>

<?php
$conn->close();
require_once __DIR__ . '/../../src/includes/footer.php';
?>
