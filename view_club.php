<?php
require_once 'config.php';

// Role-based access control
$admin_roles = ['root', 'headteacher', 'admin'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $admin_roles)) {
    header("location: " . dashboard_url());
    exit;
}

// Check for club ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("location: " . clubs_url());
    exit;
}
$club_id = (int)$_GET['id'];

// Handle Add Member submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_member'])) {
    $student_id = trim($_POST['student_id']);
    if (empty($student_id)) {
        $_SESSION['error_message'] = "Please select a student to add.";
    } else {
        $sql_check = "SELECT id FROM club_members WHERE club_id = ? AND user_id = ?";
        if($stmt_check = $conn->prepare($sql_check)) {
            $stmt_check->bind_param("ii", $club_id, $student_id);
            $stmt_check->execute();
            $stmt_check->store_result();
            if ($stmt_check->num_rows > 0) {
                $_SESSION['error_message'] = "This student is already a member of the club.";
            } else {
                $sql_insert = "INSERT INTO club_members (club_id, user_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";
                if ($stmt_insert = $conn->prepare($sql_insert)) {
                    $stmt_insert->bind_param("ii", $club_id, $student_id);
                    if ($stmt_insert->execute()) {
                        $_SESSION['success_message'] = "Member added successfully.";
                    } else {
                        $_SESSION['error_message'] = "Error adding member.";
                    }
                    $stmt_insert->close();
                }
            }
            $stmt_check->close();
        }
    }
    header("Location: " . club_view_url($club_id));
    exit();
}

// Handle Remove Member submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_member'])) {
    $member_id = trim($_POST['member_id']);
    if (empty($member_id)) {
        $_SESSION['error_message'] = "Invalid member ID.";
    } else {
        $sql_delete = "DELETE FROM club_members WHERE club_id = ? AND user_id = ?";
        if ($stmt_delete = $conn->prepare($sql_delete)) {
            $stmt_delete->bind_param("ii", $club_id, $member_id);
            if ($stmt_delete->execute()) {
                $_SESSION['success_message'] = "Member removed successfully.";
            } else {
                $_SESSION['error_message'] = "Error removing member.";
            }
            $stmt_delete->close();
        }
    }
    header("Location: " . club_view_url($club_id));
    exit();
}

// Fetch session messages
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Fetch club details
$club = null;
$sql_club = "SELECT name, description FROM clubs WHERE id = ?";
if ($stmt_club = $conn->prepare($sql_club)) {
    $stmt_club->bind_param("i", $club_id);
    $stmt_club->execute();
    $result_club = $stmt_club->get_result();
    if ($result_club->num_rows === 1) {
        $club = $result_club->fetch_assoc();
    }
    $stmt_club->close();
}

if (!$club) {
    $_SESSION['error_message'] = "Error: Club not found.";
    header("Location: " . clubs_url());
    exit;
}

// Fetch current members
$members = [];
$sql_members = "SELECT u.id, u.first_name, u.last_name FROM users u JOIN club_members cm ON u.id = cm.user_id WHERE cm.club_id = ? ORDER BY u.last_name, u.first_name";
if ($stmt_members = $conn->prepare($sql_members)) {
    $stmt_members->bind_param("i", $club_id);
    $stmt_members->execute();
    $result_members = $stmt_members->get_result();
    $members = $result_members->fetch_all(MYSQLI_ASSOC);
    $stmt_members->close();
}

// Fetch students not in the club
$non_members = [];
$sql_non_members = "SELECT id, first_name, last_name FROM users WHERE role = 'student' AND status = 'active' AND id NOT IN (SELECT user_id FROM club_members WHERE club_id = ?) ORDER BY last_name, first_name";
if ($stmt_non_members = $conn->prepare($sql_non_members)) {
    $stmt_non_members->bind_param("i", $club_id);
    $stmt_non_members->execute();
    $result_non_members = $stmt_non_members->get_result();
    $non_members = $result_non_members->fetch_all(MYSQLI_ASSOC);
    $stmt_non_members->close();
}

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0"><i class="bi bi-people-fill me-2"></i>Manage Club: <?php echo htmlspecialchars($club['name']); ?></h2>
            <p class="text-muted mb-0"><?php echo htmlspecialchars($club['description']); ?></p>
        </div>
        <a href="<?php echo clubs_url(); ?>" class="btn btn-secondary"><i class="bi bi-arrow-left-circle me-2"></i>Back to Clubs</a>
    </div>

    <!-- Display success/error messages -->
    <?php if(!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if(!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Member List -->
        <div class="col-md-7">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Club Members (<?php echo count($members); ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($members)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($members as $member): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo htmlspecialchars($member['last_name'] . ', ' . $member['first_name']); ?>
                                    <form action="<?php echo club_view_url($club_id); ?>" method="post" class="d-inline">
                                        <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                        <button type="submit" name="remove_member" class="btn btn-sm btn-outline-danger" title="Remove Member"><i class="bi bi-x-circle"></i></button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-center text-muted mt-3">This club has no members yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Add Member Form -->
        <div class="col-md-5">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Add New Member</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo club_view_url($club_id); ?>" method="post">
                        <div class="mb-3">
                            <label for="student_id" class="form-label">Select Student</label>
                            <select class="form-select" id="student_id" name="student_id" required>
                                <option value="" disabled selected>Choose a student...</option>
                                <?php if (!empty($non_members)): ?>
                                    <?php foreach ($non_members as $student): ?>
                                        <option value="<?php echo $student['id']; ?>">
                                            <?php echo htmlspecialchars($student['last_name'] . ', ' . $student['first_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No students available to add.</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <button type="submit" name="add_member" class="btn btn-primary w-100"><i class="bi bi-person-plus-fill me-2"></i>Add to Club</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
