<?php
require_once 'config.php';

// Authorization check must be at the very top.
$admin_roles = ['root', 'headteacher', 'admin'];
if (!isset($_SESSION["loggedin"]) || !in_array($_SESSION['role'], $admin_roles)) {
    header("location: " . dashboard_url());
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Loop through all posted data and update settings
    foreach ($_POST as $key => $value) {
        // Basic security: ensure the key is not empty and is a valid setting key
        if (!empty($key) && preg_match('/^[a-z_]+$/', $key)) {
            $stmt = $conn->prepare("UPDATE school_settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->bind_param("ss", $value, $key);
            $stmt->execute();
            $stmt->close();
        }
    }
    $_SESSION['success_message'] = "Settings updated successfully.";
    header("location: " . settings_url());
    exit;
}

// Fetch all settings from the database for display
$settings = [];
$sql = "SELECT setting_key, setting_value FROM school_settings";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

// Fetch session messages
$success_message = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <h2 class="my-4"><i class="bi bi-gear-wide-connected me-2"></i>School Settings</h2>

    <?php if(!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header">
            <i class="bi bi-gear-fill me-2"></i>Manage General School Settings
        </div>
        <div class="card-body">
            <form action="<?php echo settings_url(); ?>" method="post">
                <div class="row">
                    <?php foreach ($settings as $key => $value):
                        $label = str_replace('_', ' ', $key);
                        if ($key === 'school_po_box') {
                            $label = 'Post Office Box Number';
                        }
                    ?>
                        <div class="col-md-6 mb-3">
                            <label for="<?php echo htmlspecialchars($key); ?>" class="form-label text-capitalize"><?php echo htmlspecialchars($label); ?></label>
                            <?php if (strlen($value) > 255 || strpos($value, "\n") !== false): ?>
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
$conn->close();
require_once 'includes/footer.php';
?>
