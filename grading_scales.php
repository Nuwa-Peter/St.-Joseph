<?php
require_once 'config.php';
session_start();

// This page has complex logic, so we need all helpers
require_once 'includes/url_helper.php';
require_once 'includes/csrf_helper.php';

// Authentication and Authorization
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: " . login_url());
    exit;
}
if (!in_array($_SESSION['role'], ['admin', 'headteacher', 'root', 'director'])) {
    $_SESSION['error_message'] = "You are not authorized to access this page.";
    header("location: " . dashboard_url());
    exit;
}

// Handle all POST requests at the top
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verify_csrf_token();
    $redirect_url = grading_scales_url();

    try {
        // Create new grading scale
        if (isset($_POST['create_scale'])) {
            $name = trim($_POST['scale_name']);
            $description = trim($_POST['scale_description']);
            if (empty($name)) {
                throw new Exception("Scale name is required.");
            }
            $stmt = $conn->prepare("INSERT INTO grading_scales (name, description, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
            $stmt->bind_param("ss", $name, $description);
            if (!$stmt->execute()) throw new Exception($stmt->error);
            $_SESSION['success_message'] = "New grading scale created successfully.";
            $stmt->close();
        }

        // Add grade boundary to a scale
        if (isset($_POST['add_boundary'])) {
            $scale_id = $_POST['scale_id'];
            $grade_name = trim($_POST['grade_name']);
            $min_score = trim($_POST['min_score']);
            $max_score = trim($_POST['max_score']);
            $comment = trim($_POST['comment']);

            if (empty($grade_name) || !is_numeric($min_score) || !is_numeric($max_score)) {
                throw new Exception("Please fill in all required fields correctly.");
            }
            $stmt = $conn->prepare("INSERT INTO grade_boundaries (grading_scale_id, grade_name, min_score, max_score, comment, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->bind_param("isiss", $scale_id, $grade_name, $min_score, $max_score, $comment);
            if (!$stmt->execute()) throw new Exception($stmt->error);
            $_SESSION['success_message'] = "New grade boundary added successfully.";
            $stmt->close();
        }

        // Delete a grading scale
        if (isset($_POST['delete_scale'])) {
            $scale_id = $_POST['scale_id'];
            // Also delete boundaries to avoid orphaned rows
            $conn->begin_transaction();
            $stmt_b = $conn->prepare("DELETE FROM grade_boundaries WHERE grading_scale_id = ?");
            $stmt_b->bind_param("i", $scale_id);
            $stmt_b->execute();
            $stmt_b->close();

            $stmt_s = $conn->prepare("DELETE FROM grading_scales WHERE id = ?");
            $stmt_s->bind_param("i", $scale_id);
            if (!$stmt_s->execute()) {
                $conn->rollback();
                throw new Exception($stmt_s->error);
            }
            $conn->commit();
            $_SESSION['success_message'] = "Grading scale deleted successfully.";
            $stmt_s->close();
        }

        // Delete a grade boundary
        if (isset($_POST['delete_boundary'])) {
            $boundary_id = $_POST['boundary_id'];
            $stmt = $conn->prepare("DELETE FROM grade_boundaries WHERE id = ?");
            $stmt->bind_param("i", $boundary_id);
            if (!$stmt->execute()) throw new Exception($stmt->error);
            $_SESSION['success_message'] = "Grade boundary deleted successfully.";
            $stmt->close();
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
    }

    header("Location: " . $redirect_url);
    exit;
}


// Page setup
require_once 'includes/header.php';

// Fetch all grading scales and their boundaries for display
$scales = [];
$scales_result = $conn->query("SELECT * FROM grading_scales ORDER BY name ASC");
if ($scales_result) {
    while ($scale = $scales_result->fetch_assoc()) {
        $boundaries = [];
        $stmt = $conn->prepare("SELECT * FROM grade_boundaries WHERE grading_scale_id = ? ORDER BY max_score DESC");
        $stmt->bind_param("i", $scale['id']);
        $stmt->execute();
        $boundaries_result = $stmt->get_result();
        if($boundaries_result) {
            while ($boundary = $boundaries_result->fetch_assoc()) {
                $boundaries[] = $boundary;
            }
        }
        $scale['boundaries'] = $boundaries;
        $scales[] = $scale;
        $stmt->close();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Manage Grading Scales</h2>
</div>

<?php
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
    unset($_SESSION['error_message']);
}
?>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">Create New Scale</div>
            <div class="card-body">
                <form action="<?php echo grading_scales_url(); ?>" method="post">
                    <?php generate_csrf_token_form(); ?>
                    <div class="mb-3">
                        <label for="scale_name" class="form-label">Scale Name</label>
                        <input type="text" name="scale_name" class="form-control" placeholder="e.g., O-Level Scale" required>
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

                                <?php if (!empty($scale['boundaries'])): ?>
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
                                                <form action="<?php echo grading_scales_url(); ?>" method="post" class="d-inline">
                                                    <?php generate_csrf_token_form(); ?>
                                                    <input type="hidden" name="boundary_id" value="<?php echo $boundary['id']; ?>">
                                                    <button type="submit" name="delete_boundary" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure?')"><i class="bi bi-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?php else: ?>
                                    <p class="text-muted">No grade boundaries defined for this scale yet.</p>
                                <?php endif; ?>

                                <hr>
                                <h5>Add New Boundary</h5>
                                <form action="<?php echo grading_scales_url(); ?>" method="post" class="row gx-2 align-items-center">
                                    <?php generate_csrf_token_form(); ?>
                                    <input type="hidden" name="scale_id" value="<?php echo $scale['id']; ?>">
                                    <div class="col-auto"><input type="text" name="grade_name" class="form-control form-control-sm" placeholder="Grade" required></div>
                                    <div class="col-auto"><input type="number" name="min_score" class="form-control form-control-sm" placeholder="Min" required></div>
                                    <div class="col-auto"><input type="number" name="max_score" class="form-control form-control-sm" placeholder="Max" required></div>
                                    <div class="col"><input type="text" name="comment" class="form-control form-control-sm" placeholder="Comment"></div>
                                    <div class="col-auto"><button type="submit" name="add_boundary" class="btn btn-sm btn-success">Add</button></div>
                                </form>

                                <hr>
                                <form action="<?php echo grading_scales_url(); ?>" method="post" class="text-end">
                                    <?php generate_csrf_token_form(); ?>
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
require_once 'includes/footer.php';
?>
