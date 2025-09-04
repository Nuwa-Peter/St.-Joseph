<?php
require_once 'config.php';
require_once 'includes/header.php';

// Authorization check: only admins can view the calendar page directly
$admin_roles = ['headteacher', 'root', 'director'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $admin_roles)) {
    header("location: dashboard.php");
    exit;
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>School Event Calendar</h2>
        <?php
        $admin_roles = ['headteacher', 'root', 'director'];
        if (in_array($_SESSION['role'], $admin_roles)):
        ?>
            <a href="events.php" class="btn btn-primary"><i class="bi bi-pencil-square me-2"></i>Manage Events</a>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>
</div>

<!-- Modal to display event details -->
<div class="modal fade" id="eventDetailModal" tabindex="-1" aria-labelledby="eventDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="eventDetailModalLabel">Event Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <h6 id="event-title"></h6>
        <p id="event-description"></p>
        <p><small class="text-muted" id="event-dates"></small></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var eventDetailModal = new bootstrap.Modal(document.getElementById('eventDetailModal'));

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        events: 'api_get_events.php',
        eventClick: function(info) {
            // Populate and show the modal on event click
            document.getElementById('event-title').textContent = info.event.title;
            document.getElementById('event-description').innerHTML = info.event.extendedProps.description ? info.event.extendedProps.description.replace(/\n/g, '<br>') : 'No description provided.';

            let dates = new Date(info.event.start).toLocaleString();
            if(info.event.end) {
                dates += ' - ' + new Date(info.event.end).toLocaleString();
            }
            document.getElementById('event-dates').textContent = dates;

            eventDetailModal.show();
        },
        eventDidMount: function(info) {
            // Add a tooltip (using Bootstrap's tooltip)
            new bootstrap.Tooltip(info.el, {
                title: info.event.title,
                placement: 'top',
                trigger: 'hover',
                container: 'body'
            });
        }
    });

    calendar.render();
});
</script>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
