<?php
require_once 'config.php';

// Role-based access control
$authorized_roles = ['bursar', 'headteacher', 'root', 'admin'];
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], $authorized_roles)) {
    header("location: " . dashboard_url());
    exit;
}

$errors = [];

// --- Handle Invoice Generation ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generate_invoices'])) {
    $fee_structure_id = intval($_POST['fee_structure_id']);
    $class_level_id = intval($_POST['class_level_id']);
    $term = trim($_POST['term']);
    $due_date = trim($_POST['due_date']);

    if (empty($fee_structure_id)) $errors[] = "Fee Structure is required.";
    if (empty($class_level_id)) $errors[] = "Class is required.";
    if (empty($term)) $errors[] = "Term is required.";
    if (empty($due_date)) $errors[] = "Due date is required.";

    if (empty($errors)) {
        $conn->begin_transaction();
        try {
            $total_amount = 0;
            $stmt_items = $conn->prepare("SELECT SUM(amount) as total FROM fee_items WHERE fee_structure_id = ?");
            $stmt_items->bind_param("i", $fee_structure_id);
            $stmt_items->execute();
            $result_items = $stmt_items->get_result();
            if ($result_items->num_rows > 0) $total_amount = $result_items->fetch_assoc()['total'];
            $stmt_items->close();

            if ($total_amount <= 0) throw new Exception("Selected fee structure has no items or total amount is zero.");

            $academic_year = '';
            $stmt_struct = $conn->prepare("SELECT academic_year FROM fee_structures WHERE id = ?");
            $stmt_struct->bind_param("i", $fee_structure_id);
            $stmt_struct->execute();
            $result_struct = $stmt_struct->get_result();
            if ($result_struct->num_rows > 0) $academic_year = $result_struct->fetch_assoc()['academic_year'];
            $stmt_struct->close();

            $student_ids = [];
            $sql_students = "SELECT su.user_id FROM stream_user su JOIN streams s ON su.stream_id = s.id WHERE s.class_level_id = ?";
            $stmt_students = $conn->prepare($sql_students);
            $stmt_students->bind_param("i", $class_level_id);
            $stmt_students->execute();
            $result_students = $stmt_students->get_result();
            while($row = $result_students->fetch_assoc()) $student_ids[] = $row['user_id'];
            $stmt_students->close();

            if (empty($student_ids)) throw new Exception("No students found in the selected class.");

            $sql_insert_invoice = "INSERT INTO invoices (student_id, fee_structure_id, academic_year, term, total_amount, due_date, status) VALUES (?, ?, ?, ?, ?, ?, 'unpaid')";
            $stmt_insert = $conn->prepare($sql_insert_invoice);

            foreach ($student_ids as $student_id) {
                $stmt_insert->bind_param("iissds", $student_id, $fee_structure_id, $academic_year, $term, $total_amount, $due_date);
                if (!$stmt_insert->execute()) throw new Exception("Failed to create invoice for student ID: $student_id. " . $stmt_insert->error);
            }
            $stmt_insert->close();

            $conn->commit();
            $_SESSION['success_message'] = "Successfully generated " . count($student_ids) . " invoices for the selected class.";

        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error_message'] = "An error occurred during invoice generation: " . $e->getMessage();
        }
    } else {
        $_SESSION['form_errors'] = $errors;
    }
    header("Location: " . invoices_url());
    exit();
}

