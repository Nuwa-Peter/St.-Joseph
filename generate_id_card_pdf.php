<?php
session_start();
require_once 'config.php';
require_once 'includes/qrcode_generator.php';

if (!isset($_SESSION["loggedin"]) || !$_SESSION["loggedin"] === true) {
    exit('Unauthorized');
}

// --- Get POST data ---
$role = $_POST['role'] ?? null;
$scope = $_POST['generation_scope'] ?? 'all';
$user_id_single = $_POST['user_id'] ?? null;
$issue_date = $_POST['issue_date'] ?? date('Y-m-d');
$expiry_date = $_POST['expiry_date'] ?? date('Y-m-d', strtotime('+1 year'));

if (!$role) {
    exit('Missing role parameter.');
}

// --- Fetch School Settings ---
$school_settings = [];
$settings_sql = "SELECT setting_key, setting_value FROM school_settings";
$settings_result = $conn->query($settings_sql);
if ($settings_result) {
    while ($row = $settings_result->fetch_assoc()) {
        $school_settings[$row['setting_key']] = $row['setting_value'];
    }
}

// --- Get User IDs ---
$user_ids = [];
if ($scope === 'individual' && $user_id_single) {
    $user_ids[] = $user_id_single;
} else {
    $stmt = $conn->prepare("SELECT id FROM users WHERE role = ?");
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $user_ids[] = $row['id'];
    }
    $stmt->close();
}

if (empty($user_ids)) {
    exit('No users found for the selected criteria.');
}

// --- Update database with new issue/expiry dates and Log ---
$stmt_update = $conn->prepare("UPDATE users SET id_card_issue_date = ?, id_card_expiry_date = ? WHERE id = ?");
$stmt_log = $conn->prepare("INSERT INTO id_card_logs (student_id, issued_by_user_id) VALUES (?, ?)");
$issued_by_user_id = $_SESSION['id'] ?? 0;

foreach ($user_ids as $uid) {
    // Update dates
    $stmt_update->bind_param("ssi", $issue_date, $expiry_date, $uid);
    $stmt_update->execute();

    // Log the ID card generation
    if ($role === 'student' && $issued_by_user_id > 0) {
        $stmt_log->bind_param("ii", $uid, $issued_by_user_id);
        $stmt_log->execute();
    }
}
$stmt_update->close();
if ($stmt_log) $stmt_log->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Generated ID Cards</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .no-print { display: none; }
            @page {
                size: A4;
                margin: 10mm;
            }
        }
        .print-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }
    </style>
</head>
<body>

<div class="container-fluid my-4 no-print">
    <div class="d-flex justify-content-between">
        <h2>Generated ID Cards</h2>
        <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer-fill me-2"></i>Print All Cards</button>
    </div>
    <hr>
</div>

<div class="print-container">
<?php
foreach ($user_ids as $uid) {
    // Fetch user data for the template
    $sql = "
        SELECT u.*, s.name as stream_name, cl.name as class_name
        FROM users u
        LEFT JOIN stream_user su ON u.id = su.user_id
        LEFT JOIN streams s ON su.stream_id = s.id
        LEFT JOIN class_levels cl ON s.class_level_id = cl.id
        WHERE u.id = ?
    ";
    $stmt_user = $conn->prepare($sql);
    $stmt_user->bind_param("i", $uid);
    $stmt_user->execute();
    $user_data = $stmt_user->get_result()->fetch_assoc();
    $stmt_user->close();

    if ($user_data) {
        // Prepare data for the template
        $user = $user_data;
        if (!empty($user['photo'])) {
            $user['photo_path'] = $user['photo'];
        } else {
            $user['photo_path'] = ($user['gender'] === 'Female') ? 'images/placeholder_female.png' : 'images/placeholder_male.png';
        }

        // Generate QR Code
        $qr_data = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/student_view.php?id=' . $user['id'];
        $qr_code_generator = new QRCode($qr_data, ['s' => 'qrl', 'e' => 'H']);
        ob_start();
        $qr_code_generator->output_image();
        $user['qr_code'] = ob_get_clean();

        // Include the HTML template based on role
        if ($user['role'] === 'student') {
            include 'id_card_template_student_landscape.php';
        } else {
            include 'id_card_template_staff_portrait.php';
        }
    }
}
$conn->close();
?>
</div>

</body>
</html>
