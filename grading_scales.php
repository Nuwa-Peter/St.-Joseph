<?php
require_once 'config.php';
require_once 'includes/url_helper.php';

// Authorization check
$allowed_roles = ['admin', 'headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: " . login_url());
    exit;
}

// Handle POST requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Create new grading scale
    if (isset($_POST['create_scale'])) {
        $name = trim($_POST['scale_name']);
        $description = trim($_POST['scale_description']);
        if (empty($name)) {
            $_SESSION['error_message'] = "Scale name is required.";
        } else {
            $stmt = $conn->prepare("INSERT INTO grading_scales (name, description, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
            $stmt->bind_param("ss", $name, $description);
            if ($stmt->execute()) { $_SESSION['success_message'] = "New grading scale created successfully."; }
            else { $_SESSION['error_message'] = "Error creating scale."; }
            $stmt->close();
        }
    }

    // Add grade boundary to a scale
    if (isset($_POST['add_boundary'])) {
        $scale_id = $_POST['scale_id'];
        $grade_name = trim($_POST['grade_name']);
        $min_score = trim($_POST['min_score']);
        $max_score = trim($_POST['max_score']);
        $comment = trim($_POST['comment']);

        if (empty($grade_name) || !is_numeric($min_score) || !is_numeric($max_score)) {
            $_SESSION['error_message'] = "Please fill in all required fields correctly to add a boundary.";
        } else {
            $stmt = $conn->prepare("INSERT INTO grade_boundaries (grading_scale_id, grade_name, min_score, max_score, comment, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->bind_param("isiss", $scale_id, $grade_name, $min_score, $max_score, $comment);
            if ($stmt->execute()) { $_SESSION['success_message'] = "New grade boundary added successfully."; }
            else { $_SESSION['error_message'] = "Error adding boundary."; }
            $stmt->close();
        }
    }

    // Delete a grading scale
    if (isset($_POST['delete_scale'])) {
        $scale_id = $_POST['scale_id'];
        $stmt = $conn->prepare("DELETE FROM grading_scales WHERE id = ?");
        $stmt->bind_param("i", $scale_id);
        if ($stmt->execute()) { $_SESSION['success_message'] = "Grading scale deleted successfully."; }
        else { $_SESSION['error_message'] = "Error deleting scale. It might be in use."; }
        $stmt->close();
    }

    // Delete a grade boundary
    if (isset($_POST['delete_boundary'])) {
        $boundary_id = $_POST['boundary_id'];
        $stmt = $conn->prepare("DELETE FROM grade_boundaries WHERE id = ?");
        $stmt->bind_param("i", $boundary_id);
        if ($stmt->execute()) { $_SESSION['success_message'] = "Grade boundary deleted successfully."; }
        else { $_SESSION['error_message'] = "Error deleting boundary."; }
        $stmt->close();
    }

    header("Location: " . grading_scales_url());
    exit();
}

// Fetch session messages
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Fetch all grading scales and their boundaries
$scales = [];
$scales_result = $conn->query("SELECT * FROM grading_scales ORDER BY name ASC");
if($scales_result) {
    while ($scale = $scales_result->fetch_assoc()) {
        $boundaries = [];
        $stmt = $conn->prepare("SELECT * FROM grade_boundaries WHERE grading_scale_id = ? ORDER BY max_score DESC");
        $stmt->bind_param("i", $scale['id']);
        $stmt->execute();
        $boundaries_result = $stmt->get_result();
        if($boundaries_result) $boundaries = $boundaries_result->fetch_all(MYSQLI_ASSOC);
        $scale['boundaries'] = $boundaries;
        $scales[] = $scale;
        $stmt->close();
    }
}

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center my-4">
        <h2 class="text-primary"><i class="bi bi-rulers me-2"></i>Manage Grading Scales</h2>
    </div>

    <?php if($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
    <?php if($error_message): ?><div class="alert alert-danger"><?php echo $error_message; ?></div><?php endif; ?>

    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header">Create New Scale</div>
                <div class="card-body">
                    <form action="<?php echo grading_scales_url(); ?>" method="post">
                        <div class="mb-3"><label for="scale_name" class="form-label">Scale Name</label><input type="text" name="scale_name" class="form-control" placeholder="e.g., O-Level Scale" required></div>
                        <div class="mb-3"><label for="scale_description" class="form-label">Description</label><textarea name="scale_description" class="form-control" rows="2"></textarea></div>
                        <button type="submit" name="create_scale" class="btn btn-primary">Create Scale</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <h3>Existing Scales</h3>
            <?php if (empty($scales)): ?>
                <p>No grading scales created yet. Use the form on the left to create one.</p>
            <?php else: ?>
                <div class="accordion" id="scalesAccordion">
                    <?php foreach ($scales as $scale): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading-<?php echo $scale['id']; ?>"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $scale['id']; ?>"><?php echo htmlspecialchars($scale['name']); ?></button></h2>
                            <div id="collapse-<?php echo $scale['id']; ?>" class="accordion-collapse collapse" data-bs-parent="#scalesAccordion">
                                <div class="accordion-body">
                                    <p><?php echo htmlspecialchars($scale['description']); ?></p>
                                    <table class="table table-sm table-bordered">
                                        <thead><tr><th>Grade</th><th>Min Score</th><th>Max Score</th><th>Comment</th><th>Action</th></tr></thead>
                                        <tbody>
                                        <?php foreach ($scale['boundaries'] as $boundary): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($boundary['grade_name']); ?></td>
                                                <td><?php echo htmlspecialchars($boundary['min_score']); ?></td>
                                                <td><?php echo htmlspecialchars($boundary['max_score']); ?></td>
                                                <td><?php echo htmlspecialchars($boundary['comment']); ?></td>
                                                <td>
                                                    <form action="<?php echo grading_scales_url(); ?>" method="post" class="d-inline"><input type="hidden" name="boundary_id" value="<?php echo $boundary['id']; ?>"><button type="submit" name="delete_boundary" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure?')"><i class="bi bi-trash"></i></button></form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <div class="d-flex justify-content-between mt-3">
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addBoundaryModal" data-scale-id="<?php echo $scale['id']; ?>" data-scale-name="<?php echo htmlspecialchars($scale['name']); ?>"><i class="bi bi-plus-circle me-1"></i>Add Boundary</button>
                                        <form action="<?php echo grading_scales_url(); ?>" method="post" class="d-inline"><input type="hidden" name="scale_id" value="<?php echo $scale['id']; ?>"><button type="submit" name="delete_scale" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete Full Scale</button></form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Boundary Modal -->
<div class="modal fade" id="addBoundaryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?php echo grading_scales_url(); ?>" method="post">
        <div class="modal-header"><h5 class="modal-title" id="addBoundaryModalLabel">Add New Boundary</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" name="scale_id" id="modal_scale_id">
            <p>Adding boundary to: <strong id="modal_scale_name"></strong></p>
            <div class="mb-3"><label class="form-label">Grade</label><input type="text" name="grade_name" class="form-control" placeholder="e.g., D1, F9" required></div>
            <div class="row">
                <div class="col"><label class="form-label">Min Score</label><input type="number" name="min_score" class="form-control" placeholder="e.g., 80" required></div>
                <div class="col"><label class="form-label">Max Score</label><input type="number" name="max_score" class="form-control" placeholder="e.g., 100" required></div>
            </div>
            <div class="mt-3"><label class="form-label">Comment</label><input type="text" name="comment" class="form-control" placeholder="e.g., Distinction, Pass"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" name="add_boundary" class="btn btn-primary">Save Boundary</button></div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var addBoundaryModal = document.getElementById('addBoundaryModal');
    if(addBoundaryModal) {
        addBoundaryModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var scaleId = button.getAttribute('data-scale-id');
            var scaleName = button.getAttribute('data-scale-name');
            var modalScaleIdInput = addBoundaryModal.querySelector('#modal_scale_id');
            var modalScaleName = addBoundaryModal.querySelector('#modal_scale_name');
            modalScaleIdInput.value = scaleId;
            modalScaleName.textContent = scaleName;
        });
    }
});
</script>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
