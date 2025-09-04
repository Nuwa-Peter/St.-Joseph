<?php
require_once 'config.php';

// Role-based access control
$admin_roles = ['root', 'headteacher'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $admin_roles)) {
    header("location: dashboard.php");
    exit;
}

$success_message = "";
$error_message = "";

// Handle Add Club submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_club'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $teacher_id = !empty($_POST['teacher_in_charge_id']) ? trim($_POST['teacher_in_charge_id']) : null;

    if (empty($name)) {
        $error_message = "Club name is required.";
    } else {
        $sql = "INSERT INTO clubs (name, description, teacher_in_charge_id, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssi", $name, $description, $teacher_id);
            if ($stmt->execute()) {
                $success_message = "Club added successfully.";
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Handle Edit Club submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_club'])) {
    $club_id = trim($_POST['club_id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $teacher_id = !empty($_POST['teacher_in_charge_id']) ? trim($_POST['teacher_in_charge_id']) : null;

    if (empty($club_id) || empty($name)) {
        $error_message = "Club ID and name are required for editing.";
    } else {
        $sql = "UPDATE clubs SET name = ?, description = ?, teacher_in_charge_id = ?, updated_at = NOW() WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssii", $name, $description, $teacher_id, $club_id);
            if ($stmt->execute()) {
                $success_message = "Club updated successfully.";
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Handle Delete Club submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_club'])) {
    $club_id = trim($_POST['club_id']);

    if (empty($club_id)) {
        $error_message = "Club ID is required for deletion.";
    } else {
        // Also delete associated members to maintain data integrity
        $sql_delete_members = "DELETE FROM club_members WHERE club_id = ?";
        if($stmt_members = $conn->prepare($sql_delete_members)) {
            $stmt_members->bind_param("i", $club_id);
            $stmt_members->execute();
            $stmt_members->close();
        }

        $sql = "DELETE FROM clubs WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $club_id);
            if ($stmt->execute()) {
                $success_message = "Club deleted successfully.";
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

require_once 'includes/header.php';

// Fetch all teachers for the dropdown
$teachers = [];
$sql_teachers = "SELECT id, first_name, last_name FROM users WHERE role = 'teacher' AND status = 'active' ORDER BY last_name, first_name";
$result_teachers = $conn->query($sql_teachers);
if ($result_teachers && $result_teachers->num_rows > 0) {
    while ($row = $result_teachers->fetch_assoc()) {
        $teachers[] = $row;
    }
}

// Fetch all clubs
$clubs = [];
$sql_clubs = "
    SELECT
        c.id,
        c.name,
        c.description,
        CONCAT(u.first_name, ' ', u.last_name) AS teacher_name
    FROM
        clubs c
    LEFT JOIN
        users u ON c.teacher_in_charge_id = u.id
    ORDER BY
        c.name";
$result_clubs = $conn->query($sql_clubs);
if ($result_clubs && $result_clubs->num_rows > 0) {
    while ($row = $result_clubs->fetch_assoc()) {
        $clubs[] = $row;
    }
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-person-badge me-2"></i>Clubs Management</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClubModal">
            <i class="bi bi-plus-circle-fill me-2"></i>Add New Club
        </button>
    </div>

    <!-- Display success/error messages -->
    <?php if(!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if(!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Club Name</th>
                            <th>Description</th>
                            <th>Teacher in Charge</th>
                            <th style="width: 25%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($clubs)): ?>
                            <?php foreach ($clubs as $club): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($club['name']); ?></td>
                                    <td><?php echo htmlspecialchars($club['description']); ?></td>
                                    <td><?php echo htmlspecialchars($club['teacher_name'] ?? 'Not Assigned'); ?></td>
                                    <td>
                                        <a href="view_club.php?id=<?php echo $club['id']; ?>" class="btn btn-sm btn-info"><i class="bi bi-people-fill me-1"></i>Members</a>
                                        <button type="button" class="btn btn-sm btn-warning edit-btn" data-bs-toggle="modal" data-bs-target="#editClubModal" data-club-id="<?php echo $club['id']; ?>">
                                            <i class="bi bi-pencil-fill me-1"></i>Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger delete-btn" data-bs-toggle="modal" data-bs-target="#deleteClubModal" data-club-id="<?php echo $club['id']; ?>">
                                            <i class="bi bi-trash-fill me-1"></i>Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No clubs found. Add one to get started.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add New Club Modal -->
<div class="modal fade" id="addClubModal" tabindex="-1" aria-labelledby="addClubModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="clubs.php" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="addClubModalLabel">Add New Club</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Club Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="teacher_in_charge_id" class="form-label">Teacher in Charge</label>
                        <select class="form-select" id="teacher_in_charge_id" name="teacher_in_charge_id">
                            <option value="">None</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?php echo $teacher['id']; ?>">
                                    <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_club" class="btn btn-primary">Save Club</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Club Modal -->
<div class="modal fade" id="editClubModal" tabindex="-1" aria-labelledby="editClubModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="clubs.php" method="post">
                <input type="hidden" name="club_id" id="edit_club_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editClubModalLabel">Edit Club</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Club Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_teacher_in_charge_id" class="form-label">Teacher in Charge</label>
                        <select class="form-select" id="edit_teacher_in_charge_id" name="teacher_in_charge_id">
                            <option value="">None</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?php echo $teacher['id']; ?>">
                                    <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="edit_club" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Club Modal -->
<div class="modal fade" id="deleteClubModal" tabindex="-1" aria-labelledby="deleteClubModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="clubs.php" method="post">
                <input type="hidden" name="club_id" id="delete_club_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteClubModalLabel">Delete Club</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this club? This will also remove all members from the club. This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_club" class="btn btn-danger">Delete Club</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // For Edit Modal
    const editClubModal = document.getElementById('editClubModal');
    if (editClubModal) {
        editClubModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const clubId = button.getAttribute('data-club-id');

            // Fetch club data via AJAX
            fetch(`api_get_club_details.php?id=${clubId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        console.error('API Error:', data.error);
                        alert('Could not fetch club details. Please try again.');
                    } else {
                        document.getElementById('edit_club_id').value = data.id;
                        document.getElementById('edit_name').value = data.name;
                        document.getElementById('edit_description').value = data.description;
                        document.getElementById('edit_teacher_in_charge_id').value = data.teacher_in_charge_id || '';
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    alert('Could not fetch club details. Please check the console for more information.');
                });
        });
    }

    // For Delete Modal
    const deleteClubModal = document.getElementById('deleteClubModal');
    if (deleteClubModal) {
        deleteClubModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const clubId = button.getAttribute('data-club-id');
            document.getElementById('delete_club_id').value = clubId;
        });
    }
});
</script>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
