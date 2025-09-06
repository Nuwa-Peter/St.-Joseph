<?php
require_once 'config.php';
require_once 'includes/csrf_helper.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: " . login_url());
    exit;
}

// Determine which user profile to display
$user_id_to_display = $_GET['id'] ?? $_SESSION['id'];
$is_own_profile = ($user_id_to_display == $_SESSION['id']);

// --- Handle POST Requests ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // All actions on this page require a valid CSRF token
    verify_csrf_token();

    // --- Handle Profile Update ---
    if (isset($_POST['update_profile']) && $is_own_profile) {
        $email = trim($_POST['email']);
        $username = trim($_POST['username']);
        // Add validation logic here...
        $stmt = $conn->prepare("UPDATE users SET email = ?, username = ? WHERE id = ?");
        $stmt->bind_param("ssi", $email, $username, $_SESSION['id']);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Profile updated successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to update profile.";
        }
        $stmt->close();
        header("Location: " . profile_url());
        exit();
    }

    // --- Handle Photo Upload ---
    if (isset($_POST['upload_photo'])) {
        $photo_err = "";
        $image_data = null;
        $file_extension = null;

        if (!empty($_POST['cropped_photo_data'])) {
            if (preg_match('/^data:image\/(\w+);base64,/', $_POST['cropped_photo_data'], $type)) {
                $data = substr($_POST['cropped_photo_data'], strpos($_POST['cropped_photo_data'], ',') + 1);
                $image_data = base64_decode($data);
                $file_extension = strtolower($type[1]);
            } else {
                $photo_err = "Invalid image data format.";
            }
        } elseif (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filetype = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
            if (in_array(strtolower($filetype), $allowed)) {
                $image_data = file_get_contents($_FILES['profile_photo']['tmp_name']);
                $file_extension = strtolower($filetype);
            } else {
                $photo_err = "Invalid file type.";
            }
        }

        if ($image_data !== null && $file_extension !== null) {
            $new_filename = 'user_' . $user_id_to_display . '_' . uniqid() . '.' . $file_extension;
            $target_dir = 'uploads/photos/';
            if(!is_dir($target_dir)) mkdir($target_dir, 0755, true);
            $target_path = $target_dir . $new_filename;

            if (file_put_contents($target_path, $image_data)) {
                $stmt_old_photo = $conn->prepare("SELECT photo FROM users WHERE id = ?");
                $stmt_old_photo->bind_param("i", $user_id_to_display);
                $stmt_old_photo->execute();
                $old_photo = $stmt_old_photo->get_result()->fetch_assoc()['photo'];
                $stmt_old_photo->close();

                if (!empty($old_photo) && file_exists($old_photo)) {
                    unlink($old_photo);
                }

                $stmt_photo = $conn->prepare("UPDATE users SET photo = ? WHERE id = ?");
                $stmt_photo->bind_param("si", $target_path, $user_id_to_display);
                if ($stmt_photo->execute()) {
                    $_SESSION['success_message'] = "Profile photo updated successfully.";
                } else {
                    $_SESSION['error_message'] = "Failed to update database.";
                }
                $stmt_photo->close();
            } else {
                $_SESSION['error_message'] = "Failed to save the uploaded file.";
            }
        } elseif (empty($photo_err)) {
             $_SESSION['error_message'] = "No photo was submitted or an error occurred.";
        } else {
            $_SESSION['error_message'] = $photo_err;
        }
        header("Location: " . profile_url(['id' => $user_id_to_display]));
        exit();
    }

    // --- Handle Password Change ---
    if (isset($_POST['change_password']) && $is_own_profile) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_new_password = $_POST['confirm_new_password'];

        if (strlen(trim($new_password)) < 6) {
            $_SESSION['error_message'] = "Password must have at least 6 characters.";
        } elseif ($new_password != $confirm_new_password) {
            $_SESSION['error_message'] = "New password and confirmation do not match.";
        } else {
            $stmt_pass = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt_pass->bind_param("i", $user_id_to_display);
            $stmt_pass->execute();
            $user_with_pass = $stmt_pass->get_result()->fetch_assoc();
            $stmt_pass->close();

            if ($user_with_pass && password_verify($current_password, $user_with_pass['password'])) {
                $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt_update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt_update->bind_param("si", $hashed_new_password, $user_id_to_display);
                if ($stmt_update->execute()) {
                    $_SESSION['success_message'] = "Your password has been changed successfully.";
                } else {
                    $_SESSION['error_message'] = "Something went wrong. Please try again later.";
                }
                $stmt_update->close();
            } else {
                $_SESSION['error_message'] = "The current password you entered is not correct.";
            }
        }
        header("Location: " . profile_url());
        exit();
    }
}

// --- Fetch user data for display ---
$stmt = $conn->prepare("SELECT id, first_name, last_name, username, email, role, photo FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id_to_display);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    $_SESSION['error_message'] = "User not found.";
    header("Location: " . dashboard_url());
    exit;
}

