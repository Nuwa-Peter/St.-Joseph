<?php
require_once 'config.php';

// Role-based access control
$authorized_roles = ['bursar', 'headteacher', 'root'];
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], $authorized_roles)) {
    header("location: dashboard.php?unauthorized=true");
    exit;
}

require_once 'includes/header.php';

$errors = [];
$expenses = [];
$success_message = '';

// Handle Add Expense form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_expense'])) {
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $amount = trim($_POST['amount']);
    $expense_date = trim($_POST['expense_date']);
    $recorded_by = $_SESSION['id'];

    if (empty($category)) $errors[] = "Category is required.";
    if (empty($description)) $errors[] = "Description is required.";
    if (empty($amount) || !is_numeric($amount) || $amount <= 0) $errors[] = "A valid positive amount is required.";
    if (empty($expense_date)) $errors[] = "Expense date is required.";

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO expenses (category, description, amount, expense_date, recorded_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsi", $category, $description, $amount, $expense_date, $recorded_by);

        if ($stmt->execute()) {
            header("Location: expenses.php?success=1");
            exit();
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}

if (isset($_GET['success'])) {
    if ($_GET['success'] == '1') $success_message = "Expense added successfully!";
    if ($_GET['success'] == '2') $success_message = "Expense updated successfully!";
    if ($_GET['success'] == '3') $success_message = "Expense deleted successfully!";
}

// Handle Delete Expense form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_expense'])) {
    $id = intval($_POST['expense_id']);

    if (empty($id)) {
        $errors[] = "Expense ID is missing.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("DELETE FROM expenses WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            header("Location: expenses.php?success=3");
            exit();
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Handle Edit Expense form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_expense'])) {
    $id = intval($_POST['expense_id']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $amount = trim($_POST['amount']);
    $expense_date = trim($_POST['expense_date']);

    if (empty($id)) $errors[] = "Expense ID is missing.";
    if (empty($category)) $errors[] = "Category is required.";
    if (empty($description)) $errors[] = "Description is required.";
    if (empty($amount) || !is_numeric($amount) || $amount <= 0) $errors[] = "A valid positive amount is required.";
    if (empty($expense_date)) $errors[] = "Expense date is required.";

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE expenses SET category = ?, description = ?, amount = ?, expense_date = ? WHERE id = ?");
        $stmt->bind_param("ssdsi", $category, $description, $amount, $expense_date, $id);

        if ($stmt->execute()) {
            header("Location: expenses.php?success=2");
            exit();
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}


// Fetch all expenses
$sql = "SELECT
            e.id,
            e.category,
            e.description,
            e.amount,
            e.expense_date,
            CONCAT(u.first_name, ' ', u.last_name) as recorded_by_name
        FROM
            expenses e
        JOIN
            users u ON e.recorded_by = u.id
        ORDER BY
            e.expense_date DESC, e.id DESC";

if ($result = $conn->query($sql)) {
    $expenses = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $errors[] = "Failed to retrieve expenses: " . $conn->error;
}

?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center my-4">
        <h2 class="text-primary">Manage Expenses</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
            <i class="bi bi-plus-circle me-2"></i>Add New Expense
        </button>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <i class="bi bi-cash me-2"></i>Recorded Expenses
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th class="text-end">Amount (UGX)</th>
                            <th>Recorded By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($expenses)): ?>
                            <?php foreach ($expenses as $expense): ?>
                                <tr>
                                    <td><?php echo date("d-M-Y", strtotime($expense['expense_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($expense['category']); ?></td>
                                    <td><?php echo htmlspecialchars($expense['description']); ?></td>
                                    <td class="text-end"><?php echo number_format($expense['amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($expense['recorded_by_name']); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning edit-expense-btn" title="Edit Expense"
                                                data-bs-toggle="modal" data-bs-target="#editExpenseModal"
                                                data-id="<?php echo $expense['id']; ?>"
                                                data-date="<?php echo $expense['expense_date']; ?>"
                                                data-category="<?php echo htmlspecialchars($expense['category']); ?>"
                                                data-amount="<?php echo $expense['amount']; ?>"
                                                data-description="<?php echo htmlspecialchars($expense['description']); ?>">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <form action="expenses.php" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this expense record? This action cannot be undone.');">
                                            <input type="hidden" name="expense_id" value="<?php echo $expense['id']; ?>">
                                            <button type="submit" name="delete_expense" class="btn btn-sm btn-danger" title="Delete Expense">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No expenses have been recorded yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="expenses.php" method="post">
        <div class="modal-header">
          <h5 class="modal-title" id="addExpenseModalLabel">Add New Expense</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="expense_date" class="form-label">Expense Date <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="expense_date" name="expense_date" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
              <div class="input-group">
                  <span class="input-group-text">UGX</span>
                  <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" required>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="category" name="category" placeholder="e.g., Office Supplies, Utilities, Salaries" required>
          </div>
          <div class="mb-3">
            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" name="add_expense" class="btn btn-primary">Save Expense</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Expense Modal -->
<div class="modal fade" id="editExpenseModal" tabindex="-1" aria-labelledby="editExpenseModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="expenses.php" method="post">
        <input type="hidden" name="expense_id" id="edit_expense_id">
        <div class="modal-header">
          <h5 class="modal-title" id="editExpenseModalLabel">Edit Expense</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="edit_expense_date" class="form-label">Expense Date <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="edit_expense_date" name="expense_date" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="edit_amount" class="form-label">Amount <span class="text-danger">*</span></label>
              <div class="input-group">
                  <span class="input-group-text">UGX</span>
                  <input type="number" class="form-control" id="edit_amount" name="amount" step="0.01" min="0.01" required>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label for="edit_category" class="form-label">Category <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit_category" name="category" required>
          </div>
          <div class="mb-3">
            <label for="edit_description" class="form-label">Description <span class="text-danger">*</span></label>
            <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" name="edit_expense" class="btn btn-primary">Update Expense</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var editExpenseModal = document.getElementById('editExpenseModal');
  editExpenseModal.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;

    // Extract info from data-* attributes
    var expenseId = button.getAttribute('data-id');
    var date = button.getAttribute('data-date');
    var category = button.getAttribute('data-category');
    var amount = button.getAttribute('data-amount');
    var description = button.getAttribute('data-description');

    // Update the modal's content
    var modalTitle = editExpenseModal.querySelector('.modal-title');
    var idInput = editExpenseModal.querySelector('#edit_expense_id');
    var dateInput = editExpenseModal.querySelector('#edit_expense_date');
    var categoryInput = editExpenseModal.querySelector('#edit_category');
    var amountInput = editExpenseModal.querySelector('#edit_amount');
    var descriptionInput = editExpenseModal.querySelector('#edit_description');

    modalTitle.textContent = 'Edit Expense #' + expenseId;
    idInput.value = expenseId;
    dateInput.value = date;
    categoryInput.value = category;
    amountInput.value = amount;
    descriptionInput.value = description;
  });
});
</script>
