<?php
session_start();
require_once 'config.php';
require_once 'includes/header.php';

// Ensure user is an admin
$admin_roles = ['root', 'headteacher'];
if (!isset($_SESSION["loggedin"]) || !in_array($_SESSION['role'], $admin_roles)) {
    header("location: dashboard.php");
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Loop through POST data and update settings
    foreach ($_POST as $key => $value) {
        // Simple validation: ensure key is not empty
        if (!empty($key)) {
            $stmt = $conn->prepare("UPDATE school_settings SET setting_value = ? WHERE setting_key = ?");
            // Use 's' for both as setting_value is TEXT
            $stmt->bind_param("ss", $value, $key);
            $stmt->execute();
            $stmt->close();
        }
    }
    $_SESSION['message'] = "Settings updated successfully.";
    // Redirect to the same page to see the changes
    header("location: school_settings.php");
    exit;
}

// Fetch all settings from the database
$settings = [];
$sql = "SELECT setting_key, setting_value FROM school_settings";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

$conn->close();
?>

<div class="container-fluid">
    <h2 class="my-4">School Settings</h2>

    <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <i class="bi bi-gear-fill me-2"></i>Manage General School Settings
        </div>
        <div class="card-body">
            <form action="school_settings.php" method="post">
                <div class="row">
                    <?php foreach ($settings as $key => $value): ?>
                        <div class="col-md-6 mb-3">
                            <label for="<?php echo htmlspecialchars($key); ?>" class="form-label text-capitalize"><?php echo str_replace('_', ' ', htmlspecialchars($key)); ?></label>
                            <?php if (strlen($value) > 255): ?>
                                <textarea class="form-control" id="<?php echo htmlspecialchars($key); ?>" name="<?php echo htmlspecialchars($key); ?>" rows="3"><?php echo htmlspecialchars($value); ?></textarea>
                            <?php else: ?>
                                <input type="text" class="form-control" id="<?php echo htmlspecialchars($key); ?>" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <hr>
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
