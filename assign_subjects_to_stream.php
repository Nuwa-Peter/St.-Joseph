<?php
require_once 'config.php';

// Authorization check
$allowed_roles = ['admin', 'headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: " . login_url());
    exit;
}

// Handle form submission for saving assignments
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_assignments'])) {
    $stream_id = $_POST['stream_id'];
    $subject_ids = $_POST['subject_ids'] ?? [];

    $conn->begin_transaction();
    try {
        $delete_sql = "DELETE FROM stream_subject WHERE stream_id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $stream_id);
        $stmt->execute();
        $stmt->close();

        if (!empty($subject_ids)) {
            $insert_sql = "INSERT INTO stream_subject (stream_id, subject_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";
            $stmt = $conn->prepare($insert_sql);
            foreach ($subject_ids as $subject_id) {
                $stmt->bind_param("ii", $stream_id, $subject_id);
                $stmt->execute();
            }
            $stmt->close();
        }
        $conn->commit();
        $_SESSION['success_message'] = "Assignments have been updated successfully.";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "An error occurred while updating assignments.";
    }
    header("Location: " . assign_subjects_to_stream_url(['stream_id' => $stream_id]));
    exit;
}

// Fetch session messages
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Fetch all streams with their class level names
$streams_sql = "
    SELECT st.id, st.name AS stream_name, cl.name AS class_level_name
    FROM streams st
    JOIN class_levels cl ON st.class_level_id = cl.id
    ORDER BY cl.name, st.name
";
$streams_result = $conn->query($streams_sql);

// Fetch all subjects
$subjects_sql = "SELECT id, name FROM subjects ORDER BY name";
$subjects_result = $conn->query($subjects_sql);
$subjects = $subjects_result->fetch_all(MYSQLI_ASSOC);

$selected_stream_id = $_GET['stream_id'] ?? null;
$assigned_subject_ids = [];

if ($selected_stream_id) {
    $assigned_sql = "SELECT subject_id FROM stream_subject WHERE stream_id = ?";
    if ($stmt = $conn->prepare($assigned_sql)) {
        $stmt->bind_param("i", $selected_stream_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $assigned_subject_ids[] = $row['subject_id'];
        }
        $stmt->close();
    }
}

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <h2>Assign Subjects to Stream</h2>
    <p>Select a stream to see and manage its assigned subjects.</p>

    <?php if ($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
    <?php if ($error_message): ?><div class="alert alert-danger"><?php echo $error_message; ?></div><?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="<?php echo assign_subjects_to_stream_url(); ?>" method="GET" class="mb-4">
                <div class="row align-items-end">
                    <div class="col-md-6">
                        <label for="stream_id" class="form-label">Select Stream</label>
                        <select name="stream_id" id="stream_id" class="form-select">
                            <option value="">-- Select a Stream --</option>
                            <?php if ($streams_result && $streams_result->num_rows > 0):
                                $streams_result->data_seek(0);
                                while($stream = $streams_result->fetch_assoc()): ?>
                                <option value="<?php echo $stream['id']; ?>" <?php echo ($selected_stream_id == $stream['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($stream['class_level_name'] . ' - ' . $stream['stream_name']); ?>
                                </option>
                            <?php endwhile; endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">Load Subjects</button>
                    </div>
                </div>
            </form>

            <?php if ($selected_stream_id): ?>
                <hr>
                <form action="<?php echo assign_subjects_to_stream_url(); ?>" method="POST">
                    <input type="hidden" name="stream_id" value="<?php echo $selected_stream_id; ?>">
                    <h5>Assign Subjects for <?php
                        $streams_result->data_seek(0);
                        while($stream = $streams_result->fetch_assoc()) {
                            if ($stream['id'] == $selected_stream_id) {
                                echo htmlspecialchars($stream['class_level_name'] . ' - ' . $stream['stream_name']);
                                break;
                            }
                        }
                    ?></h5>

                    <div class="row mt-3">
                        <?php if (!empty($subjects)): ?>
                            <?php foreach ($subjects as $subject): ?>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="subject_ids[]" value="<?php echo $subject['id']; ?>" id="subject_<?php echo $subject['id']; ?>"
                                            <?php echo in_array($subject['id'], $assigned_subject_ids) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="subject_<?php echo $subject['id']; ?>">
                                            <?php echo htmlspecialchars($subject['name']); ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No subjects found. Please add subjects first.</p>
                        <?php endif; ?>
                    </div>

                    <div class="mt-4">
                        <button type="submit" name="save_assignments" class="btn btn-success">Save Assignments</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
