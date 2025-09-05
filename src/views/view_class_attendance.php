<?php
require_once __DIR__ . '/../../config.php';

// Ensure user is logged in and is an admin with appropriate permissions
$allowed_roles = ['headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || !in_array($_SESSION['role'], $allowed_roles)) {
    // Redirect class teachers to their specific view
    if (isset($_SESSION["loggedin"]) && $_SESSION['role'] === 'teacher') {
        header("location: class_attendance.php");
        exit;
    }
    header("location: dashboard.php");
    exit;
}

// --- Fetch data for filters ---
$streams_sql = "SELECT s.id, cl.name as class_name, s.name as stream_name
                FROM streams s
                JOIN class_levels cl ON s.class_level_id = cl.id
                ORDER BY cl.name, s.name";
$streams = $conn->query($streams_sql)->fetch_all(MYSQLI_ASSOC);

// --- Handle Filters and Fetch Attendance Data ---
$filter_stream_id = $_GET['stream_id'] ?? '';
$filter_student_id = $_GET['student_id'] ?? '';
$filter_start_date = $_GET['start_date'] ?? date('Y-m-d'); // Default to today
$filter_end_date = $_GET['end_date'] ?? date('Y-m-d');   // Default to today

// This query now correctly uses the `attendances` table and the `stream_id` we added.
$sql = "SELECT a.date, a.status, a.notes,
               u.first_name, u.last_name, u.unique_id,
               s.name as stream_name, cl.name as class_name,
               rec.first_name as recorder_fname, rec.last_name as recorder_lname
        FROM attendances a
        JOIN users u ON a.user_id = u.id
        LEFT JOIN streams s ON a.stream_id = s.id
        LEFT JOIN class_levels cl ON s.class_level_id = cl.id
        LEFT JOIN users rec ON a.recorded_by_id = rec.id
        WHERE 1=1";

$params = [];
$types = '';

if (!empty($filter_stream_id)) {
    $sql .= " AND a.stream_id = ?";
    $params[] = $filter_stream_id;
    $types .= 'i';
}
if (!empty($filter_student_id)) {
    $sql .= " AND a.user_id = ?";
    $params[] = $filter_student_id;
    $types .= 'i';
}
if (!empty($filter_start_date)) {
    $sql .= " AND a.date >= ?";
    $params[] = $filter_start_date;
    $types .= 's';
}
if (!empty($filter_end_date)) {
    $sql .= " AND a.date <= ?";
    $params[] = $filter_end_date;
    $types .= 's';
}
$sql .= " ORDER BY a.date DESC, cl.name ASC, s.name ASC, u.last_name ASC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$attendance_records = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once __DIR__ . '/../../src/includes/header.php';
?>
<style>
    @media print {
        body > .navbar,
        body > main > .footer,
        .filter-card,
        .print-btn {
            display: none;
        }
        .container-fluid {
            padding: 0 !important;
            margin: 0 !important;
        }
    }
