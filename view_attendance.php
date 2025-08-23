<?php
session_start();
require_once 'config.php';

// Ensure user is logged in and is a teacher or admin
$allowed_roles = ['teacher', 'headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: dashboard.php");
    exit;
}

// --- Fetch data for filters ---
// Get all streams/classes
$streams_sql = "SELECT s.id, cl.name as class_name, s.name as stream_name
                FROM streams s
                JOIN class_levels cl ON s.class_level_id = cl.id
                ORDER BY cl.name, s.name";
$streams = $conn->query($streams_sql)->fetch_all(MYSQLI_ASSOC);

// --- Handle Filters and Fetch Attendance Data ---
$filter_stream_id = $_GET['stream_id'] ?? '';
$filter_student_id = $_GET['student_id'] ?? '';
$filter_start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
$filter_end_date = $_GET['end_date'] ?? date('Y-m-d');

$sql = "SELECT a.date, a.status, u.first_name, u.last_name, u.unique_id, s.name as stream_name, cl.name as class_name
        FROM attendances a
        JOIN users u ON a.user_id = u.id
        LEFT JOIN stream_user su ON u.id = su.user_id
        LEFT JOIN streams s ON su.stream_id = s.id
        LEFT JOIN class_levels cl ON s.class_level_id = cl.id
        WHERE 1=1";

$params = [];
$types = '';

if (!empty($filter_stream_id)) {
    $sql .= " AND s.id = ?";
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

$sql .= " ORDER BY a.date DESC, u.last_name ASC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$attendance_records = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();


require_once 'includes/header.php';
?>

<div class="container-fluid">
    <h1 class="my-4"><i class="bi bi-file-earmark-text me-2"></i>Attendance Report</h1>

    <div class="card">
        <div class="card-header">
            Filters
        </div>
        <div class="card-body">
            <form action="view_attendance.php" method="get" class="row g-3">
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
                    <input type="text" class="form-control" id="student_search" placeholder="Search by name...">
                    <input type="hidden" name="student_id" id="student_id" value="<?php echo htmlspecialchars($filter_student_id); ?>">
                    <div id="student-search-results-report" class="list-group" style="position: absolute; z-index: 1000;"></div>
                </div>
                <div class="col-md-2">
                    <label for="start_date" class="form-label">From</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo htmlspecialchars($filter_start_date); ?>">
                </div>
                <div class="col-md-2">
                    <label for="end_date" class="form-label">To</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo htmlspecialchars($filter_end_date); ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            Attendance Records
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Student Name</th>
                            <th>Student ID</th>
                            <th>Class</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($attendance_records)): ?>
                            <tr><td colspan="5" class="text-center">No records found for the selected filters.</td></tr>
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
// JS for live student search in filter
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('student_search');
    const resultsContainer = document.getElementById('student-search-results-report');
    const studentIdInput = document.getElementById('student_id');
    let debounceTimer;

    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        studentIdInput.value = ''; // Clear hidden ID when user types
        debounceTimer = setTimeout(() => {
            const query = searchInput.value;
            if (query.length > 1) {
                fetch(`api_search_users.php?role=student&q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        resultsContainer.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(student => {
                                const item = document.createElement('a');
                                item.href = '#';
                                item.className = 'list-group-item list-group-item-action';
                                item.textContent = `${student.first_name} ${student.last_name}`;
                                item.addEventListener('click', (e) => {
                                    e.preventDefault();
                                    searchInput.value = item.textContent;
                                    studentIdInput.value = student.id;
                                    resultsContainer.innerHTML = '';
                                });
                                resultsContainer.appendChild(item);
                            });
                        }
                    });
            } else {
                resultsContainer.innerHTML = '';
            }
        }, 300);
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target)) {
            resultsContainer.innerHTML = '';
        }
    });
});
</script>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
