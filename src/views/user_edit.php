<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../src/includes/header.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$errors = [];
$user_id = 0;
$first_name = $last_name = $username = $email = $role = $status = "";
$linked_children = [];

if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
    $user_id = trim($_GET['id']);
    $sql = "SELECT first_name, last_name, username, email, role, status FROM users WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                $first_name = $user['first_name'];
                $last_name = $user['last_name'];
                $username = $user['username'];
                $email = $user['email'];
                $role = $user['role'];
                $status = $user['status'];

                // If the user is a parent, fetch their linked children
                if ($role === 'parent') {
                    $children_sql = "SELECT u.id, u.first_name, u.last_name FROM parent_student ps JOIN users u ON ps.student_id = u.id WHERE ps.parent_id = ?";
                    if ($children_stmt = $conn->prepare($children_sql)) {
                        $children_stmt->bind_param("i", $user_id);
                        $children_stmt->execute();
                        $children_result = $children_stmt->get_result();
                        while ($child_row = $children_result->fetch_assoc()) {
                            $linked_children[] = $child_row;
                        }
                        $children_stmt->close();
                    }
                }
            } else { exit("User not found."); }
        }
        $stmt->close();
    }
} else { exit("No user ID specified."); }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['id'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $status = trim($_POST['status']);

    if (empty($first_name)) $errors['first_name'] = "First name is required.";
    if (empty($last_name)) $errors['last_name'] = "Last name is required.";
    if (empty($role)) $errors['role'] = "Role is required.";
    if (empty($status)) $errors['status'] = "Status is required.";

    if (empty($username)) {
        $errors['username'] = "Username is required.";
    } else {
        $sql = "SELECT id FROM users WHERE username = ? AND id != ?";
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("si", $username, $user_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows > 0) $errors['username'] = "This username is already taken.";
            $stmt->close();
        }
    }

    if (empty($email)) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    } else {
        $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("si", $email, $user_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows > 0) $errors['email'] = "This email is already taken.";
            $stmt->close();
        }
    }

    if (empty($errors)) {
        $sql = "UPDATE users SET first_name=?, last_name=?, username=?, email=?, role=?, status=?, updated_at=NOW() WHERE id=?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssssi", $first_name, $last_name, $username, $email, $role, $status, $user_id);
            if ($stmt->execute()) {
                // --- Send notification to admins ---
                $admin_roles_to_notify = ['root', 'director', 'headteacher'];
                $admin_ids = [];
                $sql_admins = "SELECT id FROM users WHERE role IN ('" . implode("','", $admin_roles_to_notify) . "')";
                $result_admins = $conn->query($sql_admins);
                while($row = $result_admins->fetch_assoc()) {
                    if ($row['id'] != $_SESSION['id']) {
                        $admin_ids[] = $row['id'];
                    }
                }

                if(!empty($admin_ids)) {
                    $editor_name = $_SESSION['name'];
                    $message = "User profile for '" . $first_name . " " . $last_name . "' was updated by " . $editor_name . ".";
                    $link = "user_edit.php?id=" . $user_id;
                    $notify_sql = "INSERT INTO app_notifications (user_id, message, link) VALUES (?, ?, ?)";
                    $notify_stmt = $conn->prepare($notify_sql);
                    foreach($admin_ids as $admin_id) {
                        $notify_stmt->bind_param("iss", $admin_id, $message, $link);
                        $notify_stmt->execute();
                    }
                    $notify_stmt->close();
                }
                // --- End notification ---

                header("location: users.php");
                exit();
            } else {
                $errors['db'] = "Database error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<h2>Edit User</h2>
<form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
    <input type="hidden" name="id" value="<?php echo $user_id; ?>">
    <?php if(isset($errors['db'])): ?><div class="alert alert-danger"><?php echo $errors['db']; ?></div><?php endif; ?>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="first_name" class="form-label">First Name</label>
            <input type="text" name="first_name" class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($first_name); ?>">
            <?php if(isset($errors['first_name'])): ?><div class="invalid-feedback"><?php echo $errors['first_name']; ?></div><?php endif; ?>
        </div>
        <div class="col-md-6 mb-3">
            <label for="last_name" class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($last_name); ?>">
            <?php if(isset($errors['last_name'])): ?><div class="invalid-feedback"><?php echo $errors['last_name']; ?></div><?php endif; ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($username); ?>">
            <?php if(isset($errors['username'])): ?><div class="invalid-feedback"><?php echo $errors['username']; ?></div><?php endif; ?>
        </div>
        <div class="col-md-6 mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>">
            <?php if(isset($errors['email'])): ?><div class="invalid-feedback"><?php echo $errors['email']; ?></div><?php endif; ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="role" class="form-label">Role</label>
            <select name="role" class="form-select <?php echo isset($errors['role']) ? 'is-invalid' : ''; ?>">
                <option value="student" <?php if($role == 'student') echo 'selected'; ?>>Student</option>
                <option value="teacher" <?php if($role == 'teacher') echo 'selected'; ?>>Teacher</option>
                <option value="parent" <?php if($role == 'parent') echo 'selected'; ?>>Parent</option>
                <option value="bursar" <?php if($role == 'bursar') echo 'selected'; ?>>Bursar</option>
                <option value="librarian" <?php if($role == 'librarian') echo 'selected'; ?>>Librarian</option>
                <option value="headteacher" <?php if($role == 'headteacher') echo 'selected'; ?>>Head Teacher</option>
                <option value="root" <?php if($role == 'root') echo 'selected'; ?>>Root</option>
            </select>
            <?php if(isset($errors['role'])): ?><div class="invalid-feedback"><?php echo $errors['role']; ?></div><?php endif; ?>
        </div>
        <div class="col-md-6 mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" class="form-select <?php echo isset($errors['status']) ? 'is-invalid' : ''; ?>">
                <option value="active" <?php if($status == 'active') echo 'selected'; ?>>Active</option>
                <option value="inactive" <?php if($status == 'inactive') echo 'selected'; ?>>Inactive</option>
            </select>
            <?php if(isset($errors['status'])): ?><div class="invalid-feedback"><?php echo $errors['status']; ?></div><?php endif; ?>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Update User</button>
    <a href="users.php" class="btn btn-secondary">Cancel</a>
</form>

<hr class="my-4">

<?php if ($role === 'parent'): ?>
<div class="card">
    <div class="card-header">
        <h4>Manage Children</h4>
    </div>
    <div class="card-body">
        <h5>Linked Children</h5>
        <?php if (empty($linked_children)): ?>
            <p>No children are currently linked to this parent.</p>
        <?php else: ?>
            <ul class="list-group mb-3">
                <?php foreach ($linked_children as $child): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?php echo htmlspecialchars($child['first_name'] . ' ' . $child['last_name']); ?>
                        <a href="unlink_student_from_parent.php?parent_id=<?php echo $user_id; ?>&student_id=<?php echo $child['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to unlink this child?');">Unlink</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <hr>

        <h5>Link New Child</h5>
        <form action="link_student_to_parent.php" method="post">
            <input type="hidden" name="parent_id" value="<?php echo $user_id; ?>">
            <div class="mb-3">
                <label for="student_search" class="form-label">Search for a student to link:</label>
                <input type="text" class="form-control" id="student_search" name="student_search" placeholder="Start typing student name...">
                <div id="student-search-results" class="list-group mt-2"></div>
                <input type="hidden" name="student_id" id="selected_student_id">
            </div>
            <button type="submit" class="btn btn-success" id="link-student-btn" disabled>Link Student</button>
        </form>
    </div>
</div>
<?php endif; ?>


<hr class="my-4">

<?php
// --- Admin Password Reset ---
// Only show this section to authorized users
$authorized_roles = ['headteacher', 'root', 'director'];
if (in_array($_SESSION['role'], $authorized_roles)):
?>
    <h4 class="mb-3">Reset User Password</h4>
    <form action="admin_update_password.php" method="post" class="needs-validation" novalidate>
        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
        <div class="row">
            <div class="col-md-6">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" name="new_password" id="new_password" class="form-control" required>
                <div class="invalid-feedback">
                    A new password is required.
                </div>
            </div>
            <div class="col-md-6 align-self-end">
                <button type="submit" class="btn btn-warning">Set New Password</button>
            </div>
        </div>
        <small class="form-text text-muted">This will immediately change the user's password. The user will be notified if they are currently logged in.</small>
    </form>
<?php endif; ?>

<?php
$conn->close();
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const studentSearchInput = document.getElementById('student_search');
    const studentSearchResults = document.getElementById('student-search-results');
    const selectedStudentIdInput = document.getElementById('selected_student_id');
    const linkStudentBtn = document.getElementById('link-student-btn');

    if (studentSearchInput) {
        studentSearchInput.addEventListener('input', async function() {
            const query = this.value.trim();
            studentSearchResults.innerHTML = '';
            linkStudentBtn.disabled = true;

            if (query.length < 2) {
                return;
            }

            try {
                // Use the existing live search API, but specify a 'student' context
                const response = await fetch(`api_live_search.php?q=${query}&context=student`);
                const users = await response.json();

                if (users.length > 0) {
                    users.forEach(user => {
                        // Ensure we only show students if the API returns mixed results
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
require_once __DIR__ . '/../../src/includes/footer.php';
?>
