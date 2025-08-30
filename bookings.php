<?php
session_start();
require_once 'config.php';

// All logged-in users can view and book
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$success_message = "";
$error_message = "";

// Handle Add Booking
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_booking'])) {
    $resource_id = trim($_POST['resource_id']);
    $notes = trim($_POST['notes']);
    $start_time = trim($_POST['start_time']);
    $end_time = trim($_POST['end_time']);
    $user_id = $_SESSION['id'];

    if (!empty($resource_id) && !empty($start_time) && !empty($end_time)) {
        if (strtotime($end_time) <= strtotime($start_time)) {
            $error_message = "End time must be after start time.";
        } else {
            // Check for booking overlaps
            $sql_overlap = "SELECT id FROM resource_bookings WHERE resource_id = ? AND (? < end_time AND ? > start_time)";
            $stmt_overlap = $conn->prepare($sql_overlap);
            $stmt_overlap->bind_param("iss", $resource_id, $start_time, $end_time);
            $stmt_overlap->execute();
            $stmt_overlap->store_result();

            if ($stmt_overlap->num_rows > 0) {
                $error_message = "This resource is already booked for the selected time period. Please check the calendar.";
            } else {
                $sql = "INSERT INTO resource_bookings (resource_id, user_id, start_time, end_time, notes, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("iisss", $resource_id, $user_id, $start_time, $end_time, $notes);
                    if ($stmt->execute()) {
                        $success_message = "Resource booked successfully.";
                    } else {
                        $error_message = "Error: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
            $stmt_overlap->close();
        }
    } else {
        $error_message = "Resource, start time, and end time are required.";
    }
}

require_once 'includes/header.php';

// Fetch all bookable resources for the dropdown
$resources = [];
$sql_resources = "SELECT id, name FROM resources WHERE is_bookable = 1 ORDER BY name";
$result = $conn->query($sql_resources);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $resources[] = $row;
    }
}
?>
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css' rel='stylesheet' />

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-calendar-plus-fill me-2"></i>Book a Resource</h2>
        <div>
            <a href="resources.php" class="btn btn-secondary"><i class="bi bi-gear-fill me-1"></i>Manage Resources</a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBookingModal"><i class="bi bi-plus-circle-fill me-1"></i>New Booking</button>
        </div>
    </div>

    <?php if(!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert"><?php echo $success_message; ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
    <?php endif; ?>
    <?php if(!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert"><?php echo $error_message; ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>
</div>

<!-- Add Booking Modal -->
<div class="modal fade" id="addBookingModal" tabindex="-1" aria-labelledby="addBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="bookings.php" method="post">
                <div class="modal-header"><h5 class="modal-title" id="addBookingModalLabel">New Booking</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="resource_id" class="form-label">Resource</label>
                        <select class="form-select" name="resource_id" required>
                            <option value="">Select a resource...</option>
                            <?php foreach($resources as $resource): ?>
                                <option value="<?php echo $resource['id']; ?>"><?php echo htmlspecialchars($resource['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="start_time" class="form-label">Start Time</label>
                        <input type="datetime-local" class="form-control" name="start_time" required>
                    </div>
                    <div class="mb-3">
                        <label for="end_time" class="form-label">End Time</label>
                        <input type="datetime-local" class="form-control" name="end_time" required>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes / Purpose</label>
                        <textarea class="form-control" name="notes" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" name="add_booking" class="btn btn-primary">Create Booking</button></div>
            </form>
        </div>
    </div>
</div>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        events: 'api_get_bookings.php',
        slotMinTime: '08:00:00',
        slotMaxTime: '18:00:00',
        nowIndicator: true,
        selectable: true,
        select: function(info) {
            const modal = new bootstrap.Modal(document.getElementById('addBookingModal'));
            document.querySelector('#addBookingModal form [name="start_time"]').value = info.startStr.slice(0, 16);
            document.querySelector('#addBookingModal form [name="end_time"]').value = info.endStr.slice(0, 16);
            modal.show();
        }
    });
    calendar.render();
});
</script>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
