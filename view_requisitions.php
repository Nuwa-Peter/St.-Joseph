<?php
session_start();
require_once 'config.php';

// All logged-in users can view requisitions
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'includes/header.php';

$requisitions = [];
$errors = [];
$user_id = $_SESSION['id'];
$user_role = $_SESSION['role'];
$admin_roles = ['bursar', 'headteacher', 'root'];
$is_admin = in_array($user_role, $admin_roles);
$success_message = '';

// Handle Admin Actions
if ($is_admin && $_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_status'])) {
        $requisition_id = intval($_POST['requisition_id']);
        $new_status = $_POST['update_status'];
        $rejection_reason = trim($_POST['rejection_reason']);
        $allowed_statuses = ['approved', 'rejected'];

        if (in_array($new_status, $allowed_statuses)) {
            $sql = "UPDATE requisitions SET status = ?, approved_by = ?, approved_at = NOW()";
            $types = "sii";
            $params = [$new_status, $user_id];

            if ($new_status === 'rejected' && !empty($rejection_reason)) {
                $sql .= ", rejection_reason = ?";
                $types .= "s";
                $params[] = $rejection_reason;
            }
            $sql .= " WHERE id = ?";
            $params[] = $requisition_id;

            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                $success_message = "Requisition #" . $requisition_id . " has been " . $new_status . ".";

                // --- Send notification to the user who made the request ---
                $req_info_sql = "SELECT user_id, item_name FROM requisitions WHERE id = ?";
                $req_stmt = $conn->prepare($req_info_sql);
                $req_stmt->bind_param("i", $requisition_id);
                $req_stmt->execute();
                $req_result = $req_stmt->get_result();
                if($req_info = $req_result->fetch_assoc()) {
                    $requester_id = $req_info['user_id'];
                    $item_name = $req_info['item_name'];

                    $message = "Your requisition for '" . $item_name . "' has been " . $new_status . ".";
                    if ($new_status === 'rejected' && !empty($rejection_reason)) {
                        $message .= " Reason: " . $rejection_reason;
                    }
                    $link = "view_requisitions.php";
                    $notify_sql = "INSERT INTO app_notifications (user_id, message, link) VALUES (?, ?, ?)";
                    $notify_stmt = $conn->prepare($notify_sql);
                    $notify_stmt->bind_param("iss", $requester_id, $message, $link);
                    $notify_stmt->execute();
                    $notify_stmt->close();
                }
                $req_stmt->close();
                // --- End notification ---

            } else {
                $errors[] = "Failed to update requisition status.";
            }
            $stmt->close();
        } else {
            $errors[] = "Invalid status update.";
        }
    }

    if (isset($_POST['edit_requisition'])) {
        $req_id = intval($_POST['requisition_id']);
        $item_name = trim($_POST['item_name']);
        $quantity = intval($_POST['quantity']);
        $unit_price = floatval($_POST['unit_price']);

        if (empty($item_name) || $quantity <= 0 || $unit_price < 0) {
            $errors[] = "Invalid data provided for editing.";
        } else {
            $stmt = $conn->prepare("UPDATE requisitions SET item_name = ?, quantity = ?, unit_price = ? WHERE id = ?");
            $stmt->bind_param("sidi", $item_name, $quantity, $unit_price, $req_id);
            if ($stmt->execute()) {
                $success_message = "Requisition #" . $req_id . " has been updated.";
            } else {
                $errors[] = "Failed to update requisition.";
            }
            $stmt->close();
        }
    }

    if (isset($_POST['delete_requisition'])) {
        $req_id = intval($_POST['requisition_id']);
        $stmt = $conn->prepare("DELETE FROM requisitions WHERE id = ?");
        $stmt->bind_param("i", $req_id);
        if ($stmt->execute()) {
            $success_message = "Requisition #" . $req_id . " has been deleted.";
        } else {
            $errors[] = "Failed to delete requisition.";
        }
        $stmt->close();
    }
}


// --- Data Fetching ---
$sql = "";
$params = [];
$types = '';

