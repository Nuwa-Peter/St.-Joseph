<?php
session_start();
require_once 'config.php';
require_once 'includes/header.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$student = null;
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $student_id = trim($_GET["id"]);

    $sql = "
        SELECT
            u.*,
            s.name AS stream_name,
            cl.name AS class_level_name
        FROM users u
        LEFT JOIN stream_user su ON u.id = su.user_id
        LEFT JOIN streams s ON su.stream_id = s.id
        LEFT JOIN class_levels cl ON s.class_level_id = cl.id
        WHERE u.id = ? AND u.role = 'student'
    ";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $student_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $student = $result->fetch_assoc();
            } else {
                echo "<div class='alert alert-danger'>No student found with that ID.</div>";
                exit();
            }
        } else {
            echo "<div class='alert alert-danger'>Oops! Something went wrong. Please try again later.</div>";
            exit();
        }
        $stmt->close();
    }
} else {
    echo "<div class='alert alert-danger'>ID parameter is missing from the request.</div>";
    exit();
}
$conn->close();
?>

<h2>View Student Details</h2>

<?php if ($student): ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h3>
        <a href="student_edit.php?id=<?php echo $student['id']; ?>" class="btn btn-primary">Edit Student</a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 text-center">
                <?php if (!empty($student['photo']) && file_exists($student['photo'])): ?>
                    <img src="<?php echo htmlspecialchars($student['photo']); ?>" class="img-fluid rounded" alt="Student Photo" style="max-height: 250px;">
                <?php else:
                    $name = $student["first_name"] . ' ' . $student["last_name"];
                    $initials = '';
                    $parts = explode(' ', $name);
                    foreach ($parts as $part) { $initials .= strtoupper(substr($part, 0, 1)); }
                ?>
                    <div class="avatar-initials mx-auto" style="width: 150px; height: 150px; font-size: 4rem;"><?php echo htmlspecialchars($initials); ?></div>
                <?php endif; ?>
            </div>
            <div class="col-md-8">
                <p><strong>LIN:</strong> <?php echo htmlspecialchars($student['lin']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($student['phone_number']); ?></p>
                <p><strong>Date of Birth:</strong> <?php echo date("d M Y", strtotime($student['date_of_birth'])); ?></p>
                <hr>
                <p><strong>Class:</strong> <?php echo htmlspecialchars($student['class_level_name'] . ' ' . $student['stream_name']); ?></p>
                <p><strong>Student Type:</strong> <?php echo htmlspecialchars(ucfirst($student['student_type'])); ?></p>
                <p><strong>Status:</strong> <span class="badge bg-success"><?php echo htmlspecialchars(ucfirst($student['status'])); ?></span></p>
            </div>
        </div>
    </div>
    <div class="card-footer text-muted">
        <a href="students.php" class="btn btn-secondary">Back to Student List</a>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
