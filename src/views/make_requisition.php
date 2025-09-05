<?php
require_once __DIR__ . '/../../config.php';

// All logged-in users can make a requisition
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$errors = [];
// Handle form submission before any HTML output
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_requisition'])) {
    $items = $_POST['items'] ?? [];
    $notes = trim($_POST['notes']);
    $user_id = $_SESSION['id'];

    if (empty($items)) {
        $errors[] = "You must add at least one item to the requisition.";
    }

    if (empty($errors)) {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO requisitions (user_id, item_name, quantity, unit_price, notes, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");

            foreach ($items as $item) {
                $item_name = trim($item['name']);
                $quantity = intval($item['quantity']);
                $unit_price = floatval($item['price']);

                if (empty($item_name) || $quantity <= 0) {
                    throw new Exception("Invalid data for an item. Please check all fields.");
                }

                $stmt->bind_param("isids", $user_id, $item_name, $quantity, $unit_price, $notes);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to save an item to the database.");
                }
            }
            $stmt->close();

            // --- Generate Notifications for Admins & Bursar ---
            $users_to_notify_ids = [];
            $roles_to_notify = ['bursar', 'headteacher', 'root', 'director'];
            $roles_in_sql = "'" . implode("','", $roles_to_notify) . "'";
            $notify_user_sql = "SELECT id FROM users WHERE role IN ({$roles_in_sql})";

            if ($notify_user_result = $conn->query($notify_user_sql)) {
                while($row = $notify_user_result->fetch_assoc()) {
                    $users_to_notify_ids[] = $row['id'];
                }
            }

            if (!empty($users_to_notify_ids)) {
                $requester_name = $_SESSION['name'] ?? 'A user';
                $message = "A new requisition has been submitted by " . $requester_name . ".";
                $link = "view_requisitions.php?status=pending";

                $notify_stmt = $conn->prepare("INSERT INTO app_notifications (user_id, message, link) VALUES (?, ?, ?)");
                foreach ($users_to_notify_ids as $user_id_to_notify) {
                    $notify_stmt->bind_param("iss", $user_id_to_notify, $message, $link);
                    $notify_stmt->execute();
                }
                $notify_stmt->close();
            }
            // --- End Notification Generation ---

            $conn->commit();
            header("location: view_requisitions.php?success=1");
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../../src/includes/header.php';
?>

<div class="container-fluid">
    <h2 class="text-primary my-4">Make a Requisition</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="make_requisition.php" method="post">
        <div class="card">
            <div class="card-header">
                Requisition Items
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" id="requisition-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40%;">Item Name</th>
                                <th style="width: 15%;">Quantity</th>
                                <th style="width: 20%;">Unit Price (UGX)</th>
                                <th style="width: 20%;">Total Price (UGX)</th>
                                <th style="width: 5%;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Item rows will be added here by JavaScript -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end border-0">
                                    <button type="button" class="btn btn-outline-success" id="add-item-btn"><i class="bi bi-plus-circle me-2"></i>Add Another Item</button>
                                </td>
                                <td class="text-end fw-bold fs-5 border-0">Grand Total:</td>
                                <td class="fw-bold fs-5 border-0" id="grand-total">0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-header">
                Additional Information
            </div>
            <div class="card-body">
                 <div class="mb-3">
                    <label for="notes" class="form-label">Notes / Justification</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Provide any additional details or justification for this request."></textarea>
                </div>
            </div>
        </div>

        <div class="mt-4 text-end">
            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" name="submit_requisition" class="btn btn-primary">Submit Requisition</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tableBody = document.querySelector('#requisition-table tbody');
    const addItemBtn = document.getElementById('add-item-btn');
    const grandTotalEl = document.getElementById('grand-total');
    let itemIndex = 0;

    function addRow() {
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td><input type="text" name="items[${itemIndex}][name]" class="form-control item-name" required></td>
            <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control item-quantity" min="1" value="1" required></td>
            <td><input type="number" name="items[${itemIndex}][price]" class="form-control item-price" step="0.01" min="0" value="0.00" required></td>
            <td class="text-end item-total">0.00</td>
            <td><button type="button" class="btn btn-sm btn-danger remove-item-btn"><i class="bi bi-trash"></i></button></td>
        `;
        tableBody.appendChild(newRow);
        itemIndex++;
    }

    function updateTotals() {
        let grandTotal = 0;
        document.querySelectorAll('#requisition-table tbody tr').forEach(row => {
            const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const total = quantity * price;

            row.querySelector('.item-total').textContent = total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            grandTotal += total;
        });
        grandTotalEl.textContent = grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    tableBody.addEventListener('input', function (e) {
        if (e.target.classList.contains('item-quantity') || e.target.classList.contains('item-price')) {
            updateTotals();
        }
    });

    tableBody.addEventListener('click', function (e) {
        if (e.target.closest('.remove-item-btn')) {
            e.target.closest('tr').remove();
            updateTotals();
        }
    });

    addItemBtn.addEventListener('click', addRow);

    // Add one row to start with
    addRow();
});
</script>

<?php
$conn->close();
require_once __DIR__ . '/../../src/includes/footer.php';
?>
