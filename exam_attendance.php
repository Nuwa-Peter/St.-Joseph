<?php
require_once 'config.php';

// Ensure user is logged in and is a teacher or admin
$allowed_roles = ['teacher', 'headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: " . dashboard_url());
    exit;
}

$teacher_id = $_SESSION['id'];

// Fetch classes (streams)
$streams_sql = "SELECT s.id, cl.name as class_name, s.name as stream_name
                FROM streams s
                JOIN class_levels cl ON s.class_level_id = cl.id
                ORDER BY cl.name, s.name";
$streams = $conn->query($streams_sql)->fetch_all(MYSQLI_ASSOC);

// Check for session messages
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <h1 class="my-4"><i class="bi bi-calendar-check me-2"></i>Take Exam Attendance</h1>

    <?php if ($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
    <?php if ($error_message): ?><div class="alert alert-danger"><?php echo $error_message; ?></div><?php endif; ?>

    <div class="card">
        <div class="card-header">Select Class and Exam Date</div>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4"><label for="stream-select" class="form-label">Class</label><select id="stream-select" class="form-select"><option value="">Select a class...</option><?php foreach ($streams as $stream) { $stream_id = $stream['id']; $display_name = htmlspecialchars($stream['class_name'] . ' ' . $stream['stream_name']); echo "<option value='{$stream_id}' data-classname='{$display_name}'>{$display_name}</option>"; } ?></select></div>
                <div class="col-md-3"><label for="attendance-date" class="form-label">Exam Date</label><input type="date" id="attendance-date" class="form-control" value="<?php echo date('Y-m-d'); ?>"></div>
                <div class="col-md-2"><button id="fetch-students-btn" class="btn btn-primary w-100">Load Students</button></div>
            </div>
        </div>
    </div>

    <div id="attendance-sheet-container" class="mt-4 d-none">
        <form id="attendance-form" action="<?php echo save_exam_attendance_url(); ?>" method="post">
            <input type="hidden" name="stream_id" id="form-stream-id">
            <input type="hidden" name="attendance_date" id="form-attendance-date">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center"><h5 id="attendance-sheet-header" class="mb-0"></h5><button type="submit" class="btn btn-success"><i class="bi bi-save me-2"></i>Save Attendance</button></div>
                <div class="card-body"><div id="student-list-container" class="table-responsive"></div></div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fetchBtn = document.getElementById('fetch-students-btn');
    const streamSelect = document.getElementById('stream-select');
    const dateInput = document.getElementById('attendance-date');
    const sheetContainer = document.getElementById('attendance-sheet-container');
    const studentListContainer = document.getElementById('student-list-container');
    const sheetHeader = document.getElementById('attendance-sheet-header');

    fetchBtn.addEventListener('click', function() {
        const streamId = streamSelect.value;
        const attendanceDate = dateInput.value;
        const selectedOption = streamSelect.options[streamSelect.selectedIndex];
        const className = selectedOption.getAttribute('data-classname');

        if (!streamId || !attendanceDate) {
            alert('Please select a class and a date.');
            return;
        }

        document.getElementById('form-stream-id').value = streamId;
        document.getElementById('form-attendance-date').value = attendanceDate;
        sheetHeader.textContent = `Attendance for ${className} on ${attendanceDate}`;

        fetch(`<?php echo url('api/get_students_for_stream'); ?>?stream_id=${streamId}`)
            .then(response => response.json())
            .then(students => {
                if (students.error) throw new Error(students.error);
                buildAttendanceSheet(students);
                sheetContainer.classList.remove('d-none');
            })
            .catch(error => {
                studentListContainer.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
                sheetContainer.classList.remove('d-none');
            });
    });

    function buildAttendanceSheet(students) {
        let tableHtml = `<table class="table table-striped"><thead><tr><th>Student Name</th><th>Student ID</th><th class="text-center">Present</th><th class="text-center">Absent</th><th class="text-center">Late</th><th class="text-center">Excused</th></tr></thead><tbody>`;
        if (students.length === 0) {
            tableHtml += '<tr><td colspan="6" class="text-center">No students found in this class.</td></tr>';
        } else {
            students.forEach(student => {
                tableHtml += `<tr><td>${student.first_name} ${student.last_name}</td><td>${student.unique_id}</td>
                        <td class="text-center"><input class="form-check-input" type="radio" name="attendance[${student.id}]" value="present" checked></td>
                        <td class="text-center"><input class="form-check-input" type="radio" name="attendance[${student.id}]" value="absent"></td>
                        <td class="text-center"><input class="form-check-input" type="radio" name="attendance[${student.id}]" value="late"></td>
                        <td class="text-center"><input class="form-check-input" type="radio" name="attendance[${student.id}]" value="excused"></td></tr>`;
            });
        }
        tableHtml += '</tbody></table>';
        studentListContainer.innerHTML = tableHtml;
    }
});
</script>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