if ($is_admin) {
    // Admins can see all requisitions and filter by status
    $sql = "SELECT r.id, r.item_name, r.quantity, r.total_price, r.status, r.created_at,
                   CONCAT(u.first_name, ' ', u.last_name) as requester_name
            FROM requisitions r
            JOIN users u ON r.user_id = u.id";

    $filter_status = $_GET['status'] ?? '';
    if (!empty($filter_status)) {
        $sql .= " WHERE r.status = ?";
        $params[] = $filter_status;
        $types .= 's';
    }
    $sql .= " ORDER BY r.created_at DESC";

} else {
    // Regular users see only their own requisitions
    $sql = "SELECT id, item_name, quantity, total_price, status, created_at
            FROM requisitions
            WHERE user_id = ?
            ORDER BY created_at DESC";
    $params[] = $user_id;
    $types .= 'i';
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$requisitions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center my-4">
        <h2 class="text-primary">View Requisitions</h2>
        <div>
            <a href="export_requisitions_pdf.php" class="btn btn-pdf" target="_blank">
                <i class="bi bi-file-earmark-pdf me-2"></i>Export All as PDF
            </a>
            <a href="make_requisition.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Make a New Requisition
            </a>
        </div>
    </div>

    <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
        <div class="alert alert-success">Your requisition has been submitted successfully.</div>
    <?php elseif (!empty($success_message)): ?>
         <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
     <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($is_admin): ?>
    <!-- Admin Filter Bar -->
    <div class="card mb-4">
        <div class="card-header">Filter Requisitions</div>
        <div class="card-body">
            <form action="view_requisitions.php" method="get">
                <div class="row">
                    <div class="col-md-4">
                        <label for="status" class="form-label">Filter by Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending" <?php echo ($filter_status == 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo ($filter_status == 'approved') ? 'selected' : ''; ?>>Approved</option>
                            <option value="rejected" <?php echo ($filter_status == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                            <option value="fulfilled" <?php echo ($filter_status == 'fulfilled') ? 'selected' : ''; ?>>Fulfilled</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-info">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <?php echo $is_admin ? 'All Requisitions' : 'My Requisitions'; ?>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <?php if ($is_admin): ?><th>Requested By</th><?php endif; ?>
                            <th>Date & Time</th>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th class="text-end">Total Price (UGX)</th>
                            <th>Status</th>
                            <?php if ($is_admin): ?><th>Actions</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($requisitions)): ?>
                            <?php foreach ($requisitions as $req):
                                $status_class = '';
                                switch ($req['status']) {
                                    case 'approved': $status_class = 'bg-success'; break;
                                    case 'pending': $status_class = 'bg-warning text-dark'; break;
                                    case 'rejected': $status_class = 'bg-danger'; break;
                                    case 'fulfilled': $status_class = 'bg-info text-dark'; break;
                                }
                            ?>
                            <tr>
                                <?php if ($is_admin): ?><td><?php echo htmlspecialchars($req['requester_name']); ?></td><?php endif; ?>
                                <td><?php echo date("d-M-Y H:i:s", strtotime($req['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($req['item_name']); ?></td>
                                <td><?php echo $req['quantity']; ?></td>
                                <td class="text-end"><?php echo number_format($req['total_price'], 2); ?></td>
                                <td><span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($req['status']); ?></span></td>
                                <?php if ($is_admin): ?>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info action-btn" title="View Details & Action"
                                                data-bs-toggle="modal"
                                                data-bs-target="#actionRequisitionModal"
                                                data-id="<?php echo $req['id']; ?>"
                                                data-requester="<?php echo htmlspecialchars($req['requester_name']); ?>"
                                                data-item="<?php echo htmlspecialchars($req['item_name']); ?>"
                                                data-quantity="<?php echo $req['quantity']; ?>"
                                                data-price="<?php echo number_format($req['total_price'], 2); ?>"
                                                data-status="<?php echo $req['status']; ?>">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <a href="export_requisitions_pdf.php?id=<?php echo $req['id']; ?>" class="btn btn-sm btn-pdf" target="_blank" title="Export to PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                                        <button type="button" class="btn btn-sm btn-warning edit-btn" title="Edit"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editRequisitionModal"
                                                data-id="<?php echo $req['id']; ?>"
                                                data-item="<?php echo htmlspecialchars($req['item_name']); ?>"
                                                data-quantity="<?php echo $req['quantity']; ?>"
                                                data-unitprice="<?php echo $req['total_price'] / $req['quantity']; ?>">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <form action="view_requisitions.php" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this requisition?');">
                                            <input type="hidden" name="requisition_id" value="<?php echo $req['id']; ?>">
                                            <button type="submit" name="delete_requisition" class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?php echo $is_admin ? '7' : '5'; ?>" class="text-center">No requisitions found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Action Requisition Modal -->
<div class="modal fade" id="actionRequisitionModal" tabindex="-1" aria-labelledby="actionRequisitionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="view_requisitions.php" method="post">
        <input type="hidden" name="requisition_id" id="modal_requisition_id">
        <div class="modal-header">
          <h5 class="modal-title" id="actionRequisitionModalLabel">Requisition Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <p><strong>Requester:</strong> <span id="modal_requester"></span></p>
            <p><strong>Item:</strong> <span id="modal_item"></span></p>
            <p><strong>Quantity:</strong> <span id="modal_quantity"></span></p>
            <p><strong>Total Price:</strong> UGX <span id="modal_price"></span></p>
            <p><strong>Status:</strong> <span id="modal_status" class="badge"></span></p>
            <hr>
            <div class="mb-3">
                <label for="rejection_reason" class="form-label">Reason for Rejection (if rejecting):</label>
                <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="3"></textarea>
            </div>
        </div>
        <div class="modal-footer justify-content-between">
            <div>
                <button type="submit" name="update_status" value="approved" class="btn btn-success" id="modal_approve_btn">Approve</button>
                <button type="submit" name="update_status" value="rejected" class="btn btn-danger" id="modal_reject_btn">Reject</button>
            </div>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Requisition Modal -->
<div class="modal fade" id="editRequisitionModal" tabindex="-1" aria-labelledby="editRequisitionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="view_requisitions.php" method="post">
        <input type="hidden" name="requisition_id" id="edit_requisition_id">
        <div class="modal-header">
          <h5 class="modal-title" id="editRequisitionModalLabel">Edit Requisition</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label for="edit_item_name" class="form-label">Item Name</label>
                <input type="text" class="form-control" id="edit_item_name" name="item_name" required>
            </div>
             <div class="mb-3">
                <label for="edit_quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" id="edit_quantity" name="quantity" min="1" required>
            </div>
             <div class="mb-3">
                <label for="edit_unit_price" class="form-label">Unit Price</label>
                <input type="number" class="form-control" id="edit_unit_price" name="unit_price" step="0.01" min="0" required>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" name="edit_requisition" class="btn btn-primary">Save Changes</button>
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
document.addEventListener('DOMContentLoaded', function() {
    // Logic for Action Modal
    const actionModal = document.getElementById('actionRequisitionModal');
    if(actionModal) {
        actionModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const reqId = button.getAttribute('data-id');
            const requester = button.getAttribute('data-requester');
            const item = button.getAttribute('data-item');
            const quantity = button.getAttribute('data-quantity');
            const price = button.getAttribute('data-price');
            const status = button.getAttribute('data-status');

            const modal = this;
            modal.querySelector('#modal_requisition_id').value = reqId;
            modal.querySelector('#modal_requester').textContent = requester;
            modal.querySelector('#modal_item').textContent = item;
            modal.querySelector('#modal_quantity').textContent = quantity;
            modal.querySelector('#modal_price').textContent = price;

            const statusBadge = modal.querySelector('#modal_status');
            statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
            statusBadge.className = 'badge '; // reset classes
            switch (status) {
                case 'approved': statusBadge.classList.add('bg-success'); break;
                case 'pending': statusBadge.classList.add('bg-warning', 'text-dark'); break;
                case 'rejected': statusBadge.classList.add('bg-danger'); break;
                case 'fulfilled': statusBadge.classList.add('bg-info', 'text-dark'); break;
            }

            const approveBtn = modal.querySelector('#modal_approve_btn');
            const rejectBtn = modal.querySelector('#modal_reject_btn');
            if (status === 'pending') {
                approveBtn.style.display = 'inline-block';
                rejectBtn.style.display = 'inline-block';
            } else {
                approveBtn.style.display = 'none';
                rejectBtn.style.display = 'none';
            }
        });
    }

    // Logic for Edit Modal
    const editModal = document.getElementById('editRequisitionModal');
    if(editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const reqId = button.getAttribute('data-id');
            const item = button.getAttribute('data-item');
            const quantity = button.getAttribute('data-quantity');
            const unitPrice = button.getAttribute('data-unitprice');

            const modal = this;
            modal.querySelector('#edit_requisition_id').value = reqId;
            modal.querySelector('#edit_item_name').value = item;
            modal.querySelector('#edit_quantity').value = quantity;
            modal.querySelector('#edit_unit_price').value = unitPrice;
        });
    }
});
</script>
