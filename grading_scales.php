<?php
require_once 'config.php';
require_once 'includes/header.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
// Add role check for admin access if needed
// if ($_SESSION['role'] !== 'root' && $_SESSION['role'] !== 'headteacher') { ... }

$errors = [];
$success_message = "";

// Handle POST requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Create new grading scale
    if (isset($_POST['create_scale'])) {
        $name = trim($_POST['scale_name']);
        $description = trim($_POST['scale_description']);
        if (empty($name)) {
            $errors['scale_name'] = "Scale name is required.";
        } else {
            $stmt = $conn->prepare("INSERT INTO grading_scales (name, description, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
            $stmt->bind_param("ss", $name, $description);
            if ($stmt->execute()) {
                $success_message = "New grading scale created successfully.";
            } else {
                $errors['db'] = "Error creating scale.";
            }
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
            $errors['boundary_form_' . $scale_id] = "Please fill in all required fields correctly.";
        } else {
            $stmt = $conn->prepare("INSERT INTO grade_boundaries (grading_scale_id, grade_name, min_score, max_score, comment, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->bind_param("isiss", $scale_id, $grade_name, $min_score, $max_score, $comment);
            if ($stmt->execute()) {
                $success_message = "New grade boundary added successfully.";
            } else {
                $errors['db'] = "Error adding boundary.";
            }
            $stmt->close();
        }
    }

    // Delete a grading scale
    if (isset($_POST['delete_scale'])) {
        $scale_id = $_POST['scale_id'];
        $stmt = $conn->prepare("DELETE FROM grading_scales WHERE id = ?");
        $stmt->bind_param("i", $scale_id);
        if ($stmt->execute()) {
            $success_message = "Grading scale deleted successfully.";
        } else {
            $errors['db'] = "Error deleting scale.";
        }
        $stmt->close();
    }

    // Delete a grade boundary
    if (isset($_POST['delete_boundary'])) {
        $boundary_id = $_POST['boundary_id'];
        $stmt = $conn->prepare("DELETE FROM grade_boundaries WHERE id = ?");
        $stmt->bind_param("i", $boundary_id);
        if ($stmt->execute()) {
            $success_message = "Grade boundary deleted successfully.";
        } else {
            $errors['db'] = "Error deleting boundary.";
        }
        $stmt->close();
    }
}

// Fetch all grading scales and their boundaries
$scales = [];
$scales_result = $conn->query("SELECT * FROM grading_scales ORDER BY name ASC");
while ($scale = $scales_result->fetch_assoc()) {
    $boundaries = [];
    $stmt = $conn->prepare("SELECT * FROM grade_boundaries WHERE grading_scale_id = ? ORDER BY max_score DESC");
    $stmt->bind_param("i", $scale['id']);
    $stmt->execute();
    $boundaries_result = $stmt->get_result();
    while ($boundary = $boundaries_result->fetch_assoc()) {
        $boundaries[] = $boundary;
    }
    $scale['boundaries'] = $boundaries;
    $scales[] = $scale;
    $stmt->close();
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Manage Grading Scales</h2>
</div>

<?php if($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
<?php if(isset($errors['db'])): ?><div class="alert alert-danger"><?php echo $errors['db']; ?></div><?php endif; ?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Create New Scale</div>
            <div class="card-body">
                <form action="grading_scales.php" method="post">
                    <div class="mb-3">
                        <label for="scale_name" class="form-label">Scale Name</label>
                        <input type="text" name="scale_name" class="form-control <?php echo isset($errors['scale_name']) ? 'is-invalid' : ''; ?>" placeholder="e.g., O-Level Scale" required>
                        <?php if(isset($errors['scale_name'])): ?><div class="invalid-feedback"><?php echo $errors['scale_name']; ?></div><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="scale_description" class="form-label">Description</label>
                        <textarea name="scale_description" class="form-control" rows="2"></textarea>
                    </div>
                    <button type="submit" name="create_scale" class="btn btn-primary">Create Scale</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <h3>Existing Scales</h3>
        <?php if (empty($scales)): ?>
            <p>No grading scales created yet. Use the form on the left to create one.</p>
        <?php else: ?>
            <div class="accordion" id="scalesAccordion">
                <?php foreach ($scales as $scale): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-<?php echo $scale['id']; ?>">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $scale['id']; ?>" aria-expanded="false" aria-controls="collapse-<?php echo $scale['id']; ?>">
                                <?php echo htmlspecialchars($scale['name']); ?>
                            </button>
                        </h2>
                        <div id="collapse-<?php echo $scale['id']; ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?php echo $scale['id']; ?>" data-bs-parent="#scalesAccordion">
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
                                                <form action="grading_scales.php" method="post" class="d-inline">
                                                    <input type="hidden" name="boundary_id" value="<?php echo $boundary['id']; ?>">
                                                    <button type="submit" name="delete_boundary" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure?')">Del</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <hr>
                                <h5>Add New Boundary</h5>
                                <?php if(isset($errors['boundary_form_' . $scale['id']])): ?><div class="alert alert-danger p-2"><?php echo $errors['boundary_form_' . $scale['id']]; ?></div><?php endif; ?>
                                <form action="grading_scales.php" method="post" class="row gx-2 align-items-center">
                                    <input type="hidden" name="scale_id" value="<?php echo $scale['id']; ?>">
                                    <div class="col-auto"><input type="text" name="grade_name" class="form-control form-control-sm" placeholder="Grade" required></div>
                                    <div class="col-auto"><input type="number" name="min_score" class="form-control form-control-sm" placeholder="Min" required></div>
                                    <div class="col-auto"><input type="number" name="max_score" class="form-control form-control-sm" placeholder="Max" required></div>
                                    <div class="col"><input type="text" name="comment" class="form-control form-control-sm" placeholder="Comment"></div>
                                    <div class="col-auto"><button type="submit" name="add_boundary" class="btn btn-sm btn-success">Add</button></div>
                                </form>

                                <hr>
                                <form action="grading_scales.php" method="post" class="text-end">
                                    <input type="hidden" name="scale_id" value="<?php echo $scale['id']; ?>">
                                    <button type="submit" name="delete_scale" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entire scale and all its boundaries?')">Delete Full Scale</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
