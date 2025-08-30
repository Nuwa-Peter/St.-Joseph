<?php
session_start();
require_once 'config.php';

// Role-based access control
$authorized_roles = ['bursar', 'headteacher', 'root'];
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], $authorized_roles)) {
    header("location: dashboard.php?unauthorized=true");
    exit;
}

// --- Validation and Data Fetching ---
if (!isset($_GET['structure_id']) || !is_numeric($_GET['structure_id'])) {
    header("location: fee_structures.php?error=invalid_id");
    exit;
}
$structure_id = intval($_GET['structure_id']);

// Fetch the fee structure details
$stmt = $conn->prepare("SELECT name, academic_year FROM fee_structures WHERE id = ?");
$stmt->bind_param("i", $structure_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header("location: fee_structures.php?error=not_found");
    exit;
}
$structure = $result->fetch_assoc();
$stmt->close();


$errors = [];
$success_message = '';

// --- Handle POST requests for adding/deleting fee items ---

// Add new fee item
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_fee_item'])) {
    $item_name = trim($_POST['item_name']);
    $item_amount = trim($_POST['item_amount']);

    if (empty($item_name)) $errors[] = "Item name is required.";
    if (empty($item_amount) || !is_numeric($item_amount) || $item_amount < 0) {
        $errors[] = "A valid, non-negative amount is required.";
    }

    if (empty($errors)) {
        $stmt_insert = $conn->prepare("INSERT INTO fee_items (fee_structure_id, name, amount) VALUES (?, ?, ?)");
        $stmt_insert->bind_param("isd", $structure_id, $item_name, $item_amount);
        if ($stmt_insert->execute()) {
            header("location: fee_items.php?structure_id=$structure_id&success=item_added");
            exit;
        } else {
            $errors[] = "Database Error: Could not add fee item.";
        }
        $stmt_insert->close();
    }
}

// Delete fee item
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_fee_item'])) {
    $item_id_to_delete = intval($_POST['item_id']);
    $stmt_delete = $conn->prepare("DELETE FROM fee_items WHERE id = ? AND fee_structure_id = ?");
    $stmt_delete->bind_param("ii", $item_id_to_delete, $structure_id);
    if ($stmt_delete->execute()) {
        header("location: fee_items.php?structure_id=$structure_id&success=item_deleted");
        exit;
    } else {
        $errors[] = "Database Error: Could not delete fee item.";
    }
    $stmt_delete->close();
}


// Fetch all fee items for the given structure
$fee_items = [];
$stmt_items = $conn->prepare("SELECT id, name, amount FROM fee_items WHERE fee_structure_id = ? ORDER BY name ASC");
$stmt_items->bind_param("i", $structure_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
$fee_items = $result_items->fetch_all(MYSQLI_ASSOC);
$stmt_items->close();

// Handle success messages
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'item_added') $success_message = "Fee item added successfully.";
    if ($_GET['success'] == 'item_deleted') $success_message = "Fee item deleted successfully.";
}

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="fee_structures.php" class="btn btn-outline-primary mb-3">
                <i class="bi bi-arrow-left"></i> Back to Fee Structures
            </a>
            <h2 class="text-primary d-inline-block ms-2">Manage Fee Items</h2>
        </div>
    </div>

    <h4 class="text-secondary">For: <?php echo htmlspecialchars($structure['name']); ?> (<?php echo htmlspecialchars($structure['academic_year']); ?>)</h4>
    <hr>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Column for listing items -->
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-card-list me-2"></i>Fee Items in this Structure
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Item Name</th>
                                    <th>Amount</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($fee_items)): ?>
                                    <?php
                                    $total_amount = 0;
                                    foreach ($fee_items as $item):
                                        $total_amount += $item['amount'];
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                                            <td><?php echo number_format($item['amount'], 2); ?></td>
                                            <td class="text-end">
                                                <form action="fee_items.php?structure_id=<?php echo $structure_id; ?>" method="post" onsubmit="return confirm('Are you sure you want to delete this item?');" class="d-inline">
                                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                                    <button type="submit" name="delete_fee_item" class="btn btn-sm btn-danger" title="Delete Item"><i class="bi bi-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr class="table-secondary">
                                        <td class="text-end"><strong>Total:</strong></td>
                                        <td colspan="2"><strong><?php echo number_format($total_amount, 2); ?></strong></td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center">No fee items have been added to this structure yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- Column for adding a new item -->
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-plus-circle me-2"></i>Add New Fee Item
                </div>
                <div class="card-body">
                    <form action="fee_items.php?structure_id=<?php echo $structure_id; ?>" method="post">
                        <div class="mb-3">
                            <label for="item_name" class="form-label">Item Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="item_name" name="item_name" placeholder="e.g., Tuition Fee" required>
                        </div>
                        <div class="mb-3">
                            <label for="item_amount" class="form-label">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">UGX</span>
                                <input type="number" class="form-control" id="item_amount" name="item_amount" step="0.01" min="0" placeholder="e.g., 500000" required>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="add_fee_item" class="btn btn-primary">Add Item to Structure</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
