<?php
session_start();
require_once 'config.php';
require_once 'vendor/autoload.php';
require_once 'includes/qrcode_generator.php';

if (!isset($_SESSION["loggedin"]) || !$_SESSION["loggedin"] === true) {
    exit('Unauthorized');
}

// --- Helper Functions ---
function drawStudentCard(TCPDF $pdf, array $user, array $settings, string $issue_date, string $expiry_date) {
    $pdf->AddPage('L', [$settings['card_width_mm'], $settings['card_height_mm']]);

    // Left Blue Panel
    $pdf->SetFillColor(21, 66, 132);
    $pdf->Rect(0, 0, 28, $settings['card_height_mm'], 'F');

    // School Logo
    if (!empty($settings['school_logo_path']) && file_exists($settings['school_logo_path'])) {
        $pdf->Image($settings['school_logo_path'], 8, 5, 18, 18, '', '', 'T', false, 300, '', false, false, 1, false, false, false);
    }

    // QR Code
    $qr_data = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/student_view.php?id=' . $user['id'];
    $qr_code_generator = new QRCode($qr_data, ['s' => 'qrl', 'e' => 'H']);
    $qr_image_resource = $qr_code_generator->render_image();
    ob_start();
    imagepng($qr_image_resource);
    $qr_image_data = ob_get_clean();
    imagedestroy($qr_image_resource);
    $pdf->Image('@' . $qr_image_data, 6, 28, 20, 20, 'PNG');

    // Right Panel
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetTextColor(21, 66, 132);
    $pdf->SetXY(30, 4);
    $pdf->Cell(54, 5, strtoupper($settings['school_name'] ?? 'SCHOOL NAME'), 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 7);
    $pdf->SetTextColor(119, 119, 119);
    $pdf->SetXY(30, 8);
    $pdf->Cell(54, 5, 'STUDENT IDENTIFICATION CARD', 0, 1, 'C');

    // Photo
    if (!empty($user['photo']) && file_exists($user['photo'])) {
        $pdf->Image($user['photo'], 31, 14, 25, 30, '', '', 'T', false, 300, '', false, false, 1, false, false, false);
    } else {
        $placeholder = ($user['gender'] === 'Female') ? 'images/placeholder_female.png' : 'images/placeholder_male.png';
        $pdf->Image($placeholder, 31, 14, 25, 30, 'PNG', '', 'T', false, 300, '', false, false, 0);
    }

    // Details
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetTextColor(51, 51, 51);
    $pdf->SetXY(58, 16);
    $pdf->Cell(25, 5, 'Name:', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 7);
    $pdf->SetXY(58, 20);
    $pdf->MultiCell(25, 8, $user['first_name'] . ' ' . $user['last_name'], 0, 'L');

    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetXY(58, 28);
    $pdf->Cell(10, 5, 'ID No:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 7);
    $pdf->Cell(15, 5, $user['unique_id'] ?? 'N/A', 0, 1, 'L');

    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetXY(58, 32);
    $pdf->Cell(10, 5, 'Class:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 7);
    $pdf->Cell(15, 5, trim(($user['class_name'] ?? '') . ' ' . ($user['stream_name'] ?? '')), 0, 1, 'L');

    // Footer Dates
    $pdf->SetFont('helvetica', '', 5);
    $pdf->SetTextColor(85, 85, 85);
    $pdf->SetY(-8);
    $pdf->Cell(25, 5, 'Issued: ' . $issue_date, 0, 0, 'L');
    $pdf->Cell(0, 5, 'Expires: ' . $expiry_date, 0, 1, 'R');
}

function drawStaffCard(TCPDF $pdf, array $user, array $settings, string $issue_date, string $expiry_date) {
    $pdf->AddPage('P', [$settings['card_height_mm'], $settings['card_width_mm']]);

    // Header
    $pdf->SetFillColor(13, 71, 161);
    $pdf->Rect(0, 0, $settings['card_height_mm'], 22, 'F');
    if (!empty($settings['school_logo_path']) && file_exists($settings['school_logo_path'])) {
        $pdf->Image($settings['school_logo_path'], 22, 3, 12, 12, '', '', 'T', false, 300, '', false, false, 1, false, false, false);
    }
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetY(16);
    $pdf->Cell(0, 5, strtoupper($settings['school_name'] ?? 'SCHOOL NAME'), 0, 1, 'C');

    // Photo
    if (!empty($user['photo']) && file_exists($user['photo'])) {
        $pdf->Image($user['photo'], 15, 25, 24, 24, 'JPG', '', 'T', true, 300, 'C', false, false, 1, false, false, false);
    } else {
        $placeholder = ($user['gender'] === 'Female') ? 'images/placeholder_female.png' : 'images/placeholder_male.png';
        $pdf->Image($placeholder, 15, 25, 24, 24, 'PNG', '', 'T', true, 300, 'C', false, false, 0);
    }

    // Details
    $pdf->SetY(52);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetTextColor(51, 51, 51);
    $pdf->Cell(0, 5, strtoupper($user['first_name'] . ' ' . $user['last_name']), 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(13, 71, 161);
    $pdf->Cell(0, 5, strtoupper($user['role']), 0, 1, 'C');

    // Footer
    $pdf->SetY(-15);
    $pdf->SetFont('helvetica', '', 7);
    $pdf->Cell(0, 4, 'ID: ' . ($user['unique_id'] ?? 'N/A'), 0, 1, 'C');
    $pdf->Cell(0, 4, 'Issued: ' . $issue_date . ' | Expires: ' . $expiry_date, 0, 1, 'C');
}

// --- Main ---

// Fetch School Settings
$settings = [];
$settings_sql = "SELECT setting_key, setting_value FROM school_settings";
$settings_result = $conn->query($settings_sql);
if ($settings_result) {
    while ($row = $settings_result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}
$settings = array_merge([
    'card_width_mm' => 85.6,
    'card_height_mm' => 53.98
], $settings);


// Get POST data
$role = $_POST['role'] ?? null;
$scope = $_POST['generation_scope'] ?? 'all';
$user_id_single = $_POST['user_id'] ?? null;
$issue_date = $_POST['issue_date'] ?? date('Y-m-d');
$expiry_date = $_POST['expiry_date'] ?? date('Y-m-d', strtotime('+1 year'));

if (!$role) {
    exit('Missing role parameter.');
}

// Get User IDs
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

// --- PDF Setup ---
$pdf = new TCPDF('', 'mm', '', true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor($settings['school_name'] ?? 'School');
$pdf->SetMargins(3, 3, 3);
$pdf->SetAutoPageBreak(false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// --- Loop and Generate ---
$stmt_update = $conn->prepare("UPDATE users SET id_card_issue_date = ?, id_card_expiry_date = ? WHERE id = ?");
$stmt_log = $conn->prepare("INSERT INTO id_card_logs (student_id, issued_by_user_id) VALUES (?, ?)");
$issued_by_user_id = $_SESSION['id'] ?? 0;

foreach ($user_ids as $uid) {
    // Update and Log
    $stmt_update->bind_param("ssi", $issue_date, $expiry_date, $uid);
    $stmt_update->execute();
    if ($role === 'student' && $issued_by_user_id > 0) {
        $stmt_log->bind_param("ii", $uid, $issued_by_user_id);
        $stmt_log->execute();
    }

    // Fetch user data
    $sql = "SELECT u.*, s.name as stream_name, cl.name as class_name FROM users u LEFT JOIN stream_user su ON u.id = su.user_id LEFT JOIN streams s ON su.stream_id = s.id LEFT JOIN class_levels cl ON s.class_level_id = cl.id WHERE u.id = ?";
    $stmt_user = $conn->prepare($sql);
    $stmt_user->bind_param("i", $uid);
    $stmt_user->execute();
    $user_data = $stmt_user->get_result()->fetch_assoc();
    $stmt_user->close();

    if ($user_data) {
        if ($user_data['role'] === 'student') {
            drawStudentCard($pdf, $user_data, $settings, $issue_date, $expiry_date);
        } else {
            drawStaffCard($pdf, $user_data, $settings, $issue_date, $expiry_date);
        }
    }
}

$stmt_update->close();
if ($stmt_log) $stmt_log->close();
$conn->close();

// --- Output PDF ---
$pdf->Output('id_cards.pdf', 'I');
exit;
