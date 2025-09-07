<?php
require_once 'config.php';

// Access control for admins
$admin_roles = ['root', 'headteacher'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $admin_roles)) {
    header("location: dashboard.php");
    exit;
}

// Get Dormitory ID
if (!isset($_GET['dormitory_id']) || empty($_GET['dormitory_id'])) {
    header("location: dormitories.php");
    exit;
}
$dormitory_id = (int)$_GET['dormitory_id'];

$success_message = "";
$error_message = "";

// Handle Add Room
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_room'])) {
    $room_number = trim($_POST['room_number']);
    $capacity = trim($_POST['capacity']);
    if (!empty($room_number)) {
        $sql = "INSERT INTO dormitory_rooms (dormitory_id, room_number, capacity, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("isi", $dormitory_id, $room_number, $capacity);
            if ($stmt->execute()) {
                $success_message = "Room added successfully.";
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $error_message = "Room number is required.";
    }
}

// Handle Edit Room
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_room'])) {
    $room_id = trim($_POST['room_id']);
    $room_number = trim($_POST['room_number']);
    $capacity = trim($_POST['capacity']);
    if (!empty($room_id) && !empty($room_number)) {
        $sql = "UPDATE dormitory_rooms SET room_number = ?, capacity = ?, updated_at = NOW() WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sii", $room_number, $capacity, $room_id);
            if ($stmt->execute()) {
                $success_message = "Room updated successfully.";
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $error_message = "ID and room number are required.";
    }
}

// Handle Delete Room
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_room'])) {
    $room_id = trim($_POST['room_id']);
    if (!empty($room_id)) {
        // Check for room assignments before deleting
        $sql_check = "SELECT id FROM room_assignments WHERE dormitory_room_id = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("i", $room_id);
        $stmt_check->execute();
        $stmt_check->store_result();
        if($stmt_check->num_rows > 0) {
            $error_message = "Cannot delete room because it has students assigned to it.";
        } else {
            $sql = "DELETE FROM dormitory_rooms WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("i", $room_id);
                if ($stmt->execute()) {
                    $success_message = "Room deleted successfully.";
                } else {
                    $error_message = "Error: " . $stmt->error;
                }
                $stmt->close();
            }
        }
        $stmt_check->close();
    } else {
        $error_message = "ID is required.";
    }
}

require_once 'includes/header.php';

// Fetch dormitory details
$dorm_name = '';
$sql_dorm = "SELECT name FROM dormitories WHERE id = ?";
if ($stmt_dorm = $conn->prepare($sql_dorm)) {
    $stmt_dorm->bind_param("i", $dormitory_id);
    $stmt_dorm->execute();
    $result_dorm = $stmt_dorm->get_result();
    if($row = $result_dorm->fetch_assoc()) {
        $dorm_name = $row['name'];
    } else {
        // Dorm not found, redirect
        header("location: dormitories.php");
        exit;
    }
    $stmt_dorm->close();
}

// Fetch all rooms for this dormitory
$rooms = [];
$sql_rooms = "SELECT id, room_number, capacity FROM dormitory_rooms WHERE dormitory_id = ? ORDER BY room_number";
if ($stmt_rooms = $conn->prepare($sql_rooms)) {
    $stmt_rooms->bind_param("i", $dormitory_id);
    $stmt_rooms->execute();
    $result_rooms = $stmt_rooms->get_result();
    while ($row = $result_rooms->fetch_assoc()) {
        $rooms[] = $row;
    }
    $stmt_rooms->close();
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="dormitories.php" class="text-decoration-none text-muted-hover d-block mb-2"><i class="bi bi-arrow-left-circle me-1"></i>Back to Dormitories</a>
            <h2 class="mb-0"><i class="bi bi-door-open-fill me-2"></i>Rooms in <?php echo htmlspecialchars($dorm_name); ?></h2>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoomModal">
            <i class="bi bi-plus-circle-fill me-2"></i>Add New Room
        </button>
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
                            <th>Room Number / Name</th>
                            <th>Capacity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($rooms)): ?>
                            <?php foreach($rooms as $room): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($room['room_number']); ?></td>
                                    <td><?php echo htmlspecialchars($room['capacity']); ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm edit-room-btn" data-id="<?php echo $room['id']; ?>" data-number="<?php echo htmlspecialchars($room['room_number']); ?>" data-capacity="<?php echo $room['capacity']; ?>" data-bs-toggle="modal" data-bs-target="#editRoomModal"><i class="bi bi-pencil-fill me-1"></i>Edit</button>
                                        <button class="btn btn-danger btn-sm delete-room-btn" data-id="<?php echo $room['id']; ?>" data-bs-toggle="modal" data-bs-target="#deleteRoomModal"><i class="bi bi-trash-fill me-1"></i>Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center">No rooms found in this dormitory.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Room Modal -->
<div class="modal fade" id="addRoomModal" tabindex="-1" aria-labelledby="addRoomModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="manage_rooms.php?dormitory_id=<?php echo $dormitory_id; ?>" method="post">
                <div class="modal-header"><h5 class="modal-title" id="addRoomModalLabel">Add New Room</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label for="room_number" class="form-label">Room Number / Name</label><input type="text" class="form-control" id="room_number" name="room_number" required></div>
                    <div class="mb-3"><label for="capacity" class="form-label">Capacity</label><input type="number" class="form-control" id="capacity" name="capacity" required min="0"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" name="add_room" class="btn btn-primary">Save Room</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Room Modal -->
<div class="modal fade" id="editRoomModal" tabindex="-1" aria-labelledby="editRoomModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="manage_rooms.php?dormitory_id=<?php echo $dormitory_id; ?>" method="post">
                <input type="hidden" name="room_id" id="edit_room_id">
                <div class="modal-header"><h5 class="modal-title" id="editRoomModalLabel">Edit Room</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label for="edit_room_number" class="form-label">Room Number / Name</label><input type="text" class="form-control" id="edit_room_number" name="room_number" required></div>
                    <div class="mb-3"><label for="edit_capacity" class="form-label">Capacity</label><input type="number" class="form-control" id="edit_capacity" name="capacity" required min="0"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" name="edit_room" class="btn btn-primary">Save Changes</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Room Modal -->
<div class="modal fade" id="deleteRoomModal" tabindex="-1" aria-labelledby="deleteRoomModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="manage_rooms.php?dormitory_id=<?php echo $dormitory_id; ?>" method="post">
                <input type="hidden" name="room_id" id="delete_room_id">
                <div class="modal-header"><h5 class="modal-title" id="deleteRoomModalLabel">Delete Room</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body"><p>Are you sure you want to delete this room? This action cannot be undone.</p></div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="delete_room" class="btn btn-danger">Delete</button></div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editRoomModal = document.getElementById('editRoomModal');
    editRoomModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        document.getElementById('edit_room_id').value = button.getAttribute('data-id');
        document.getElementById('edit_room_number').value = button.getAttribute('data-number');
        document.getElementById('edit_capacity').value = button.getAttribute('data-capacity');
    });

    const deleteRoomModal = document.getElementById('deleteRoomModal');
    deleteRoomModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        document.getElementById('delete_room_id').value = button.getAttribute('data-id');
    });
});
</script>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
