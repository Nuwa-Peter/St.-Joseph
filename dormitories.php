<?php
require_once 'config.php';

// Access control for admins
$admin_roles = ['root', 'headteacher', 'admin'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $admin_roles)) {
    header("location: " . dashboard_url());
    exit;
}

// Handle Add Dormitory
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_dorm'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $capacity = trim($_POST['capacity']);
    if (!empty($name)) {
        $sql = "INSERT INTO dormitories (name, description, capacity, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssi", $name, $description, $capacity);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Dormitory added successfully.";
            } else {
                $_SESSION['error_message'] = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $_SESSION['error_message'] = "Dormitory name is required.";
    }
    header("Location: " . dormitories_url());
    exit();
}

// Handle Edit Dormitory
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_dorm'])) {
    $id = trim($_POST['dorm_id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $capacity = trim($_POST['capacity']);
    if (!empty($id) && !empty($name)) {
        $sql = "UPDATE dormitories SET name = ?, description = ?, capacity = ?, updated_at = NOW() WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssii", $name, $description, $capacity, $id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Dormitory updated successfully.";
            } else {
                $_SESSION['error_message'] = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $_SESSION['error_message'] = "ID and name are required.";
    }
    header("Location: " . dormitories_url());
    exit();
}

// Handle Delete Dormitory
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_dorm'])) {
    $id = trim($_POST['dorm_id']);
    if (!empty($id)) {
        $sql_delete_rooms = "DELETE FROM dormitory_rooms WHERE dormitory_id = ?";
        if($stmt_rooms = $conn->prepare($sql_delete_rooms)){
            $stmt_rooms->bind_param("i", $id);
            $stmt_rooms->execute();
            $stmt_rooms->close();
        }

        $sql = "DELETE FROM dormitories WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Dormitory and all its rooms deleted successfully.";
            } else {
                $_SESSION['error_message'] = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $_SESSION['error_message'] = "ID is required.";
    }
    header("Location: " . dormitories_url());
    exit();
}

// Fetch session messages
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Fetch all dormitories
$dorms = [];
$sql_dorms = "SELECT id, name, description, capacity FROM dormitories ORDER BY name";
$result_dorms = $conn->query($sql_dorms);
if ($result_dorms) {
    $dorms = $result_dorms->fetch_all(MYSQLI_ASSOC);
}

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-house-door-fill me-2"></i>Dormitories</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDormModal"><i class="bi bi-plus-circle-fill me-2"></i>Add New Dormitory</button>
    </div>

    <?php if(!empty($success_message)): ?><div class="alert alert-success alert-dismissible fade show" role="alert"><?php echo $success_message; ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div><?php endif; ?>
    <?php if(!empty($error_message)): ?><div class="alert alert-danger alert-dismissible fade show" role="alert"><?php echo $error_message; ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div><?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr><th>Name</th><th>Description</th><th>Capacity</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($dorms)): ?>
                            <?php foreach($dorms as $dorm): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($dorm['name']); ?></td>
                                    <td><?php echo htmlspecialchars($dorm['description']); ?></td>
                                    <td><?php echo htmlspecialchars($dorm['capacity']); ?></td>
                                    <td>
                                        <a href="<?php echo manage_rooms_url($dorm['id']); ?>" class="btn btn-info btn-sm"><i class="bi bi-door-open-fill me-1"></i>Manage Rooms</a>
                                        <button class="btn btn-warning btn-sm edit-dorm-btn" data-id="<?php echo $dorm['id']; ?>" data-name="<?php echo htmlspecialchars($dorm['name']); ?>" data-description="<?php echo htmlspecialchars($dorm['description']); ?>" data-capacity="<?php echo $dorm['capacity']; ?>" data-bs-toggle="modal" data-bs-target="#editDormModal"><i class="bi bi-pencil-fill me-1"></i>Edit</button>
                                        <button class="btn btn-danger btn-sm delete-dorm-btn" data-id="<?php echo $dorm['id']; ?>" data-bs-toggle="modal" data-bs-target="#deleteDormModal"><i class="bi bi-trash-fill me-1"></i>Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center">No dormitories found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addDormModal" tabindex="-1" aria-labelledby="addDormModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo dormitories_url(); ?>" method="post">
                <div class="modal-header"><h5 class="modal-title" id="addDormModalLabel">Add New Dormitory</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label for="name" class="form-label">Dormitory Name</label><input type="text" class="form-control" id="name" name="name" required></div>
                    <div class="mb-3"><label for="description" class="form-label">Description</label><textarea class="form-control" id="description" name="description" rows="3"></textarea></div>
                    <div class="mb-3"><label for="capacity" class="form-label">Total Capacity</label><input type="number" class="form-control" id="capacity" name="capacity" min="0"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" name="add_dorm" class="btn btn-primary">Save Dormitory</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editDormModal" tabindex="-1" aria-labelledby="editDormModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo dormitories_url(); ?>" method="post">
                <input type="hidden" name="dorm_id" id="edit_dorm_id">
                <div class="modal-header"><h5 class="modal-title" id="editDormModalLabel">Edit Dormitory</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label for="edit_name" class="form-label">Dormitory Name</label><input type="text" class="form-control" id="edit_name" name="name" required></div>
                    <div class="mb-3"><label for="edit_description" class="form-label">Description</label><textarea class="form-control" id="edit_description" name="description" rows="3"></textarea></div>
                    <div class="mb-3"><label for="edit_capacity" class="form-label">Total Capacity</label><input type="number" class="form-control" id="edit_capacity" name="capacity" min="0"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" name="edit_dorm" class="btn btn-primary">Save Changes</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteDormModal" tabindex="-1" aria-labelledby="deleteDormModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo dormitories_url(); ?>" method="post">
                <input type="hidden" name="dorm_id" id="delete_dorm_id">
                <div class="modal-header"><h5 class="modal-title" id="deleteDormModalLabel">Delete Dormitory</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body"><p>Are you sure you want to delete this dormitory? This will also delete all rooms associated with it. This action cannot be undone.</p></div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="delete_dorm" class="btn btn-danger">Delete</button></div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editDormModal = document.getElementById('editDormModal');
    if (editDormModal) {
        editDormModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            document.getElementById('edit_dorm_id').value = button.getAttribute('data-id');
            document.getElementById('edit_name').value = button.getAttribute('data-name');
            document.getElementById('edit_description').value = button.getAttribute('data-description');
            document.getElementById('edit_capacity').value = button.getAttribute('data-capacity');
        });
    }
    const deleteDormModal = document.getElementById('deleteDormModal');
    if(deleteDormModal) {
        deleteDormModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            document.getElementById('delete_dorm_id').value = button.getAttribute('data-id');
        });
    }
});
</script>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
