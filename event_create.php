<?php
session_start();
require_once 'config.php';

// Authorization check
$admin_roles = ['headteacher', 'root', 'director'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $admin_roles)) {
    header("location: dashboard.php");
    exit;
}

$errors = [];
$title = $description = $start_date = $end_date = $event_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $start_date = trim($_POST['start_date']);
    $end_date = trim($_POST['end_date']);
    $event_type = trim($_POST['event_type']);
    $created_by = $_SESSION['id'];

    // Validation
    if (empty($title)) $errors['title'] = "Title is required.";
    if (empty($start_date)) $errors['start_date'] = "Start date is required.";
    if (empty($event_type)) $errors['event_type'] = "Event type is required.";
    if (!empty($end_date) && $end_date < $start_date) {
        $errors['end_date'] = "End date cannot be before the start date.";
    }

    if (empty($errors)) {
        $sql = "INSERT INTO events (title, description, start_date, end_date, event_type, created_by) VALUES (?, ?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            // Set end_date to null if it's empty
            $end_date_to_insert = !empty($end_date) ? $end_date : null;
            $stmt->bind_param("sssssi", $title, $description, $start_date, $end_date_to_insert, $event_type, $created_by);
            if ($stmt->execute()) {
                header("location: events.php");
                exit();
            } else {
                $errors['db'] = "Database error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <h2>Create New Event</h2>
    <a href="events.php" class="btn btn-secondary mb-3">Back to Events</a>

    <form action="event_create.php" method="post">
        <?php if(isset($errors['db'])): ?><div class="alert alert-danger"><?php echo $errors['db']; ?></div><?php endif; ?>

        <div class="mb-3">
            <label for="title" class="form-label">Event Title</label>
            <input type="text" name="title" class="form-control <?php echo isset($errors['title']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($title); ?>">
            <?php if(isset($errors['title'])): ?><div class="invalid-feedback"><?php echo $errors['title']; ?></div><?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($description); ?></textarea>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="start_date" class="form-label">Start Date & Time</label>
                <input type="datetime-local" name="start_date" class="form-control <?php echo isset($errors['start_date']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($start_date); ?>">
                <?php if(isset($errors['start_date'])): ?><div class="invalid-feedback"><?php echo $errors['start_date']; ?></div><?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label for="end_date" class="form-label">End Date & Time (Optional)</label>
                <input type="datetime-local" name="end_date" class="form-control <?php echo isset($errors['end_date']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($end_date); ?>">
                <?php if(isset($errors['end_date'])): ?><div class="invalid-feedback"><?php echo $errors['end_date']; ?></div><?php endif; ?>
            </div>
        </div>

        <div class="mb-3">
            <label for="event_type" class="form-label">Event Type</label>
            <select name="event_type" class="form-select <?php echo isset($errors['event_type']) ? 'is-invalid' : ''; ?>">
                <option value="">Select a type...</option>
                <option value="Holiday" <?php if($event_type == 'Holiday') echo 'selected'; ?>>Holiday</option>
                <option value="Exam" <?php if($event_type == 'Exam') echo 'selected'; ?>>Exam</option>
                <option value="Meeting" <?php if($event_type == 'Meeting') echo 'selected'; ?>>Meeting</option>
                <option value="Sports" <?php if($event_type == 'Sports') echo 'selected'; ?>>Sports</option>
                <option value="General" <?php if($event_type == 'General') echo 'selected'; ?>>General</option>
            </select>
            <?php if(isset($errors['event_type'])): ?><div class="invalid-feedback"><?php echo $errors['event_type']; ?></div><?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">Create Event</button>
    </form>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