</style>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center my-4">
        <h1 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Daily Class Attendance Report</h1>
        <button class="btn btn-secondary print-btn" onclick="window.print()"><i class="bi bi-printer-fill me-2"></i>Print Report</button>
    </div>

    <div class="card filter-card">
        <div class="card-header">
            <i class="bi bi-filter me-2"></i>Filters
        </div>
        <div class="card-body">
            <form action="view_class_attendance.php" method="get" id="filter-form">
                <div class="row g-3 align-items-end">
                    <div class="col-12 mb-2">
                        <label class="form-label">Quick Date Filters</label><br>
                        <div class="btn-group" role="group" aria-label="Quick Date Filters">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="filter-today">Today</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="filter-week">This Week</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="filter-month">This Month</button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="stream_id" class="form-label">Class</label>
                        <select name="stream_id" id="stream_id" class="form-select">
                            <option value="">All Classes</option>
                            <?php foreach ($streams as $stream): ?>
                                <option value="<?php echo $stream['id']; ?>" <?php if ($filter_stream_id == $stream['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($stream['class_name'] . ' ' . $stream['stream_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="student_search" class="form-label">Student</label>
                        <input type="text" class="form-control" id="student_search" placeholder="Search by name or ID...">
                        <input type="hidden" name="student_id" id="student_id" value="<?php echo htmlspecialchars($filter_student_id); ?>">
                        <div id="student-search-results-report" class="list-group position-absolute" style="z-index: 1000; width: calc(100% - 1rem);"></div>
                    </div>
                    <div class="col-md-2">
                        <label for="start_date" class="form-label">From</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo htmlspecialchars($filter_start_date); ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="end_date" class="form-label">To</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo htmlspecialchars($filter_end_date); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-2"></i>Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <i class="bi bi-list-ul me-2"></i>Attendance Records
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Student Name</th>
                            <th>Student ID</th>
                            <th>Class</th>
                            <th>Status</th>
                            <th>Notes</th>
                            <th>Recorded By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($attendance_records)): ?>
                            <tr><td colspan="7" class="text-center">No records found for the selected filters.</td></tr>
                        <?php else: ?>
                            <?php foreach($attendance_records as $record): ?>
                                <tr>
                                    <td><?php echo date("d-M-Y", strtotime($record['date'])); ?></td>
                                    <td><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($record['unique_id']); ?></td>
                                    <td><?php echo htmlspecialchars($record['class_name'] . ' ' . $record['stream_name']); ?></td>
                                    <td>
                                         <?php
                                        $status = htmlspecialchars($record['status']);
                                        $badge_class = 'bg-secondary';
                                        if ($status === 'present') $badge_class = 'bg-success';
                                        if ($status === 'absent') $badge_class = 'bg-danger';
                                        if ($status === 'late') $badge_class = 'bg-warning text-dark';
                                        if ($status === 'excused') $badge_class = 'bg-info text-dark';
                                        echo "<span class='badge " . $badge_class . "'>" . ucfirst($status) . "</span>";
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($record['notes']); ?></td>
                                    <td><?php echo htmlspecialchars($record['recorder_fname'] . ' ' . $record['recorder_lname']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Quick Date Filter Logic ---
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const filterForm = document.getElementById('filter-form');

    document.getElementById('filter-today').addEventListener('click', function() {
        const today = new Date().toISOString().slice(0, 10);
        startDateInput.value = today;
        endDateInput.value = today;
        filterForm.submit();
    });

    document.getElementById('filter-week').addEventListener('click', function() {
        const today = new Date();
        const dayOfWeek = today.getDay(); // Sunday - 0, Monday - 1, ...
        // Adjust to make Monday the first day of the week
        const firstDayOfWeek = new Date(today.setDate(today.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1) )).toISOString().slice(0, 10);
        const lastDayOfWeek = new Date(new Date(firstDayOfWeek).setDate(new Date(firstDayOfWeek).getDate() + 6)).toISOString().slice(0,10);

        startDateInput.value = firstDayOfWeek;
        endDateInput.value = lastDayOfWeek;
        filterForm.submit();
    });

    document.getElementById('filter-month').addEventListener('click', function() {
        const today = new Date();
        const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().slice(0, 10);
        const lastDayOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0).toISOString().slice(0, 10);
        startDateInput.value = firstDayOfMonth;
        endDateInput.value = lastDayOfMonth;
        filterForm.submit();
    });

    // --- Live Student Search Logic ---
    const searchInput = document.getElementById('student_search');
    const resultsContainer = document.getElementById('student-search-results-report');
    const studentIdInput = document.getElementById('student_id');
    let debounceTimer;

    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        const query = searchInput.value.trim();

        // Clear previous results and hidden ID
        resultsContainer.innerHTML = '';
        studentIdInput.value = '';

        if (query.length < 2) return;

        debounceTimer = setTimeout(() => {
            fetch(`api_search_users.php?role=student&q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        data.forEach(student => {
                            const item = document.createElement('a');
                            item.href = '#';
                            item.className = 'list-group-item list-group-item-action';
                            item.textContent = `${student.first_name} ${student.last_name} (${student.unique_id})`;
                            item.dataset.studentId = student.id;
                            item.addEventListener('click', (e) => {
                                e.preventDefault();
                                searchInput.value = item.textContent;
                                studentIdInput.value = item.dataset.studentId;
                                resultsContainer.innerHTML = '';
                            });
                            resultsContainer.appendChild(item);
                        });
                    } else {
                        const noResult = document.createElement('span');
                        noResult.className = 'list-group-item';
                        noResult.textContent = 'No students found';
                        resultsContainer.appendChild(noResult);
                    }
                })
                .catch(error => console.error('Error fetching students:', error));
        }, 300);
    });

    // Hide search results when clicking outside
    document.addEventListener('click', function(e) {
        if (!resultsContainer.contains(e.target) && e.target !== searchInput) {
            resultsContainer.innerHTML = '';
        }
    });

    // Pre-fill student search box if a student_id is in the URL
    if (studentIdInput.value) {
        fetch(`api_get_user.php?id=${studentIdInput.value}`)
            .then(response => response.json())
            .then(data => {
                if (data.id) {
                     searchInput.value = `${data.first_name} ${data.last_name} (${data.unique_id})`;
                }
            });
    }
});
</script>

<?php
$conn->close();
require_once __DIR__ . '/../../src/includes/footer.php';
?>
