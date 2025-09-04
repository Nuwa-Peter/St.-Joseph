<?php
require_once 'config.php';

// Redirect if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$errors = [];
$first_name = $last_name = $username = $email = $role = "";

// Define roles available for creation based on the creator's role
$all_roles = ['student', 'teacher', 'parent', 'bursar', 'librarian', 'lab_attendant', 'headteacher', 'root'];
$allowed_roles = $all_roles;

// Headteachers cannot create root users
if (isset($_SESSION['role']) && $_SESSION['role'] === 'headteacher') {
    $allowed_roles = array_filter($all_roles, function($r) { return $r !== 'root'; });
}

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verify_csrf_token();
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = trim($_POST['role']);

    // Validate form fields
    if (empty($first_name)) $errors['first_name'] = "First name is required.";
    if (empty($last_name)) $errors['last_name'] = "Last name is required.";
    if (empty($password)) $errors['password'] = "Password is required.";

    // Validate role
    if (empty($role)) {
        $errors['role'] = "Role is required.";
    } elseif (!in_array($role, $allowed_roles)) {
        $errors['role'] = "You do not have permission to create a user with this role.";
    }

    // Validate username
    if (empty($username)) {
        $errors['username'] = "Username is required.";
    } else {
        $sql = "SELECT id FROM users WHERE username = ?";
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows > 0) $errors['username'] = "This username is already taken.";
            $stmt->close();
        }
    }

    // Validate email only if it is not empty
    if (!empty($email)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Invalid email format.";
        } else {
            $sql = "SELECT id FROM users WHERE email = ?";
            if($stmt = $conn->prepare($sql)){
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();
                if($stmt->num_rows > 0) $errors['email'] = "This email is already taken.";
                $stmt->close();
            }
        }
    }

    // Check input errors before inserting in database
    if (empty($errors)) {
        $conn->begin_transaction();
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $email_to_insert = !empty($email) ? $email : null;
            $sql = "INSERT INTO users (first_name, last_name, username, email, password, role, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $first_name, $last_name, $username, $email_to_insert, $hashed_password, $role);
            $stmt->execute();
            $new_user_id = $stmt->insert_id;
            $stmt->close();

            // If the role is parent, link the selected students
            if ($role === 'parent' && !empty($_POST['linked_student_ids'])) {
                $linked_student_ids = explode(',', $_POST['linked_student_ids']);
                $link_sql = "INSERT INTO parent_student (parent_id, student_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";
                $link_stmt = $conn->prepare($link_sql);
                foreach ($linked_student_ids as $student_id) {
                    if (is_numeric($student_id)) {
                        $link_stmt->bind_param("ii", $new_user_id, $student_id);
                        $link_stmt->execute();
                    }
                }
                $link_stmt->close();
            }

            $conn->commit();

            // --- Send notification to admins ---
            $admin_roles_to_notify = ['root', 'director', 'headteacher'];
            $admin_ids = [];
            $sql_admins = "SELECT id FROM users WHERE role IN ('" . implode("','", $admin_roles_to_notify) . "')";
            $result_admins = $conn->query($sql_admins);
            while($row = $result_admins->fetch_assoc()) {
                if ($row['id'] != $_SESSION['id']) { // Don't notify the admin who performed the action
                    $admin_ids[] = $row['id'];
                }
            }

            if(!empty($admin_ids)) {
                $creator_name = $_SESSION['name'];
                $message = "New user '" . $first_name . " " . $last_name . "' (" . $role . ") was created by " . $creator_name . ".";
                $link = "user_edit.php?id=" . $new_user_id;
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

        } catch (Exception $e) {
            $conn->rollback();
            $errors['db'] = "An error occurred: " . $e->getMessage();
        }
    }
}

require_once 'includes/header.php';
?>