// Fetch session messages
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <h2>User Profile</h2>

    <?php if ($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
    <?php if ($error_message): ?><div class="alert alert-danger"><?php echo $error_message; ?></div><?php endif; ?>

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
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#photoUploadModal"><i class="bi bi-upload me-1"></i> Upload</button>
                         <button type="button" class="btn btn-outline-secondary" id="webcam-button"><i class="bi bi-camera-video me-1"></i> Camera</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <form action="<?php echo profile_url(); ?>" method="post">
                <?php echo csrf_input(); ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row align-items-center mb-3">
                            <div class="col-sm-3"><h6 class="mb-0">Full Name</h6></div>
                            <div class="col-sm-9 text-secondary"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                        </div>
                        <div class="row align-items-center mb-3">
                            <div class="col-sm-3"><h6 class="mb-0">Email</h6></div>
                            <div class="col-sm-9 text-secondary">
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" <?php echo !$is_own_profile ? 'readonly' : ''; ?>>
                            </div>
                        </div>
                        <div class="row align-items-center mb-3">
                            <div class="col-sm-3"><h6 class="mb-0">Username</h6></div>
                            <div class="col-sm-9 text-secondary">
                                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" <?php echo !$is_own_profile ? 'readonly' : ''; ?>>
                            </div>
                        </div>
                        <div class="row align-items-center">
                            <div class="col-sm-3"><h6 class="mb-0">Role</h6></div>
                            <div class="col-sm-9 text-secondary"><?php echo htmlspecialchars(ucfirst($user['role'])); ?></div>
                        </div>
                        <?php if ($is_own_profile): ?>
                        <hr>
                        <button type="submit" name="update_profile" class="btn btn-info">Update Profile</button>
                        <?php endif; ?>
                    </div>
                </div>
            </form>

            <?php if ($is_own_profile): ?>
            <div class="card">
                <div class="card-header">Change Password</div>
                <div class="card-body">
                    <form action="<?php echo profile_url(); ?>" method="post">
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
</div>

<!-- Photo Upload Modal -->
<div class="modal fade" id="photoUploadModal" tabindex="-1" aria-labelledby="photoUploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo profile_url(['id' => $user_id_to_display]); ?>" method="post" enctype="multipart/form-data">
                <?php echo csrf_input(); ?>
                <div class="modal-header"><h5 class="modal-title" id="photoUploadModalLabel">Upload New Photo</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body">
                    <p>Choose a new photo to upload. It will be cropped to a square.</p>
                    <div class="mb-3"><label for="profile_photo" class="form-label">Select image:</label><input class="form-control" type="file" id="profile_photo" name="profile_photo" accept="image/*" required></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" name="upload_photo" class="btn btn-primary">Upload</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Webcam & Cropper Modals -->
<div class="modal fade" id="webcamModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Capture Photo</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><video id="webcam-video" width="100%" autoplay></video></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-primary" id="capture-button">Capture</button></div></div></div></div>
<div class="modal fade" id="cropperModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Crop Image</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><div><img id="image-to-crop" src="" style="max-width: 100%;"></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-primary" id="crop-button">Crop & Use</button></div></div></div></div>

<!-- Hidden Form for Webcam/Cropped Photo -->
<form id="webcam-photo-form" action="<?php echo profile_url(['id' => $user_id_to_display]); ?>" method="post" style="display: none;">
    <?php echo csrf_input(); ?>
    <input type="hidden" name="cropped_photo_data" id="hidden-cropped-photo-data">
    <input type="hidden" name="upload_photo" value="1">
</form>

<?php require_once 'includes/footer.php'; ?>

<script src="<?php echo url('assets/libs/cropperjs/cropper.min.js'); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const webcamButton = document.getElementById('webcam-button');
    if (!webcamButton) return;

    const webcamModalEl = document.getElementById('webcamModal');
    const cropperModalEl = document.getElementById('cropperModal');
    if (!webcamModalEl || !cropperModalEl) return;

    const webcamModal = new bootstrap.Modal(webcamModalEl);
    const cropperModal = new bootstrap.Modal(cropperModalEl);
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
            alert("Could not access the webcam. Please ensure you have a webcam connected and have granted permission.");
        }
    });

    captureButton.addEventListener('click', () => {
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
        imageToCrop.src = canvas.toDataURL('image/png');
        if (stream) stream.getTracks().forEach(track => track.stop());
        webcamModal.hide();
        cropperModal.show();
    });

    cropperModalEl.addEventListener('shown.bs.modal', () => {
        cropper = new Cropper(imageToCrop, { aspectRatio: 1, viewMode: 1 });
    });

    cropperModalEl.addEventListener('hidden.bs.modal', () => {
        if (cropper) cropper.destroy();
        cropper = null;
    });

    cropButton.addEventListener('click', () => {
        if (cropper) {
            hiddenPhotoDataInput.value = cropper.getCroppedCanvas({ width: 400, height: 400 }).toDataURL('image/png');
            hiddenPhotoForm.submit();
        }
    });

    webcamModalEl.addEventListener('hidden.bs.modal', () => {
        if (stream) stream.getTracks().forEach(track => track.stop());
    });
});
</script>
