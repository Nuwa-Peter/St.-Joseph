<?php
require_once __DIR__ . '/../../config.php';

// Authorization check
$admin_roles = ['headteacher', 'root', 'director'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $admin_roles)) {
    header("location: dashboard.php");
    exit;
}

$errors = [];
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($event_id === 0) {
    header("location: events.php?error=notfound");
    exit;
}

// Fetch existing event data
$sql = "SELECT * FROM events WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $event = $result->fetch_assoc();
        $title = $event['title'];
        $description = $event['description'];
        $start_date = date('Y-m-d\TH:i', strtotime($event['start_date']));
        $end_date = $event['end_date'] ? date('Y-m-d\TH:i', strtotime($event['end_date'])) : '';
        $event_type = $event['event_type'];
    } else {
        header("location: events.php?error=notfound");
        exit;
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $start_date = trim($_POST['start_date']);
    $end_date = trim($_POST['end_date']);
    $event_type = trim($_POST['event_type']);

    // Validation
    if (empty($title)) $errors['title'] = "Title is required.";
    // ... add other validation ...

    if (empty($errors)) {
        $sql_update = "UPDATE events SET title=?, description=?, start_date=?, end_date=?, event_type=? WHERE id=?";
        if ($stmt_update = $conn->prepare($sql_update)) {
            $end_date_to_update = !empty($end_date) ? $end_date : null;
            $stmt_update->bind_param("sssssi", $title, $description, $start_date, $end_date_to_update, $event_type, $event_id);
            if ($stmt_update->execute()) {
                header("location: events.php");
                exit();
            } else {
                $errors['db'] = "Database error: " . $stmt_update->error;
            }
            $stmt_update->close();
        }
    }
}

require_once __DIR__ . '/../../src/includes/header.php';
?>

<div class="container mt-4">
    <h2>Edit Event</h2>
    <a href="events.php" class="btn btn-secondary mb-3">Back to Events</a>

    <form action="event_edit.php?id=<?php echo $event_id; ?>" method="post">
        <?php if(isset($errors['db'])): ?><div class="alert alert-danger"><?php echo $errors['db']; ?></div><?php endif; ?>

        <div class="mb-3">
            <label for="title" class="form-label">Event Title</label>
            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($title); ?>">
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($description); ?></textarea>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="start_date" class="form-label">Start Date & Time</label>
                <input type="datetime-local" name="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date); ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label for="end_date" class="form-label">End Date & Time (Optional)</label>
                <input type="datetime-local" name="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date); ?>">
            </div>
        </div>

        <div class="mb-3">
            <label for="event_type" class="form-label">Event Type</label>
            <select name="event_type" class="form-select">
                <option value="Holiday" <?php if($event_type == 'Holiday') echo 'selected'; ?>>Holiday</option>
                <option value="Exam" <?php if($event_type == 'Exam') echo 'selected'; ?>>Exam</option>
                <option value="Meeting" <?php if($event_type == 'Meeting') echo 'selected'; ?>>Meeting</option>
                <option value="Sports" <?php if($event_type == 'Sports') echo 'selected'; ?>>Sports</option>
                <option value="General" <?php if($event_type == 'General') echo 'selected'; ?>>General</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Update Event</button>
    </form>
</div>

<?php
$conn->close();
require_once __DIR__ . '/../../src/includes/footer.php';
?>
