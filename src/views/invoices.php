<?php
require_once __DIR__ . '/../../config.php';

// Role-based access control
$authorized_roles = ['bursar', 'headteacher', 'root'];
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], $authorized_roles)) {
    header("location: dashboard.php?unauthorized=true");
    exit;
}

$errors = [];
$invoices = [];
$success_message = '';

// --- Handle Invoice Generation ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generate_invoices'])) {
    $fee_structure_id = intval($_POST['fee_structure_id']);
    $class_level_id = intval($_POST['class_level_id']);
    $term = trim($_POST['term']);
    $due_date = trim($_POST['due_date']);

    // --- Validation ---
    if (empty($fee_structure_id)) $errors[] = "Fee Structure is required.";
    if (empty($class_level_id)) $errors[] = "Class is required.";
    if (empty($term)) $errors[] = "Term is required.";
    if (empty($due_date)) $errors[] = "Due date is required.";

    if (empty($errors)) {
        $conn->begin_transaction();
        try {
            // 1. Get total amount from fee structure items
            $total_amount = 0;
            $stmt_items = $conn->prepare("SELECT SUM(amount) as total FROM fee_items WHERE fee_structure_id = ?");
            $stmt_items->bind_param("i", $fee_structure_id);
            $stmt_items->execute();
            $result_items = $stmt_items->get_result();
            if ($result_items->num_rows > 0) {
                $total_amount = $result_items->fetch_assoc()['total'];
            }
            $stmt_items->close();

            if ($total_amount <= 0) {
                throw new Exception("Selected fee structure has no items or total amount is zero.");
            }

            // 2. Get academic year from fee structure
            $academic_year = '';
            $stmt_struct = $conn->prepare("SELECT academic_year FROM fee_structures WHERE id = ?");
            $stmt_struct->bind_param("i", $fee_structure_id);
            $stmt_struct->execute();
            $result_struct = $stmt_struct->get_result();
            if ($result_struct->num_rows > 0) {
                $academic_year = $result_struct->fetch_assoc()['academic_year'];
            }
            $stmt_struct->close();


            // 3. Get all students in the selected class level (via streams)
            $student_ids = [];
            $sql_students = "SELECT su.user_id FROM stream_user su JOIN streams s ON su.stream_id = s.id WHERE s.class_level_id = ?";
            $stmt_students = $conn->prepare($sql_students);
            $stmt_students->bind_param("i", $class_level_id);
            $stmt_students->execute();
            $result_students = $stmt_students->get_result();
            while($row = $result_students->fetch_assoc()) {
                $student_ids[] = $row['user_id'];
            }
            $stmt_students->close();

            if (empty($student_ids)) {
                throw new Exception("No students found in the selected class.");
            }

            // 4. Create an invoice for each student
            $sql_insert_invoice = "INSERT INTO invoices (student_id, fee_structure_id, academic_year, term, total_amount, due_date, status) VALUES (?, ?, ?, ?, ?, ?, 'unpaid')";
            $stmt_insert = $conn->prepare($sql_insert_invoice);

            foreach ($student_ids as $student_id) {
                $stmt_insert->bind_param("iissds", $student_id, $fee_structure_id, $academic_year, $term, $total_amount, $due_date);
                if (!$stmt_insert->execute()) {
                    throw new Exception("Failed to create invoice for student ID: $student_id. " . $stmt_insert->error);
                }
            }
            $stmt_insert->close();

            $conn->commit();
            $success_message = "Successfully generated " . count($student_ids) . " invoices for the selected class.";

        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "An error occurred during invoice generation: " . $e->getMessage();
        }
    }
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
        $errors[] = "Invalid payment data provided. Please fill all required fields.";
    } else {
        $conn->begin_transaction();
        try {
            // 1. Fetch current invoice details to prevent overpayment and get total amount
            $stmt_invoice = $conn->prepare("SELECT total_amount, amount_paid FROM invoices WHERE id = ?");
            $stmt_invoice->bind_param("i", $invoice_id);
            $stmt_invoice->execute();
            $invoice_details = $stmt_invoice->get_result()->fetch_assoc();
            $stmt_invoice->close();

            if (!$invoice_details) {
                throw new Exception("Invoice not found.");
            }

            $balance = $invoice_details['total_amount'] - $invoice_details['amount_paid'];
            if ($amount_paid_now > $balance) {
                throw new Exception("Payment amount cannot be greater than the outstanding balance of " . number_format($balance, 2));
            }

            // 2. Insert into payments table
            $stmt_pay = $conn->prepare("INSERT INTO payments (invoice_id, amount, payment_date, payment_method, notes, recorded_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_pay->bind_param("idsssi", $invoice_id, $amount_paid_now, $payment_date, $payment_method, $notes, $recorded_by);
            if (!$stmt_pay->execute()) {
                throw new Exception("Failed to record payment transaction: " . $stmt_pay->error);
            }
            $stmt_pay->close();

            // 3. Update amount_paid on the invoice
            $stmt_update = $conn->prepare("UPDATE invoices SET amount_paid = amount_paid + ? WHERE id = ?");
            $stmt_update->bind_param("di", $amount_paid_now, $invoice_id);
            if (!$stmt_update->execute()) {
                throw new Exception("Failed to update invoice amount: " . $stmt_update->error);
            }
            $stmt_update->close();

            // 4. Update invoice status based on the new balance
            $new_balance = $balance - $amount_paid_now;
            $new_status = ($new_balance <= 0) ? 'paid' : 'partially_paid';
            $stmt_status = $conn->prepare("UPDATE invoices SET status = ? WHERE id = ?");
            $stmt_status->bind_param("si", $new_status, $invoice_id);
            if (!$stmt_status->execute()) {
                throw new Exception("Failed to update invoice status: " . $stmt_status->error);
            }
            $stmt_status->close();

            $conn->commit();
            $success_message = "Payment of " . number_format($amount_paid_now, 2) . " recorded successfully for Invoice #{$invoice_id}.";

        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "An error occurred while recording the payment: " . $e->getMessage();
        }
    }
}


