<?php
require_once 'config.php';

// Authorization check
$allowed_roles_to_view = ['admin', 'headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles_to_view)) {
    // Allow users to view their own profile
    if (!isset($_GET['id']) || $_GET['id'] != $_SESSION['id']) {
        header("location: " . login_url());
        exit;
    }
}

$errors = [];
$user_id = $_REQUEST['id'] ?? 0;
if(!$user_id) {
    $_SESSION['error_message'] = "No user ID specified.";
    header("location: " . users_url());
    exit();
}

// Handle User Data Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $status = trim($_POST['status']);

    // Validation...
    if (empty($first_name)) $errors['first_name'] = "First name is required.";
    // Add more validation...

    if (empty($errors)) {
        $sql = "UPDATE users SET first_name=?, last_name=?, username=?, email=?, role=?, status=?, updated_at=NOW() WHERE id=?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssssi", $first_name, $last_name, $username, $email, $role, $status, $user_id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "User profile updated successfully.";
            } else {
                $_SESSION['error_message'] = "Database error: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $_SESSION['form_errors'] = $errors;
    }
    header("Location: " . user_edit_url($user_id));
    exit();
}

// Fetch user data
$sql = "SELECT first_name, last_name, username, email, role, status FROM users WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$user) {
        $_SESSION['error_message'] = "User not found.";
        header("location: " . users_url());
        exit();
    }
}

// If the user is a parent, fetch their linked children
$linked_children = [];
if ($user['role'] === 'parent') {
    $children_sql = "SELECT u.id, u.first_name, u.last_name FROM parent_student ps JOIN users u ON ps.student_id = u.id WHERE ps.parent_id = ?";
    if ($children_stmt = $conn->prepare($children_sql)) {
        $children_stmt->bind_param("i", $user_id);
        $children_stmt->execute();
        $children_result = $children_stmt->get_result();
        $linked_children = $children_result->fetch_all(MYSQLI_ASSOC);
        $children_stmt->close();
    }
}

