<?php
require_once 'config.php';

// Role-based access control
$authorized_roles = ['bursar', 'headteacher', 'root', 'admin'];
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], $authorized_roles)) {
    header("location: " . dashboard_url());
    exit;
}

// --- Data Fetching ---
$students_ledger = [];

// Base query to get financial summary for each student
$sql = "SELECT
            u.id,
            u.first_name,
            u.last_name,
            u.lin,
            IFNULL(inv.total_due, 0) AS total_due,
            IFNULL(inv.total_paid, 0) AS total_paid,
            (IFNULL(inv.total_due, 0) - IFNULL(inv.total_paid, 0)) AS balance
        FROM
            users u
        LEFT JOIN (
            SELECT
                student_id,
                SUM(total_amount) AS total_due,
                SUM(amount_paid) AS total_paid
            FROM
                invoices
            WHERE
                status != 'cancelled'
            GROUP BY
                student_id
        ) AS inv ON u.id = inv.student_id
        WHERE
            u.role = 'student' AND u.status = 'active'";

$where_clauses = [];
$params = [];
$types = '';

// Filtering logic
$search_student = $_GET['search_student'] ?? '';
$filter_status = $_GET['filter_status'] ?? '';

if (!empty($search_student)) {
    $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.lin LIKE ?)";
    $search_term = "%{$search_student}%";
    array_push($params, $search_term, $search_term, $search_term);
    $types .= 'sss';
}

if (!empty($filter_status)) {
    if ($filter_status === 'has_balance') {
        $sql .= " HAVING balance > 0";
    } elseif ($filter_status === 'fully_paid') {
        $sql .= " HAVING balance <= 0 AND total_due > 0";
    } elseif ($filter_status === 'no_invoices') {
         $sql .= " HAVING total_due = 0";
    }
}

$sql .= " ORDER BY u.last_name, u.first_name";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$students_ledger = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <h2 class="text-primary my-4"><i class="bi bi-person-badge me-2"></i>Student Accounts Ledger</h2>

    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <i class="bi bi-filter me-2"></i>Filter Student Accounts
        </div>
        <div class="card-body">
            <form action="<?php echo student_accounts_url(); ?>" method="get" class="row g-3 align-items-center">
                <div class="col-md-6">
                    <label for="search_student" class="visually-hidden">Search Student</label>
                    <input type="text" class="form-control" name="search_student" id="search_student" placeholder="Search by student name or LIN..." value="<?php echo htmlspecialchars($search_student); ?>">
                </div>
                <div class="col-md-4">
                    <label for="filter_status" class="visually-hidden">Payment Status</label>
                    <select name="filter_status" id="filter_status" class="form-select">
                        <option value="">All Students</option>
                        <option value="has_balance" <?php echo ($filter_status == 'has_balance') ? 'selected' : ''; ?>>Has Outstanding Balance</option>
                        <option value="fully_paid" <?php echo ($filter_status == 'fully_paid') ? 'selected' : ''; ?>>Fully Paid</option>
                        <option value="no_invoices" <?php echo ($filter_status == 'no_invoices') ? 'selected' : ''; ?>>No Invoices</option>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-info">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Student Name</th>
                            <th>LIN</th>
                            <th class="text-end">Total Billed</th>
                            <th class="text-end">Total Paid</th>
                            <th class="text-end">Balance</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($students_ledger)): ?>
                            <?php foreach ($students_ledger as $student):
                                $balance_status = '';
                                $balance_class = '';
                                if ($student['total_due'] == 0) {
                                    $balance_status = 'No Invoices';
                                    $balance_class = 'text-secondary';
                                } elseif ($student['balance'] > 0) {
                                    $balance_status = 'Has Balance';
                                    $balance_class = 'text-danger';
                                } else {
                                    $balance_status = 'Fully Paid';
                                    $balance_class = 'text-success';
                                }
                            ?>
                                <tr>
                                    <td><a href="<?php echo student_ledger_url($student['id']); ?>"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></a></td>
                                    <td><?php echo htmlspecialchars($student['lin'] ?? 'N/A'); ?></td>
                                    <td class="text-end"><?php echo number_format($student['total_due'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($student['total_paid'], 2); ?></td>
                                    <td class="text-end fw-bold <?php echo $balance_class; ?>"><?php echo number_format($student['balance'], 2); ?></td>
                                    <td><span class="badge rounded-pill <?php echo strpos($balance_class, 'danger') ? 'bg-danger' : (strpos($balance_class, 'success') ? 'bg-success' : 'bg-secondary'); ?>"><?php echo $balance_status; ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No students found matching your criteria.</td>
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
require_once 'includes/footer.php';
?>
