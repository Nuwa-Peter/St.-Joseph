<?php
require_once 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Determine which user profile to display
$user_id_to_display = $_GET['id'] ?? $_SESSION['id'];
$is_own_profile = ($user_id_to_display == $_SESSION['id']);

// Fetch user's data from the database
$stmt = $conn->prepare("SELECT id, first_name, last_name, username, email, role, photo FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id_to_display);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    // Handle user not found
    require_once 'includes/header.php';
    echo "<div class='container'><div class='alert alert-danger mt-4'>User not found.</div></div>";
    require_once 'includes/footer.php';
    exit;
}

// Password change logic
$password_err = "";
$success_message = "";
$photo_err = "";

// Handle Photo Upload
if (isset($_POST['upload_photo'])) {
    verify_csrf_token();
    $authorized_roles = ['teacher', 'librarian', 'root', 'bursar', 'headteacher'];
    if (in_array($_SESSION['role'], $authorized_roles)) {

        $image_data = null;
        $file_extension = null;

        // Check for webcam data first
        if (!empty($_POST['cropped_photo_data'])) {
            // data:image/png;base64,iVBORw0KGgo...
            if (preg_match('/^data:image\/(\w+);base64,/', $_POST['cropped_photo_data'], $type)) {
                $data = substr($_POST['cropped_photo_data'], strpos($_POST['cropped_photo_data'], ',') + 1);
                $image_data = base64_decode($data);
                $file_extension = strtolower($type[1]); // png, jpeg etc.
            } else {
                $photo_err = "Invalid image data format.";
            }
        }
        // Fallback to standard file upload
        elseif (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['profile_photo']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            if (in_array(strtolower($filetype), $allowed)) {
                $image_data = file_get_contents($_FILES['profile_photo']['tmp_name']);
                $file_extension = strtolower($filetype);
            } else {
                $photo_err = "Invalid file type. Please upload a JPG, PNG, or GIF.";
            }
        }

        // If we have image data, process it
        if ($image_data !== null && $file_extension !== null) {
            // Generate a unique name for the file
            $new_filename = 'user_' . $user_id_to_display . '_' . uniqid() . '.' . $file_extension;
            $target_path = 'uploads/photos/' . $new_filename;

            if (file_put_contents($target_path, $image_data)) {
                // Delete old photo if it exists
                if (!empty($user['photo']) && file_exists($user['photo'])) {
                    unlink($user['photo']);
                }
                // Update database
                $stmt_photo = $conn->prepare("UPDATE users SET photo = ? WHERE id = ?");
                $stmt_photo->bind_param("si", $target_path, $user_id_to_display);
                if ($stmt_photo->execute()) {
                    $success_message = "Profile photo updated successfully.";
                    // Refresh user data to show new photo immediately
                    $user['photo'] = $target_path;
                } else {
                    $photo_err = "Failed to update database.";
                }
                $stmt_photo->close();
            } else {
                $photo_err = "Failed to save the uploaded file.";
            }
        }
        // If no data was submitted and no other error was set, set a generic error.
        elseif (empty($photo_err)) {
             $photo_err = "No photo was submitted or an error occurred during upload.";
        }

    } else {
        $photo_err = "You are not authorized to perform this action.";
    }
}