// --- Data Fetching ---
// Fetch data for modal dropdowns
$structures_for_modal = $conn->query("SELECT id, name, academic_year FROM fee_structures ORDER BY academic_year DESC, name ASC")->fetch_all(MYSQLI_ASSOC);
$classes_for_modal = $conn->query("SELECT id, name FROM class_levels ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

// Base query
$sql = "SELECT
            i.id,
            i.academic_year,
            i.term,
            i.total_amount,
            i.amount_paid,
            i.due_date,
            i.status,
            CONCAT(u.first_name, ' ', u.last_name) AS student_name,
            u.id as student_id
        FROM
            invoices AS i
        JOIN
            users AS u ON i.student_id = u.id";

$where_clauses = [];
$params = [];
$types = '';

// Filtering logic
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

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}

$sql .= " ORDER BY i.created_at DESC";

// Prepare and execute statement
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$invoices = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();


require_once __DIR__ . '/../../src/includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary">Invoices</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateInvoicesModal">
            <i class="bi bi-receipt-cutoff me-2"></i>Generate New Invoices
        </button>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-filter me-2"></i>Filter Invoices
        </div>
        <div class="card-body">
            <form action="invoices.php" method="get" class="row g-3 align-items-center">
                <div class="col-md-5">
                    <label for="search_student" class="visually-hidden">Search Student</label>
                    <input type="text" class="form-control" name="search_student" id="search_student" placeholder="Search by student name or LIN..." value="<?php echo htmlspecialchars($search_student); ?>">
                </div>
                <div class="col-md-5">
                    <label for="filter_status" class="visually-hidden">Status</label>
                    <select name="filter_status" id="filter_status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="unpaid" <?php echo ($filter_status == 'unpaid') ? 'selected' : ''; ?>>Unpaid</option>
                        <option value="partially_paid" <?php echo ($filter_status == 'partially_paid') ? 'selected' : ''; ?>>Partially Paid</option>
                        <option value="paid" <?php echo ($filter_status == 'paid') ? 'selected' : ''; ?>>Paid</option>
                        <option value="overdue" <?php echo ($filter_status == 'overdue') ? 'selected' : ''; ?>>Overdue</option>
                        <option value="cancelled" <?php echo ($filter_status == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-info">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Invoice ID</th>
                            <th>Student</th>
                            <th>Academic Year</th>
                            <th>Term</th>
                            <th class="text-end">Total Amount</th>
                            <th class="text-end">Amount Paid</th>
                            <th class="text-end">Balance</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
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
                                    <td><a href="student_ledger.php?student_id=<?php echo $invoice['student_id']; ?>"><?php echo htmlspecialchars($invoice['student_name']); ?></a></td>
                                    <td><?php echo htmlspecialchars($invoice['academic_year']); ?></td>
                                    <td><?php echo htmlspecialchars($invoice['term']); ?></td>
                                    <td class="text-end"><?php echo number_format($invoice['total_amount'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($invoice['amount_paid'], 2); ?></td>
                                    <td class="text-end fw-bold"><?php echo number_format($balance, 2); ?></td>
                                    <td><?php echo date("d-M-Y", strtotime($invoice['due_date'])); ?></td>
                                    <td><span class="badge <?php echo $status_class; ?>"><?php echo ucwords(str_replace('_', ' ', $invoice['status'])); ?></span></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-success record-payment-btn"
                                                title="Record Payment"
                                                data-bs-toggle="modal"
                                                data-bs-target="#recordPaymentModal"
                                                data-invoice-id="<?php echo $invoice['id']; ?>"
                                                data-student-name="<?php echo htmlspecialchars($invoice['student_name']); ?>"
                                                data-balance="<?php echo $balance; ?>">
                                            <i class="bi bi-cash-coin"></i>
                                        </button>
                                        <button class="btn btn-sm btn-info" title="View Details"><i class="bi bi-eye"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center">No invoices found matching your criteria.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Generate Invoices Modal -->
<div class="modal fade" id="generateInvoicesModal" tabindex="-1" aria-labelledby="generateInvoicesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="invoices.php" method="post">
        <div class="modal-header">
          <h5 class="modal-title" id="generateInvoicesModalLabel">Generate New Invoices</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted">This tool will generate an individual invoice for every student in the selected class, based on the chosen fee structure.</p>

          <div class="mb-3">
            <label for="fee_structure_id" class="form-label">Fee Structure <span class="text-danger">*</span></label>
            <select class="form-select" id="fee_structure_id" name="fee_structure_id" required>
              <option value="">Select a Fee Structure...</option>
              <?php foreach ($structures_for_modal as $structure): ?>
                <option value="<?php echo $structure['id']; ?>"><?php echo htmlspecialchars($structure['name'] . ' (' . $structure['academic_year'] . ')'); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label for="class_level_id" class="form-label">For Class <span class="text-danger">*</span></label>
            <select class="form-select" id="class_level_id" name="class_level_id" required>
                <option value="">Select a Class...</option>
                <?php foreach ($classes_for_modal as $class): ?>
                    <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
                <?php endforeach; ?>
            </select>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="term" class="form-label">Term <span class="text-danger">*</span></label>
              <select class="form-select" id="term" name="term" required>
                <option value="">Select a Term...</option>
                <option value="Term 1">Term 1</option>
                <option value="Term 2">Term 2</option>
                <option value="Term 3">Term 3</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="due_date" name="due_date" required>
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="generate_invoices" class="btn btn-primary">Generate Invoices</button>
        </div>
      </form>
    </div>
  </div>
</div>


<?php
$conn->close();
require_once __DIR__ . '/../../src/includes/footer.php';
?>
