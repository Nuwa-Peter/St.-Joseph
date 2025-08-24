<?php
session_start();
require_once 'config.php';

// Ensure user is logged in and is a teacher or admin
$allowed_roles = ['teacher', 'class teacher', 'headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: dashboard.php");
    exit;
}

$user_id = $_SESSION['id'];

// Fetch classes (streams)
$streams_sql = "SELECT s.id, cl.name as class_name, s.name as stream_name
                FROM streams s
                JOIN class_levels cl ON s.class_level_id = cl.id
                ORDER BY cl.name, s.name";
$streams = $conn->query($streams_sql)->fetch_all(MYSQLI_ASSOC);

// Check for session messages
$success_message = $_SESSION['class_attendance_success'] ?? '';
$error_message = $_SESSION['class_attendance_error'] ?? '';
unset($_SESSION['class_attendance_success']);
unset($_SESSION['class_attendance_error']);


require_once 'includes/header.php';
?>

<div class="container-fluid">
    <h1 class="my-4"><i class="bi bi-person-check-fill me-2"></i>Daily Class Attendance</h1>

    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            Select Class and Date
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="stream-select" class="form-label">Class</label>
                    <select id="stream-select" class="form-select">
                        <option value="">Select a class...</option>
                        <?php foreach ($streams as $stream): ?>
                            <option value="<?php echo $stream['id']; ?>" data-classname="<?php echo htmlspecialchars($stream['class_name'] . ' ' . $stream['stream_name']); ?>">
                                <?php echo htmlspecialchars($stream['class_name'] . ' ' . $stream['stream_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="attendance-date" class="form-label">Date</label>
                    <input type="date" id="attendance-date" class="form-control" value="<?php echo date('Y-m-d'); ?>" readonly>
                </div>
                <div class="col-md-2">
                    <button id="fetch-students-btn" class="btn btn-primary w-100">Load Class</button>
                </div>
            </div>
        </div>
    </div>

    <!-- This container will hold either the attendance form or a "locked" message -->
    <div id="attendance-display-area" class="mt-4"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fetchBtn = document.getElementById('fetch-students-btn');
    const streamSelect = document.getElementById('stream-select');
    const dateInput = document.getElementById('attendance-date');
    const displayArea = document.getElementById('attendance-display-area');

    fetchBtn.addEventListener('click', function() {
        const streamId = streamSelect.value;
        const attendanceDate = dateInput.value;
        const selectedOption = streamSelect.options[streamSelect.selectedIndex];
        const className = selectedOption ? selectedOption.getAttribute('data-classname') : '';

        if (!streamId) {
            alert('Please select a class.');
            return;
        }

        // 1. Check if attendance has already been taken
        fetch(`api_check_attendance_status.php?stream_id=${streamId}&date=${attendanceDate}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                if (data.status === 'taken') {
                    // Display locked message
                    displayArea.innerHTML = generateLockedMessage(className, attendanceDate);
                } else {
                    // 2. Fetch student list and display attendance form
                    fetchStudentsAndBuildSheet(streamId, attendanceDate, className);
                }
            })
            .catch(error => {
                displayArea.innerHTML = `<div class="alert alert-danger">Error checking attendance status: ${error.message}</div>`;
            });
    });

    function fetchStudentsAndBuildSheet(streamId, attendanceDate, className) {
        fetch(`api_get_students_for_stream.php?stream_id=${streamId}`)
            .then(response => response.json())
            .then(students => {
                if (students.error) {
                    throw new Error(students.error);
                }
                displayArea.innerHTML = generateAttendanceForm(students, streamId, attendanceDate, className);
            })
            .catch(error => {
                displayArea.innerHTML = `<div class="alert alert-danger">Error fetching students: ${error.message}</div>`;
            });
    }

    function generateLockedMessage(className, attendanceDate) {
        return `
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Attendance Locked</h5>
                </div>
                <div class="card-body text-center">
                    <i class="bi bi-lock-fill fs-1 text-warning"></i>
                    <p class="fs-5 mt-3">Attendance for <strong>${className}</strong> on <strong>${attendanceDate}</strong> has already been recorded.</p>
                    <p>It can be taken again tomorrow.</p>
                </div>
            </div>
        `;
    }

    function generateAttendanceForm(students, streamId, attendanceDate, className) {
        let studentRows = '';
        if (students.length === 0) {
            studentRows = '<tr><td colspan="6" class="text-center">No students found in this class.</td></tr>';
        } else {
            students.forEach(student => {
                studentRows += `
                    <tr>
                        <td>${student.first_name} ${student.last_name}</td>
                        <td>${student.unique_id}</td>
                        <td class="text-center"><input class="form-check-input" type="radio" name="attendance[${student.id}]" value="present" checked></td>
                        <td class="text-center"><input class="form-check-input" type="radio" name="attendance[${student.id}]" value="absent"></td>
                        <td class="text-center"><input class="form-check-input" type="radio" name="attendance[${student.id}]" value="late"></td>
                        <td class="text-center"><input class="form-check-input" type="radio" name="attendance[${student.id}]" value="excused"></td>
                        <td><input type="text" name="notes[${student.id}]" class="form-control form-control-sm" placeholder="Add a note..."></td>
                    </tr>
                `;
            });
        }

        return `
            <form id="attendance-form" action="save_class_attendance.php" method="post">
                <input type="hidden" name="stream_id" value="${streamId}">
                <input type="hidden" name="attendance_date" value="${attendanceDate}">

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Attendance for ${className} on ${attendanceDate}</h5>
                        <button type="submit" class="btn btn-success"><i class="bi bi-save me-2"></i>Save Attendance</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Student Name</th>
                                        <th>Student ID</th>
                                        <th class="text-center">Present</th>
                                        <th class="text-center">Absent</th>
                                        <th class="text-center">Late</th>
                                        <th class="text-center">Excused</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${studentRows}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>
        `;
    }
});
</script>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