if (isset($_POST['change_password'])) {
    verify_csrf_token();
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
        $stmt_pass->bind_param("i", $user_id_to_display);
        $stmt_pass->execute();
        $result_pass = $stmt_pass->get_result();
        $user_with_pass = $result_pass->fetch_assoc();
        $stmt_pass->close();

        if ($user_with_pass && password_verify($current_password, $user_with_pass['password'])) {
            // Current password is correct, update to new password
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt_update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt_update->bind_param("si", $hashed_new_password, $user_id_to_display);
            if ($stmt_update->execute()) {
                $success_message = "Your password has been changed successfully.";

                // --- Send notification to admins ---
                $admin_roles_to_notify = ['root', 'director', 'headteacher'];
                $admin_ids = [];
                $sql_admins = "SELECT id FROM users WHERE role IN ('" . implode("','", $admin_roles_to_notify) . "')";
                $result_admins = $conn->query($sql_admins);
                while($row = $result_admins->fetch_assoc()) {
                    $admin_ids[] = $row['id'];
                }

                if(!empty($admin_ids)) {
                    $user_name = $_SESSION['name'];
                    $message = "User '" . $user_name . "' changed their own password.";
                    $link = "profile.php?id=" . $_SESSION['id'];
                    $notify_sql = "INSERT INTO app_notifications (user_id, message, link) VALUES (?, ?, ?)";
                    $notify_stmt = $conn->prepare($notify_sql);
                    foreach($admin_ids as $admin_id) {
                        $notify_stmt->bind_param("iss", $admin_id, $message, $link);
                        $notify_stmt->execute();
                    }
                    $notify_stmt->close();
                }
                // --- End notification ---

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

<?php
// Display success or error messages
if (isset($_GET['update_success'])) {
    echo "<div class='alert alert-success'>Profile updated successfully.</div>";
}
if (isset($_SESSION['profile_errors'])) {
    foreach ($_SESSION['profile_errors'] as $error) {
        echo "<div class='alert alert-danger'>".htmlspecialchars($error)."</div>";
    }
    unset($_SESSION['profile_errors']); // Clear errors after displaying
}
?>

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

                <?php if ($is_own_profile): ?>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#photoUploadModal">
                        <i class="bi bi-upload me-1"></i> Upload Photo
                    </button>
                     <button type="button" class="btn btn-outline-secondary" id="webcam-button">
                        <i class="bi bi-camera-video me-1"></i> Take Photo
                    </button>
                    <input type="hidden" name="cropped_photo_data" id="cropped-photo-data">
                <?php endif; ?>

            </div>
        </div>
    </div>
    <div class="col-md-8">
        <form action="update_profile.php" method="post">
            <?php echo csrf_input(); ?>
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-3"><h6 class="mb-0">Full Name</h6></div>
                        <div class="col-sm-9 text-secondary"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                    </div>
                    <hr>
                    <div class="row align-items-center">
                        <div class="col-sm-3"><h6 class="mb-0">Email</h6></div>
                        <div class="col-sm-9 text-secondary">
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" <?php echo !$is_own_profile ? 'readonly' : ''; ?>>
                        </div>
                    </div>
                    <hr>
                    <div class="row align-items-center">
                        <div class="col-sm-3"><h6 class="mb-0">Username</h6></div>
                        <div class="col-sm-9 text-secondary">
                            <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" <?php echo !$is_own_profile ? 'readonly' : ''; ?>>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-3"><h6 class="mb-0">Role</h6></div>
                        <div class="col-sm-9 text-secondary"><?php echo htmlspecialchars(ucfirst($user['role'])); ?></div>
                    </div>
                    <?php if ($is_own_profile): ?>
                    <hr>
                    <div class="row">
                        <div class="col-sm-12">
                            <button type="submit" name="update_profile" class="btn btn-info">Update Profile</button>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <?php if ($is_own_profile): ?>
        <div class="card">
            <div class="card-header">Change Password</div>
            <div class="card-body">
                <?php if($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
                <?php if($password_err): ?><div class="alert alert-danger"><?php echo $password_err; ?></div><?php endif; ?>

                <form action="profile.php" method="post">
                    <?php echo csrf_input(); ?>
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
        <?php endif; ?>
    </div>
</div>

<!-- Photo Upload Modal -->
<div class="modal fade" id="photoUploadModal" tabindex="-1" aria-labelledby="photoUploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="profile.php" method="post" enctype="multipart/form-data">
                <?php echo csrf_input(); ?>
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

<!-- Webcam & Cropper Modals -->
<div class="modal fade" id="webcamModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Capture Photo</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><video id="webcam-video" width="100%" autoplay></video></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-primary" id="capture-button">Capture</button></div></div></div></div>
<div class="modal fade" id="cropperModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Crop Image</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><div><img id="image-to-crop" src="" style="max-width: 100%;"></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-primary" id="crop-button">Crop & Use</button></div></div></div></div>


<!-- Hidden Form for Webcam/Cropped Photo -->
<form id="webcam-photo-form" action="profile.php" method="post" style="display: none;">
    <?php echo csrf_input(); ?>
    <input type="hidden" name="cropped_photo_data" id="hidden-cropped-photo-data">
    <input type="hidden" name="upload_photo" value="1">
</form>

<?php
$conn->close();
require_once 'includes/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if the user is authorized to see the webcam button
    const webcamButton = document.getElementById('webcam-button');
    if (!webcamButton) return; // Exit if button doesn't exist

    const webcamModal = new bootstrap.Modal(document.getElementById('webcamModal'));
    const cropperModal = new bootstrap.Modal(document.getElementById('cropperModal'));
    const video = document.getElementById('webcam-video');
    const captureButton = document.getElementById('capture-button');
    const imageToCrop = document.getElementById('image-to-crop');
    const cropButton = document.getElementById('crop-button');
    const hiddenPhotoForm = document.getElementById('webcam-photo-form');
    const hiddenPhotoDataInput = document.getElementById('hidden-cropped-photo-data');
    let stream;
    let cropper;

    webcamButton.addEventListener('click', async () => {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: true });
            video.srcObject = stream;
            webcamModal.show();
        } catch (err) {
            console.error("Error accessing webcam:", err);
            alert("Could not access the webcam. Please ensure you have a webcam connected and have granted permission.");
        }
    });

    captureButton.addEventListener('click', () => {
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
        imageToCrop.src = canvas.toDataURL('image/png');

        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }

        webcamModal.hide();
        cropperModal.show();
    });

    document.getElementById('cropperModal').addEventListener('shown.bs.modal', () => {
        cropper = new Cropper(imageToCrop, { aspectRatio: 1, viewMode: 1 });
    });

    document.getElementById('cropperModal').addEventListener('hidden.bs.modal', () => {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
    });

    cropButton.addEventListener('click', () => {
        if (cropper) {
            const canvas = cropper.getCroppedCanvas({ width: 400, height: 400 });
            hiddenPhotoDataInput.value = canvas.toDataURL('image/png');
            hiddenPhotoForm.submit();
        }
    });

    document.getElementById('webcamModal').addEventListener('hidden.bs.modal', () => {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
    });
});
</script>