// Fetch session messages
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
if (isset($_SESSION['form_errors'])) {
    $errors = array_merge($errors, $_SESSION['form_errors']);
    unset($_SESSION['form_errors']);
}
unset($_SESSION['success_message'], $_SESSION['error_message']);

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-person-lines-fill me-2"></i>Edit User</h2>
        <a href="<?php echo users_url(); ?>" class="btn btn-secondary">Back to Users</a>
    </div>

    <?php if($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
    <?php if($error_message): ?><div class="alert alert-danger"><?php echo $error_message; ?></div><?php endif; ?>

    <form action="<?php echo user_edit_url($user_id); ?>" method="post">
        <input type="hidden" name="id" value="<?php echo $user_id; ?>">
        <div class="card shadow-sm mb-4">
            <div class="card-header">User Details</div>
            <div class="card-body">
                <?php if(isset($errors['db'])): ?><div class="alert alert-danger"><?php echo $errors['db']; ?></div><?php endif; ?>
                <div class="row">
                    <div class="col-md-6 mb-3"><label for="first_name" class="form-label">First Name</label><input type="text" name="first_name" class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($user['first_name']); ?>"><?php if(isset($errors['first_name'])): ?><div class="invalid-feedback"><?php echo $errors['first_name']; ?></div><?php endif; ?></div>
                    <div class="col-md-6 mb-3"><label for="last_name" class="form-label">Last Name</label><input type="text" name="last_name" class="form-control <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($user['last_name']); ?>"><?php if(isset($errors['last_name'])): ?><div class="invalid-feedback"><?php echo $errors['last_name']; ?></div><?php endif; ?></div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label for="username" class="form-label">Username</label><input type="text" name="username" class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($user['username']); ?>"><?php if(isset($errors['username'])): ?><div class="invalid-feedback"><?php echo $errors['username']; ?></div><?php endif; ?></div>
                    <div class="col-md-6 mb-3"><label for="email" class="form-label">Email Address</label><input type="email" name="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($user['email']); ?>"><?php if(isset($errors['email'])): ?><div class="invalid-feedback"><?php echo $errors['email']; ?></div><?php endif; ?></div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label for="role" class="form-label">Role</label><select name="role" class="form-select <?php echo isset($errors['role']) ? 'is-invalid' : ''; ?>"><option value="student" <?php if($user['role'] == 'student') echo 'selected'; ?>>Student</option><option value="teacher" <?php if($user['role'] == 'teacher') echo 'selected'; ?>>Teacher</option><option value="parent" <?php if($user['role'] == 'parent') echo 'selected'; ?>>Parent</option><option value="bursar" <?php if($user['role'] == 'bursar') echo 'selected'; ?>>Bursar</option><option value="librarian" <?php if($user['role'] == 'librarian') echo 'selected'; ?>>Librarian</option><option value="headteacher" <?php if($user['role'] == 'headteacher') echo 'selected'; ?>>Head Teacher</option><option value="root" <?php if($user['role'] == 'root') echo 'selected'; ?>>Root</option></select><?php if(isset($errors['role'])): ?><div class="invalid-feedback"><?php echo $errors['role']; ?></div><?php endif; ?></div>
                    <div class="col-md-6 mb-3"><label for="status" class="form-label">Status</label><select name="status" class="form-select <?php echo isset($errors['status']) ? 'is-invalid' : ''; ?>"><option value="active" <?php if($user['status'] == 'active') echo 'selected'; ?>>Active</option><option value="inactive" <?php if($user['status'] == 'inactive') echo 'selected'; ?>>Inactive</option></select><?php if(isset($errors['status'])): ?><div class="invalid-feedback"><?php echo $errors['status']; ?></div><?php endif; ?></div>
                </div>
                <button type="submit" name="update_user" class="btn btn-primary">Update User</button>
            </div>
        </div>
    </form>

    <?php if ($user['role'] === 'parent'): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header"><h4>Manage Children</h4></div>
        <div class="card-body">
            <h5>Linked Children</h5>
            <?php if (empty($linked_children)): ?><p>No children are currently linked.</p>
            <?php else: ?>
                <ul class="list-group mb-3">
                    <?php foreach ($linked_children as $child): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo htmlspecialchars($child['first_name'] . ' ' . $child['last_name']); ?>
                            <a href="<?php echo unlink_student_from_parent_url(['parent_id' => $user_id, 'student_id' => $child['id']]); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Unlink</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <hr>
            <h5>Link New Child</h5>
            <form action="<?php echo link_student_to_parent_url(); ?>" method="post">
                <input type="hidden" name="parent_id" value="<?php echo $user_id; ?>">
                <div class="mb-3"><label for="student_search" class="form-label">Search for student:</label><input type="text" class="form-control" id="student_search" placeholder="Start typing student name..."><div id="student-search-results" class="list-group mt-2"></div><input type="hidden" name="student_id" id="selected_student_id"></div>
                <button type="submit" class="btn btn-success" id="link-student-btn" disabled>Link Student</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <?php if (in_array($_SESSION['role'], ['headteacher', 'root', 'director'])): ?>
    <div class="card shadow-sm">
        <div class="card-header"><h4>Reset User Password</h4></div>
        <div class="card-body">
            <form action="<?php echo admin_update_password_url(); ?>" method="post" class="needs-validation" novalidate>
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <div class="row">
                    <div class="col-md-6"><label for="new_password" class="form-label">New Password</label><input type="password" name="new_password" id="new_password" class="form-control" required><div class="invalid-feedback">A new password is required.</div></div>
                    <div class="col-md-6 align-self-end"><button type="submit" class="btn btn-warning">Set New Password</button></div>
                </div>
                <small class="form-text text-muted">This will immediately change the user's password.</small>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const studentSearchInput = document.getElementById('student_search');
    if (studentSearchInput) {
        const studentSearchResults = document.getElementById('student-search-results');
        const selectedStudentIdInput = document.getElementById('selected_student_id');
        const linkStudentBtn = document.getElementById('link-student-btn');

        studentSearchInput.addEventListener('input', async function() {
            const query = this.value.trim();
            studentSearchResults.innerHTML = '';
            linkStudentBtn.disabled = true;
            if (query.length < 2) return;

            try {
                const response = await fetch(`<?php echo url('api/live_search'); ?>?q=${query}&context=student`);
                const users = await response.json();
                if (users.length > 0) {
                    users.forEach(user => {
                        if (user.role === 'student') {
                            const userItem = document.createElement('a');
                            userItem.href = '#';
                            userItem.className = 'list-group-item list-group-item-action';
                            userItem.textContent = `${user.first_name} ${user.last_name} (ID: ${user.id})`;
                            userItem.addEventListener('click', function(e) {
                                e.preventDefault();
                                studentSearchInput.value = this.textContent;
                                selectedStudentIdInput.value = user.id;
                                studentSearchResults.innerHTML = '';
                                linkStudentBtn.disabled = false;
                            });
                            studentSearchResults.appendChild(userItem);
                        }
                    });
                } else {
                    studentSearchResults.innerHTML = '<div class="list-group-item">No students found.</div>';
                }
            } catch (error) {
                console.error('Error searching for students:', error);
            }
        });
    }
});
</script>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