// --- Handle Payment Recording ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['record_payment'])) {
    $invoice_id = intval($_POST['invoice_id']);
    $amount_paid_now = floatval($_POST['amount']);
    $payment_date = trim($_POST['payment_date']);
    $payment_method = trim($_POST['payment_method']);
    $notes = trim($_POST['notes']);
    $recorded_by = $_SESSION['id'];

    if (empty($invoice_id) || $amount_paid_now <= 0 || empty($payment_date) || empty($payment_method)) {
        $_SESSION['error_message'] = "Invalid payment data provided. Please fill all required fields.";
    } else {
        $conn->begin_transaction();
        try {
            $stmt_invoice = $conn->prepare("SELECT total_amount, amount_paid FROM invoices WHERE id = ?");
            $stmt_invoice->bind_param("i", $invoice_id);
            $stmt_invoice->execute();
            $invoice_details = $stmt_invoice->get_result()->fetch_assoc();
            $stmt_invoice->close();

            if (!$invoice_details) throw new Exception("Invoice not found.");

            $balance = $invoice_details['total_amount'] - $invoice_details['amount_paid'];
            if ($amount_paid_now > $balance) throw new Exception("Payment amount cannot be greater than the outstanding balance of " . number_format($balance, 2));

            $stmt_pay = $conn->prepare("INSERT INTO payments (invoice_id, amount, payment_date, payment_method, notes, recorded_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_pay->bind_param("idsssi", $invoice_id, $amount_paid_now, $payment_date, $payment_method, $notes, $recorded_by);
            if (!$stmt_pay->execute()) throw new Exception("Failed to record payment transaction: " . $stmt_pay->error);
            $stmt_pay->close();

            $stmt_update = $conn->prepare("UPDATE invoices SET amount_paid = amount_paid + ? WHERE id = ?");
            $stmt_update->bind_param("di", $amount_paid_now, $invoice_id);
            if (!$stmt_update->execute()) throw new Exception("Failed to update invoice amount: " . $stmt_update->error);
            $stmt_update->close();

            $new_balance = $balance - $amount_paid_now;
            $new_status = ($new_balance <= 0) ? 'paid' : 'partially_paid';
            $stmt_status = $conn->prepare("UPDATE invoices SET status = ? WHERE id = ?");
            $stmt_status->bind_param("si", $new_status, $invoice_id);
            if (!$stmt_status->execute()) throw new Exception("Failed to update invoice status: " . $stmt_status->error);
            $stmt_status->close();

            $conn->commit();
            $_SESSION['success_message'] = "Payment of " . number_format($amount_paid_now, 2) . " recorded successfully for Invoice #{$invoice_id}.";

        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error_message'] = "An error occurred while recording the payment: " . $e->getMessage();
        }
    }
    header("Location: " . invoices_url());
    exit();
}

// --- Data Fetching ---
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
if (isset($_SESSION['form_errors'])) {
    $errors = array_merge($errors, $_SESSION['form_errors']);
    unset($_SESSION['form_errors']);
}
unset($_SESSION['success_message'], $_SESSION['error_message']);

