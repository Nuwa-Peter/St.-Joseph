<?php
require_once 'config.php';

// Role-based access control
$authorized_roles = ['bursar', 'headteacher', 'root'];
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], $authorized_roles)) {
    header("location: " . dashboard_url() . "?unauthorized=true");
    exit;
}

$errors = [];
$success_message = '';

// Handle form submission for adding a new fee structure
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_fee_structure'])) {
    $name = trim($_POST['name']);
    $academic_year = trim($_POST['academic_year']);
    $description = trim($_POST['description']);

    if (empty($name)) {
        $errors[] = "Structure name is required.";
    }
    if (empty($academic_year)) {
        $errors[] = "Academic year is required.";
    }

    // Check for duplicate name within the same academic year
    if (empty($errors)) {
        $stmt_check = $conn->prepare("SELECT id FROM fee_structures WHERE name = ? AND academic_year = ?");
        $stmt_check->bind_param("ss", $name, $academic_year);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows > 0) {
            $_SESSION['error_message'] = "A fee structure with this name already exists for the selected academic year.";
        } else {
            $stmt_check->close();
            $stmt = $conn->prepare("INSERT INTO fee_structures (name, academic_year, description) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $academic_year, $description);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "New fee structure has been created successfully!";
            } else {
                $_SESSION['error_message'] = "Database error: Failed to create fee structure.";
            }
            $stmt->close();
        }
    } else {
        // Persist errors in session if redirecting
        $_SESSION['form_errors'] = $errors;
    }
    header("location: " . fees_url());
    exit();
}


// Fetch all fee structures to display in the table
$fee_structures = [];
$sql = "SELECT id, name, description, academic_year FROM fee_structures ORDER BY academic_year DESC, name ASC";
if ($result = $conn->query($sql)) {
    $fee_structures = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $_SESSION['error_message'] = "Error fetching fee structures: " . $conn->error;
}

// Check for session messages
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $errors[] = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
if (isset($_SESSION['form_errors'])) {
    $errors = array_merge($errors, $_SESSION['form_errors']);
    unset($_SESSION['form_errors']);
}


require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center my-4">
        <h2 class="text-primary"><i class="bi bi-diagram-3 me-2"></i>Fee Structures</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStructureModal">
            <i class="bi bi-plus-circle me-2"></i>Add New Fee Structure
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

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header">
            Existing Fee Structures
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Structure Name</th>
                            <th>Academic Year</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($fee_structures)): ?>
                            <?php foreach ($fee_structures as $structure): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($structure['name']); ?></td>
                                    <td><?php echo htmlspecialchars($structure['academic_year']); ?></td>
                                    <td><?php echo htmlspecialchars($structure['description']); ?></td>
                                    <td>
                                        <a href="<?php echo url('fee_items.php', ['structure_id' => $structure['id']]); ?>" class="btn btn-sm btn-info" title="View/Edit Items"><i class="bi bi-card-list"></i> Items</a>
                                        <button class="btn btn-sm btn-warning" title="Edit Structure" disabled><i class="bi bi-pencil-square"></i></button>
                                        <button class="btn btn-sm btn-danger" title="Delete Structure" disabled><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No fee structures found. Click "Add New" to create one.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Fee Structure Modal -->
<div class="modal fade" id="addStructureModal" tabindex="-1" aria-labelledby="addStructureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?php echo fees_url(); ?>" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStructureModalLabel">Add New Fee Structure</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Structure Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div class="form-text">A descriptive name (e.g., "S1 Boarding", "S5 Day Scholars").</div>
                    </div>
                    <div class="mb-3">
                        <label for="academic_year" class="form-label">Academic Year <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="academic_year" name="academic_year" placeholder="YYYY/YYYY" required>
                        <div class="form-text">The academic year this structure applies to (e.g., "2025/2026").</div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_fee_structure" class="btn btn-primary">Save Structure</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
