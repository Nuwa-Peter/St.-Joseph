<?php
require_once 'config.php';

// Handle form submission for saving assignments
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_assignments'])) {
    $stream_id = $_POST['stream_id'];
    $subject_ids = $_POST['subject_ids'] ?? [];

    $conn->begin_transaction();

    try {
        // Delete existing assignments for this stream
        $delete_sql = "DELETE FROM stream_subject WHERE stream_id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $stream_id);
        $stmt->execute();
        $stmt->close();

        // Insert new assignments
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
        // Redirect to the same page to show the changes and prevent resubmission
        header("Location: assign_subjects_to_stream.php?stream_id=" . $stream_id . "&success=1");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        // Handle error, maybe show a message
        header("Location: assign_subjects_to_stream.php?stream_id=" . $stream_id . "&error=1");
        exit;
    }
}


require_once 'includes/header.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

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
$subjects = [];
if ($subjects_result->num_rows > 0) {
    while ($row = $subjects_result->fetch_assoc()) {
        $subjects[] = $row;
    }
}

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
?>

<h2>Assign Subjects to Stream</h2>
<p>Select a stream to see and manage its assigned subjects.</p>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">Assignments have been updated successfully.</div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">An error occurred while updating assignments.</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form action="" method="GET" class="mb-4">
            <div class="row align-items-end">
                <div class="col-md-6">
                    <label for="stream_id" class="form-label">Select Stream</label>
                    <select name="stream_id" id="stream_id" class="form-select">
                        <option value="">-- Select a Stream --</option>
                        <?php if ($streams_result->num_rows > 0): ?>
                            <?php while($stream = $streams_result->fetch_assoc()): ?>
                                <option value="<?php echo $stream['id']; ?>" <?php echo ($selected_stream_id == $stream['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($stream['class_level_name'] . ' - ' . $stream['stream_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">Load Subjects</button>
                </div>
            </div>
        </form>

        <?php if ($selected_stream_id): ?>
            <hr>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <input type="hidden" name="stream_id" value="<?php echo $selected_stream_id; ?>">
                <h5>Assign Subjects for <?php
                    // We need to refetch the stream name here as the previous loop is exhausted
                    $streams_result->data_seek(0); // Reset pointer
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
                        <p>No subjects found in the system. Please add subjects first.</p>
                    <?php endif; ?>
                </div>

                <div class="mt-4">
                    <button type="submit" name="save_assignments" class="btn btn-success">Save Assignments</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