$structures_for_modal = $conn->query("SELECT id, name, academic_year FROM fee_structures ORDER BY academic_year DESC, name ASC")->fetch_all(MYSQLI_ASSOC);
$classes_for_modal = $conn->query("SELECT id, name FROM class_levels ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

$sql = "SELECT i.id, i.academic_year, i.term, i.total_amount, i.amount_paid, i.due_date, i.status, CONCAT(u.first_name, ' ', u.last_name) AS student_name, u.id as student_id FROM invoices AS i JOIN users AS u ON i.student_id = u.id";
$where_clauses = [];
$params = [];
$types = '';
$search_student = $_GET['search_student'] ?? '';
$filter_status = $_GET['filter_status'] ?? '';

if (!empty($search_student)) {
    $where_clauses[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.lin LIKE ?)";
    $search_term = "%{$search_student}%";
    array_push($params, $search_term, $search_term, $search_term);
    $types .= 'sss';
}
if (!empty($filter_status)) {
    $where_clauses[] = "i.status = ?";
    $params[] = $filter_status;
    $types .= 's';
}
if (!empty($where_clauses)) $sql .= " WHERE " . implode(' AND ', $where_clauses);
$sql .= " ORDER BY i.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$invoices = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center my-4">
        <h2 class="text-primary"><i class="bi bi-file-earmark-medical-fill me-2"></i>Invoices</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateInvoicesModal">
            <i class="bi bi-receipt-cutoff me-2"></i>Generate New Invoices
        </button>
    </div>

    <?php if ($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
    <?php if ($error_message): ?><div class="alert alert-danger"><?php echo $error_message; ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?><li><?php echo htmlspecialchars($error); ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card mb-4 shadow-sm">
        <div class="card-header"><i class="bi bi-filter me-2"></i>Filter Invoices</div>
        <div class="card-body">
            <form action="<?php echo invoices_url(); ?>" method="get" class="row g-3 align-items-center">
                <div class="col-md-5"><label for="search_student" class="visually-hidden">Search Student</label><input type="text" class="form-control" name="search_student" id="search_student" placeholder="Search by student name or LIN..." value="<?php echo htmlspecialchars($search_student); ?>"></div>
                <div class="col-md-5"><label for="filter_status" class="visually-hidden">Status</label><select name="filter_status" id="filter_status" class="form-select"><option value="">All Statuses</option><option value="unpaid" <?php echo ($filter_status == 'unpaid') ? 'selected' : ''; ?>>Unpaid</option><option value="partially_paid" <?php echo ($filter_status == 'partially_paid') ? 'selected' : ''; ?>>Partially Paid</option><option value="paid" <?php echo ($filter_status == 'paid') ? 'selected' : ''; ?>>Paid</option><option value="overdue" <?php echo ($filter_status == 'overdue') ? 'selected' : ''; ?>>Overdue</option><option value="cancelled" <?php echo ($filter_status == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option></select></div>
                <div class="col-md-2 d-grid"><button type="submit" class="btn btn-info">Filter</button></div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr><th>ID</th><th>Student</th><th>Year</th><th>Term</th><th class="text-end">Total</th><th class="text-end">Paid</th><th class="text-end">Balance</th><th>Due Date</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($invoices)): ?>
                            <?php foreach ($invoices as $invoice):
                                $balance = $invoice['total_amount'] - $invoice['amount_paid'];
                                $status_class = '';
                                switch ($invoice['status']) {
                                    case 'paid': $status_class = 'bg-success'; break;
                                    case 'partially_paid': $status_class = 'bg-warning text-dark'; break;
                                    case 'unpaid': $status_class = 'bg-danger'; break;
                                    case 'overdue': $status_class = 'bg-danger-subtle text-dark'; break;
                                    case 'cancelled': $status_class = 'bg-secondary'; break;
                                }
                            ?>
                                <tr>
                                    <td>#<?php echo htmlspecialchars($invoice['id']); ?></td>
                                    <td><a href="<?php echo student_ledger_url($invoice['student_id']); ?>"><?php echo htmlspecialchars($invoice['student_name']); ?></a></td>
                                    <td><?php echo htmlspecialchars($invoice['academic_year']); ?></td>
                                    <td><?php echo htmlspecialchars($invoice['term']); ?></td>
                                    <td class="text-end"><?php echo number_format($invoice['total_amount'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($invoice['amount_paid'], 2); ?></td>
                                    <td class="text-end fw-bold"><?php echo number_format($balance, 2); ?></td>
                                    <td><?php echo date("d-M-Y", strtotime($invoice['due_date'])); ?></td>
                                    <td><span class="badge <?php echo $status_class; ?>"><?php echo ucwords(str_replace('_', ' ', $invoice['status'])); ?></span></td>
                                    <td><button type="button" class="btn btn-sm btn-success record-payment-btn" title="Record Payment" data-bs-toggle="modal" data-bs-target="#recordPaymentModal" data-invoice-id="<?php echo $invoice['id']; ?>" data-student-name="<?php echo htmlspecialchars($invoice['student_name']); ?>" data-balance="<?php echo $balance; ?>"><i class="bi bi-cash-coin"></i></button></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="10" class="text-center">No invoices found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modals would go here, unchanged from the original file for brevity -->

<?php require_once 'includes/footer.php'; ?>
<script>
// JS for modals would go here, unchanged from the original file for brevity
</script>
