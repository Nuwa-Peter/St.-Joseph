<?php
require_once 'config.php';

// Access control for admins
$admin_roles = ['root', 'headteacher'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $admin_roles)) {
    header("location: dashboard.php");
    exit;
}

$success_message = "";
$error_message = "";
$current_academic_year = date('Y'); // Example: Use the current year

// Handle Assign Student
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_student'])) {
    $student_id = trim($_POST['student_id']);
    $room_id = trim($_POST['room_id']);
    $academic_year = trim($_POST['academic_year']);

    if (!empty($student_id) && !empty($room_id) && !empty($academic_year)) {
        // More robust checks would be needed in a production system
        $sql = "INSERT INTO room_assignments (dormitory_room_id, user_id, academic_year, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iis", $room_id, $student_id, $academic_year);
            if ($stmt->execute()) {
                $success_message = "Student assigned to room successfully.";
            } else {
                // Check for duplicate entry
                if ($conn->errno == 1062) {
                    $error_message = "This student is already assigned to a room for this academic year.";
                } else {
                    $error_message = "Error assigning student: " . $stmt->error;
                }
            }
            $stmt->close();
        }
    } else {
        $error_message = "All fields are required.";
    }
}

// Handle Un-assign Student
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['unassign_student'])) {
    $assignment_id = trim($_POST['assignment_id']);
    if (!empty($assignment_id)) {
        $sql = "DELETE FROM room_assignments WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $assignment_id);
            if ($stmt->execute()) {
                $success_message = "Student un-assigned successfully.";
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

require_once 'includes/header.php';

// Fetch all necessary data
$dormitories_data = [];
$sql = "
    SELECT
        d.id as dorm_id, d.name as dorm_name,
        dr.id as room_id, dr.room_number, dr.capacity,
        ra.id as assignment_id, ra.academic_year,
        u.id as student_id, u.first_name, u.last_name
    FROM
        dormitories d
    LEFT JOIN
        dormitory_rooms dr ON d.id = dr.dormitory_id
    LEFT JOIN
        room_assignments ra ON dr.id = ra.dormitory_room_id AND ra.academic_year = ?
    LEFT JOIN
        users u ON ra.user_id = u.id
    ORDER BY
        d.name, dr.room_number, u.last_name
";
if($stmt_main = $conn->prepare($sql)){
    $stmt_main->bind_param("s", $current_academic_year);
    $stmt_main->execute();
    $result = $stmt_main->get_result();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $dorm_id = $row['dorm_id'];
            $room_id = $row['room_id'];
            if (!$dorm_id) continue;

            if (!isset($dormitories_data[$dorm_id])) {
                $dormitories_data[$dorm_id] = ['name' => $row['dorm_name'], 'rooms' => []];
            }
            if ($room_id && !isset($dormitories_data[$dorm_id]['rooms'][$room_id])) {
                $dormitories_data[$dorm_id]['rooms'][$room_id] = ['number' => $row['room_number'], 'capacity' => $row['capacity'], 'assignments' => []];
            }
            if ($row['assignment_id']) {
                $dormitories_data[$dorm_id]['rooms'][$room_id]['assignments'][] = ['id' => $row['assignment_id'], 'student_name' => $row['first_name'] . ' ' . $row['last_name']];
            }
        }
    }
    $stmt_main->close();
}


// Fetch students not yet assigned for the current academic year
$unassigned_students = [];
$sql_unassigned = "SELECT id, first_name, last_name FROM users WHERE role = 'student' AND status = 'active' AND id NOT IN (SELECT user_id FROM room_assignments WHERE academic_year = ?) ORDER BY last_name";
if($stmt_unassigned = $conn->prepare($sql_unassigned)){
    $stmt_unassigned->bind_param("s", $current_academic_year);
    $stmt_unassigned->execute();
    $result_unassigned = $stmt_unassigned->get_result();
    if($result_unassigned) {
        while($row = $result_unassigned->fetch_assoc()) $unassigned_students[] = $row;
    }
    $stmt_unassigned->close();
}


// Fetch all rooms for dropdown
$all_rooms = [];
$sql_all_rooms = "SELECT dr.id, dr.room_number, d.name as dorm_name FROM dormitory_rooms dr JOIN dormitories d ON dr.dormitory_id = d.id ORDER BY d.name, dr.room_number";
$result_all_rooms = $conn->query($sql_all_rooms);
if($result_all_rooms) {
    while($row = $result_all_rooms->fetch_assoc()) $all_rooms[] = $row;
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0"><i class="bi bi-person-square me-2"></i>Room Assignments for <?php echo $current_academic_year; ?></h2>
            <a href="dormitories.php" class="text-decoration-none text-muted-hover"><i class="bi bi-arrow-left-circle me-1"></i>Manage Dormitories & Rooms</a>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignStudentModal">
            <i class="bi bi-person-plus-fill me-2"></i>Assign a Student
        </button>
    </div>

    <?php if(!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert"><?php echo $success_message; ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
    <?php endif; ?>
    <?php if(!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert"><?php echo $error_message; ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
    <?php endif; ?>

    <?php foreach($dormitories_data as $dorm): ?>
        <h4 class="mt-4"><?php echo htmlspecialchars($dorm['name']); ?></h4>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (!empty($dorm['rooms'])): ?>
                <?php foreach($dorm['rooms'] as $room): ?>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between">
                                <strong>Room: <?php echo htmlspecialchars($room['number']); ?></strong>
                                <span class="badge bg-secondary"><?php echo count($room['assignments']); ?> / <?php echo $room['capacity']; ?></span>
                            </div>
                            <ul class="list-group list-group-flush">
                                <?php if(!empty($room['assignments'])): ?>
                                    <?php foreach($room['assignments'] as $assignment): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <?php echo htmlspecialchars($assignment['student_name']); ?>
                                            <form method="post" action="room_assignments.php" class="d-inline">
                                                <input type="hidden" name="assignment_id" value="<?php echo $assignment['id']; ?>">
                                                <button type="submit" name="unassign_student" class="btn btn-sm btn-outline-danger" title="Un-assign"><i class="bi bi-person-x-fill"></i></button>
                                            </form>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="list-group-item text-muted">No students assigned.</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No rooms found for this dormitory.</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<!-- Assign Student Modal -->
<div class="modal fade" id="assignStudentModal" tabindex="-1" aria-labelledby="assignStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="room_assignments.php" method="post">
                <div class="modal-header"><h5 class="modal-title" id="assignStudentModalLabel">Assign Student to Room</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="academic_year" value="<?php echo $current_academic_year; ?>">
                    <div class="mb-3">
                        <label for="student_id" class="form-label">Unassigned Student</label>
                        <select name="student_id" class="form-select" required>
                            <option value="">Select a student...</option>
                            <?php foreach($unassigned_students as $student): ?>
                                <option value="<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['last_name'] . ', ' . $student['first_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="room_id" class="form-label">Room</label>
                        <select name="room_id" class="form-select" required>
                            <option value="">Select a room...</option>
                            <?php foreach($all_rooms as $room): ?>
                                <option value="<?php echo $room['id']; ?>"><?php echo htmlspecialchars($room['dorm_name'] . ' - ' . $room['room_number']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="assign_student" class="btn btn-primary">Assign Student</button></div>
            </form>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