<h2>Create User</h2>
<form action="user_create.php" method="post">
    <?php echo csrf_input(); ?>
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
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>">
            <?php if(isset($errors['password'])): ?><div class="invalid-feedback"><?php echo $errors['password']; ?></div><?php endif; ?>
        </div>
        <div class="col-md-6 mb-3">
            <label for="role" class="form-label">Role</label>
            <select name="role" class="form-select <?php echo isset($errors['role']) ? 'is-invalid' : ''; ?>">
                <option value="">Select a role...</option>
                <?php foreach ($allowed_roles as $role_value): ?>
                    <option value="<?php echo htmlspecialchars($role_value); ?>" <?php echo ($role == $role_value) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $role_value))); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if(isset($errors['role'])): ?><div class="invalid-feedback"><?php echo $errors['role']; ?></div><?php endif; ?>
        </div>
    </div>
    <div id="parent-linking-section" class="card mt-4" style="display: none;">
        <div class="card-header">
            <h5>Link Children to Parent</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="student_search" class="form-label">Search for a student to link:</label>
                <input type="text" class="form-control" id="student_search" placeholder="Start typing student name...">
                <div id="student-search-results" class="list-group mt-2"></div>
            </div>
            <h6>Students to be linked:</h6>
            <ul id="students-to-link-list" class="list-group">
                <!-- Selected students will be added here by JavaScript -->
            </ul>
            <input type="hidden" name="linked_student_ids" id="linked_student_ids">
        </div>
    </div>

    <button type="submit" class="btn btn-primary mt-4">Create User</button>
    <a href="users.php" class="btn btn-secondary mt-4">Cancel</a>
</form>

<?php
$conn->close();
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.querySelector('select[name="role"]');
    const parentLinkingSection = document.getElementById('parent-linking-section');
    const studentSearchInput = document.getElementById('student_search');
    const studentSearchResults = document.getElementById('student-search-results');
    const studentsToLinkList = document.getElementById('students-to-link-list');
    const linkedStudentIdsInput = document.getElementById('linked_student_ids');

    let linkedStudentIds = [];

    // Show/hide the linking section based on role
    roleSelect.addEventListener('change', function() {
        if (this.value === 'parent') {
            parentLinkingSection.style.display = 'block';
        } else {
            parentLinkingSection.style.display = 'none';
        }
    });

    // Student search logic
    studentSearchInput.addEventListener('input', async function() {
        const query = this.value.trim();
        studentSearchResults.innerHTML = '';
        if (query.length < 2) return;

        try {
            const response = await fetch(`api_live_search.php?q=${query}&context=student`);
            const users = await response.json();

            if (users.length > 0) {
                users.forEach(user => {
                    if (user.role === 'student' && !linkedStudentIds.includes(user.id)) {
                        const userItem = document.createElement('a');
                        userItem.href = '#';
                        userItem.className = 'list-group-item list-group-item-action';
                        userItem.textContent = `${user.first_name} ${user.last_name} (ID: ${user.id})`;
                        userItem.addEventListener('click', function(e) {
                            e.preventDefault();
                            addStudentToLinkList(user.id, this.textContent);
                            studentSearchInput.value = '';
                            studentSearchResults.innerHTML = '';
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

    function addStudentToLinkList(id, name) {
        linkedStudentIds.push(id);

        const listItem = document.createElement('li');
        listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
        listItem.textContent = name;
        listItem.dataset.studentId = id;

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn-close';
        removeBtn.addEventListener('click', function() {
            const index = linkedStudentIds.indexOf(id);
            if (index > -1) {
                linkedStudentIds.splice(index, 1);
            }
            listItem.remove();
            updateHiddenInput();
        });

        listItem.appendChild(removeBtn);
        studentsToLinkList.appendChild(listItem);
        updateHiddenInput();
    }

    function updateHiddenInput() {
        linkedStudentIdsInput.value = linkedStudentIds.join(',');
    }

});
</script>
<?php
require_once 'includes/footer.php';
?>
