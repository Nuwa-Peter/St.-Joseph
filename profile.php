<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Fetch current user's data from the database
$user_id = $_SESSION['id'];
$stmt = $conn->prepare("SELECT first_name, last_name, username, email, role, photo FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    // Should not happen if user is logged in, but as a safeguard
    echo "Error: User not found.";
    exit;
}

// Password change logic
$password_err = "";
$success_message = "";
$photo_err = "";

// Handle Photo Upload
if (isset($_POST['upload_photo'])) {
    $authorized_roles = ['teacher', 'librarian', 'root', 'bursar', 'headteacher'];
    if (in_array($_SESSION['role'], $authorized_roles)) {
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['profile_photo']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            if (in_array(strtolower($filetype), $allowed)) {
                // Generate a unique name for the file
                $new_filename = 'user_' . $user_id . '_' . uniqid() . '.' . $filetype;
                $target_path = 'uploads/photos/' . $new_filename;

                if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_path)) {
                    // Delete old photo if it exists
                    if (!empty($user['photo']) && file_exists($user['photo'])) {
                        unlink($user['photo']);
                    }
                    // Update database
                    $stmt_photo = $conn->prepare("UPDATE users SET photo = ? WHERE id = ?");
                    $stmt_photo->bind_param("si", $target_path, $user_id);
                    if ($stmt_photo->execute()) {
                        $success_message = "Profile photo updated successfully.";
                        // Refresh user data to show new photo immediately
                        $user['photo'] = $target_path;
                    } else {
                        $photo_err = "Failed to update database.";
                    }
                    $stmt_photo->close();
                } else {
                    $photo_err = "Failed to move uploaded file.";
                }
            } else {
                $photo_err = "Invalid file type. Please upload a JPG, PNG, or GIF.";
            }
        } else {
            $photo_err = "Error uploading file. Please try again.";
        }
    } else {
        $photo_err = "You are not authorized to perform this action.";
    }
}


if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    // Validate new password
    if (strlen(trim($new_password)) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } elseif ($new_password != $confirm_new_password) {
        $password_err = "New password and confirmation do not match.";
    } else {
        // Verify current password
        $stmt_pass = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt_pass->bind_param("i", $user_id);
        $stmt_pass->execute();
        $result_pass = $stmt_pass->get_result();
        $user_with_pass = $result_pass->fetch_assoc();
        $stmt_pass->close();

        if ($user_with_pass && password_verify($current_password, $user_with_pass['password'])) {
            // Current password is correct, update to new password
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt_update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt_update->bind_param("si", $hashed_new_password, $user_id);
            if ($stmt_update->execute()) {
                $success_message = "Your password has been changed successfully.";
            } else {
                $password_err = "Something went wrong. Please try again later.";
            }
            $stmt_update->close();
        } else {
            $password_err = "The current password you entered is not correct.";
        }
    }
}

require_once 'includes/header.php';
?>

<h2>User Profile</h2>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-body text-center">
                <?php if (!empty($user['photo']) && file_exists($user['photo'])): ?>
                    <img src="<?php echo htmlspecialchars($user['photo']); ?>" alt="Profile Photo" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                <?php else:
                    $name = $user["first_name"] . ' ' . $user["last_name"];
                    $initials = '';
                    $parts = explode(' ', $name);
                    foreach ($parts as $part) { $initials .= strtoupper(substr($part, 0, 1)); }
                ?>
                    <div class="avatar-initials mx-auto mb-3" style="width: 150px; height: 150px; font-size: 4rem;"><?php echo htmlspecialchars($initials); ?></div>
                <?php endif; ?>
                <h5 class="card-title"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h5>
                <p class="text-muted mb-1"><?php echo htmlspecialchars(ucfirst($user['role'])); ?></p>
                <p class="text-muted mb-4"><?php echo htmlspecialchars($user['email']); ?></p>

                <?php
                $authorized_roles = ['teacher', 'librarian', 'root', 'bursar', 'headteacher'];
                if (in_array($_SESSION['role'], $authorized_roles)):
                ?>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#photoUploadModal">
                        Change Photo
                    </button>
                <?php endif; ?>

            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-3"><h6 class="mb-0">Full Name</h6></div>
                    <div class="col-sm-9 text-secondary"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><h6 class="mb-0">Email</h6></div>
                    <div class="col-sm-9 text-secondary"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><h6 class="mb-0">Username</h6></div>
                    <div class="col-sm-9 text-secondary"><?php echo htmlspecialchars($user['username']); ?></div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><h6 class="mb-0">Role</h6></div>
                    <div class="col-sm-9 text-secondary"><?php echo htmlspecialchars(ucfirst($user['role'])); ?></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Change Password</div>
            <div class="card-body">
                <?php if($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
                <?php if($password_err): ?><div class="alert alert-danger"><?php echo $password_err; ?></div><?php endif; ?>

                <form action="profile.php" method="post">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_new_password" class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_new_password" class="form-control" required>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Photo Upload Modal -->
<div class="modal fade" id="photoUploadModal" tabindex="-1" aria-labelledby="photoUploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="profile.php" method="post" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="photoUploadModalLabel">Upload New Profile Photo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if(isset($_POST['upload_photo']) && $photo_err): ?><div class="alert alert-danger"><?php echo $photo_err; ?></div><?php endif; ?>
                    <p>Choose a new photo to upload. It will be cropped to a square.</p>
                    <div class="mb-3">
                        <label for="profile_photo" class="form-label">Select image:</label>
                        <input class="form-control" type="file" id="profile_photo" name="profile_photo" accept="image/*" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="upload_photo" class="btn btn-primary">Upload and Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
