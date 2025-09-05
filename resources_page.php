<?php
require_once 'config.php';

// Access control for admins
$admin_roles = ['root', 'headteacher'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $admin_roles)) {
    header("location: dashboard.php");
    exit;
}

$success_message = "";
$error_message = "";

// Handle Add Resource
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_resource'])) {
    $name = trim($_POST['name']);
    $type = trim($_POST['type']);
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);
    $is_bookable = isset($_POST['is_bookable']) ? 1 : 0;

    if (!empty($name) && !empty($type)) {
        $sql = "INSERT INTO resources (name, type, location, description, is_bookable, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssi", $name, $type, $location, $description, $is_bookable);
            if ($stmt->execute()) {
                $success_message = "Resource added successfully.";
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $error_message = "Resource name and type are required.";
    }
}

// Handle Edit Resource
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_resource'])) {
    $id = trim($_POST['resource_id']);
    $name = trim($_POST['name']);
    $type = trim($_POST['type']);
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);
    $is_bookable = isset($_POST['is_bookable']) ? 1 : 0;

    if (!empty($id) && !empty($name) && !empty($type)) {
        $sql = "UPDATE resources SET name = ?, type = ?, location = ?, description = ?, is_bookable = ?, updated_at = NOW() WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssii", $name, $type, $location, $description, $is_bookable, $id);
            if ($stmt->execute()) {
                $success_message = "Resource updated successfully.";
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $error_message = "ID, name, and type are required.";
    }
}

// Handle Delete Resource
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_resource'])) {
    $id = trim($_POST['resource_id']);
    if (!empty($id)) {
        // You might want to check for existing bookings before deleting
        $sql = "DELETE FROM resources WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $success_message = "Resource deleted successfully.";
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $error_message = "ID is required.";
    }
}

require_once 'includes/header.php';

// Fetch all resources
$resources = [];
$sql_resources = "SELECT * FROM resources ORDER BY type, name";
$result = $conn->query($sql_resources);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $resources[] = $row;
    }
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-journal-bookmark-fill me-2"></i>Manage Resources</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addResourceModal"><i class="bi bi-plus-circle-fill me-1"></i>Add New Resource</button>
    </div>

    <?php if(!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert"><?php echo $success_message; ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
    <?php endif; ?>
    <?php if(!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert"><?php echo $error_message; ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Location</th>
                            <th>Bookable</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($resources)): ?>
                            <?php foreach($resources as $resource): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($resource['name']); ?></td>
                                    <td><?php echo htmlspecialchars($resource['type']); ?></td>
                                    <td><?php echo htmlspecialchars($resource['location']); ?></td>
                                    <td><span class="badge <?php echo $resource['is_bookable'] ? 'bg-success' : 'bg-secondary'; ?>"><?php echo $resource['is_bookable'] ? 'Yes' : 'No'; ?></span></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm edit-btn" data-bs-toggle="modal" data-bs-target="#editResourceModal" data-resource='<?php echo json_encode($resource); ?>'><i class="bi bi-pencil-fill me-1"></i>Edit</button>
                                        <button class="btn btn-danger btn-sm delete-btn" data-bs-toggle="modal" data-bs-target="#deleteResourceModal" data-id="<?php echo $resource['id']; ?>"><i class="bi bi-trash-fill me-1"></i>Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center">No resources found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addResourceModal" tabindex="-1" aria-labelledby="addResourceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/resources" method="post">
                <div class="modal-header"><h5 class="modal-title" id="addResourceModalLabel">Add New Resource</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Name</label><input type="text" class="form-control" name="name" required></div>
                    <div class="mb-3"><label class="form-label">Type</label><input type="text" class="form-control" name="type" placeholder="e.g., Laboratory, Hall, Projector" required></div>
                    <div class="mb-3"><label class="form-label">Location</label><input type="text" class="form-control" name="location"></div>
                    <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"></textarea></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="is_bookable" value="1" id="is_bookable_add" checked><label class="form-check-label" for="is_bookable_add">This resource can be booked</label></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" name="add_resource" class="btn btn-primary">Save Resource</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editResourceModal" tabindex="-1" aria-labelledby="editResourceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/resources" method="post">
                <input type="hidden" name="resource_id" id="edit_resource_id">
                <div class="modal-header"><h5 class="modal-title" id="editResourceModalLabel">Edit Resource</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Name</label><input type="text" class="form-control" id="edit_name" name="name" required></div>
                    <div class="mb-3"><label class="form-label">Type</label><input type="text" class="form-control" id="edit_type" name="type" required></div>
                    <div class="mb-3"><label class="form-label">Location</label><input type="text" class="form-control" id="edit_location" name="location"></div>
                    <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" id="edit_description" name="description" rows="3"></textarea></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="is_bookable" value="1" id="edit_is_bookable"><label class="form-check-label" for="edit_is_bookable">This resource can be booked</label></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" name="edit_resource" class="btn btn-primary">Save Changes</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteResourceModal" tabindex="-1" aria-labelledby="deleteResourceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/resources" method="post">
                <input type="hidden" name="resource_id" id="delete_resource_id">
                <div class="modal-header"><h5 class="modal-title" id="deleteResourceModalLabel">Delete Resource</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body"><p>Are you sure you want to delete this resource? All associated bookings will also be deleted. This action cannot be undone.</p></div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="delete_resource" class="btn btn-danger">Delete</button></div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editModal = document.getElementById('editResourceModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const resource = JSON.parse(button.getAttribute('data-resource'));

        document.getElementById('edit_resource_id').value = resource.id;
        document.getElementById('edit_name').value = resource.name;
        document.getElementById('edit_type').value = resource.type;
        document.getElementById('edit_location').value = resource.location;
        document.getElementById('edit_description').value = resource.description;
        document.getElementById('edit_is_bookable').checked = resource.is_bookable == 1;
    });

    const deleteModal = document.getElementById('deleteResourceModal');
    deleteModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        document.getElementById('delete_resource_id').value = button.getAttribute('data-id');
    });
});
</script>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
