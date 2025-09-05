<?php
require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$errors = [];
$first_name = $last_name = $other_name = $email = $phone_number = $availability = $gender = $date_of_birth = "";
$subjects = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve form data
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $other_name = trim($_POST['other_name']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $availability = trim($_POST['availability']);
    $gender = trim($_POST['gender']);
    $date_of_birth = trim($_POST['date_of_birth']);
    $subjects_posted = isset($_POST['subjects']) ? $_POST['subjects'] : [];

    // Validate inputs
    if (empty($first_name)) $errors['first_name'] = "First name is required.";
    if (empty($last_name)) $errors['last_name'] = "Last name is required.";
    if (empty($email)) $errors['email'] = "Email is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Invalid email format.";
    if (empty($availability)) $errors['availability'] = "Availability is required.";
    if (empty($gender)) $errors['gender'] = "Gender is required.";

    // Check if email is already taken
    $sql_check = "SELECT id FROM users WHERE email = ?";
    if ($stmt_check = $conn->prepare($sql_check)) {
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows > 0) $errors['email'] = "This email is already taken.";
        $stmt_check->close();
    }

    // Remove duplicate subjects and empty values
    $subjects = array_filter(array_unique($subjects_posted));
    if (empty($subjects)) $errors['subjects'] = "At least one subject is required.";

    if (empty($errors)) {
        $conn->begin_transaction();
        try {
            // Auto-generate username and password
            $base_username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $first_name . '.' . $last_name));
            $username = $base_username;
            $i = 1;
            while(true) {
                $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ?");
                $stmt_check->bind_param("s", $username);
                $stmt_check->execute();
                $stmt_check->store_result();
                if($stmt_check->num_rows == 0) { $stmt_check->close(); break; }
                $stmt_check->close();
                $username = $base_username . $i++;
            }
            $default_password = password_hash('password123', PASSWORD_DEFAULT);
            $role = 'teacher';

            // Insert into users table
            $sql_user = "INSERT INTO users (first_name, last_name, other_name, username, email, password, role, gender, phone_number, date_of_birth, availability, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $stmt_user = $conn->prepare($sql_user);
            $stmt_user->bind_param("sssssssssss", $first_name, $last_name, $other_name, $username, $email, $default_password, $role, $gender, $phone_number, $date_of_birth, $availability);
            $stmt_user->execute();
            $new_teacher_id = $stmt_user->insert_id;
            $stmt_user->close();

            // Insert into subject_teacher table
            $sql_subject = "INSERT INTO subject_teacher (user_id, subject_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";
            $stmt_subject = $conn->prepare($sql_subject);
            foreach ($subjects as $subject_id) {
                $stmt_subject->bind_param("ii", $new_teacher_id, $subject_id);
                $stmt_subject->execute();
            }
            $stmt_subject->close();

            $conn->commit();
            header("location: teachers.php");
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $errors['db'] = "Database error: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../../src/includes/header.php';
?>

<?php
// Fetch subjects for dropdown
$subjects_sql = "SELECT id, name FROM subjects ORDER BY name ASC";
$subjects_result = $conn->query($subjects_sql);
$all_subjects = $subjects_result->fetch_all(MYSQLI_ASSOC);
?>

<h2>Add New Teacher</h2>
<form id="teacher-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div class="card">
        <div class="card-header">Teacher's Details</div>
        <div class="card-body">
            <?php if(isset($errors['db'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($errors['db']); ?></div><?php endif; ?>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" name="first_name" id="first_name" class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($first_name); ?>" required>
                    <?php if(isset($errors['first_name'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['first_name']); ?></div><?php endif; ?>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="last_name" class="form-label">Surname</label>
                    <input type="text" name="last_name" id="last_name" class="form-control <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($last_name); ?>" required>
                    <?php if(isset($errors['last_name'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['last_name']); ?></div><?php endif; ?>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="other_name" class="form-label">Other Name</label>
                    <input type="text" name="other_name" id="other_name" class="form-control" value="<?php echo htmlspecialchars($other_name); ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>" required>
                    <?php if(isset($errors['email'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div><?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input type="tel" name="phone_number" id="phone_number" class="form-control" value="<?php echo htmlspecialchars($phone_number); ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="gender" class="form-label">Gender</label>
                    <select name="gender" id="gender" class="form-select <?php echo isset($errors['gender']) ? 'is-invalid' : ''; ?>" required>
                        <option value="">Select Gender...</option>
                        <option value="Male" <?php echo ($gender == 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($gender == 'Female') ? 'selected' : ''; ?>>Female</option>
                    </select>
                    <?php if(isset($errors['gender'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['gender']); ?></div><?php endif; ?>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" id="date_of_birth" class="form-control" value="<?php echo htmlspecialchars($date_of_birth); ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="availability" class="form-label">Availability</label>
                    <select name="availability" id="availability" class="form-select <?php echo isset($errors['availability']) ? 'is-invalid' : ''; ?>" required>
                        <option value="">Select Availability...</option>
                        <option value="full-time" <?php echo ($availability == 'full-time') ? 'selected' : ''; ?>>Full-time</option>
                        <option value="part-time" <?php echo ($availability == 'part-time') ? 'selected' : ''; ?>>Part-time</option>
                    </select>
                     <?php if(isset($errors['availability'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['availability']); ?></div><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">Teachable Subjects</div>
        <div class="card-body">
            <?php if(isset($errors['subjects'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($errors['subjects']); ?></div><?php endif; ?>
            <div id="subjects-container">
                <!-- Subject dropdowns will be added here by JS -->
            </div>
            <button type="button" id="add-subject-btn" class="btn btn-secondary mt-2"><i class="bi bi-plus-circle me-2"></i>Add Subject</button>
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary">Create Teacher</button>
        <a href="teachers.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const subjectsContainer = document.getElementById('subjects-container');
    const addSubjectBtn = document.getElementById('add-subject-btn');
    const allSubjects = <?php echo json_encode($all_subjects); ?>;
    let subjectCount = 0;
    const MAX_SUBJECTS = 6;

    function createSubjectRow() {
        if (subjectCount >= MAX_SUBJECTS) {
            addSubjectBtn.disabled = true;
            return;
        }
        subjectCount++;

        const row = document.createElement('div');
        row.className = 'input-group mb-2';

        const select = document.createElement('select');
        select.name = 'subjects[]';
        select.className = 'form-select';
        select.required = true;

        let options = '<option value="">Select a subject...</option>';
        allSubjects.forEach(subject => {
            options += `<option value="${subject.id}">${subject.name}</option>`;
        });
        select.innerHTML = options;

        row.appendChild(select);

        if (subjectCount > 1) {
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-outline-danger';
            removeBtn.innerHTML = '<i class="bi bi-trash"></i>';
            removeBtn.onclick = function() {
                subjectsContainer.removeChild(row);
                subjectCount--;
                addSubjectBtn.disabled = false;
            };
            row.appendChild(removeBtn);
        }

        subjectsContainer.appendChild(row);
    }

    addSubjectBtn.addEventListener('click', createSubjectRow);

    // Add the first subject row initially
    createSubjectRow();
});
</script>

<?php
$conn->close();
require_once __DIR__ . '/../../src/includes/footer.php';
?>
