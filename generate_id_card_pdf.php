<?php
session_start();
require_once 'config.php';
require_once 'vendor/autoload.php';
require_once 'includes/qrcode_generator.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    exit('Unauthorized');
}

// --- Get POST data ---
$role = $_POST['role'] ?? null;
$scope = $_POST['generation_scope'] ?? 'all';
$user_id = $_POST['user_id'] ?? null;
$issue_date = $_POST['issue_date'] ?? null;
$expiry_date = $_POST['expiry_date'] ?? null;

if (!$role || !$issue_date || !$expiry_date) {
    exit('Missing required parameters.');
}

// --- Get User IDs ---
$user_ids = [];
if ($scope === 'individual' && $user_id) {
    $user_ids[] = $user_id;
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

// --- Update database with new issue/expiry dates ---
$stmt_update = $conn->prepare("UPDATE users SET id_card_issue_date = ?, id_card_expiry_date = ? WHERE id = ?");
foreach ($user_ids as $uid) {
    $stmt_update->bind_param("ssi", $issue_date, $expiry_date, $uid);
    $stmt_update->execute();
}
$stmt_update->close();

// --- PDF Generation ---

class IDCardPDF extends TCPDF {
    public function Header() {}
    public function Footer() {}
}

// CR80 size in mm: 85.60 x 53.98
$cr80_width = 85.6;
$cr80_height = 53.98;
$page_format = [$cr80_width, $cr80_height];

$pdf = new IDCardPDF('P', 'mm', $page_format, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('St. Joseph\'s VSS');
$pdf->SetMargins(3, 3, 3);
$pdf->SetAutoPageBreak(false);

foreach ($user_ids as $uid) {
    // Fetch user data
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
    $user = $stmt_user->get_result()->fetch_assoc();
    $stmt_user->close();

    // --- FRONT SIDE ---
    $pdf->AddPage('P', $page_format);

    // BG and Border
    $pdf->SetFillColor(240, 248, 255); // AliceBlue
    $pdf->Rect(0, 0, $cr80_width, $cr80_height, 'F');
    $pdf->SetDrawColor(0, 0, 128); // Navy
    $pdf->SetLineWidth(0.5);
    $pdf->Rect(1, 1, $cr80_width - 2, $cr80_height - 2);

    // Header
    $pdf->Image('images/logo.png', 4, 4, 12, 12, 'PNG');
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 5, 'St. Joseph\'s VSS', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 7);
    $pdf->Cell(0, 3, 'Student ID Card', 0, 1, 'C');

    // Photo
    $photo_path = $user['photo'] ?? null;
    if ($photo_path && file_exists($photo_path)) {
        $pdf->Image($photo_path, 32, 15, 22, 22, 'JPG');
    } else {
        $placeholder = ($user['gender'] === 'Female') ? 'images/placeholder_female.png' : 'images/placeholder_male.png';
        if (file_exists($placeholder)) $pdf->Image($placeholder, 32, 15, 22, 22, 'PNG');
    }

    // Details
    $pdf->SetY(38);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(0, 4, htmlspecialchars($user['first_name'] . ' ' . $user['last_name']), 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 6.5);
    $y_pos = 42;
    $details = [
        'ID' => $user['unique_id'] ?? 'N/A',
        'LIN' => $user['lin'] ?? 'N/A',
        'Class' => ($user['class_name'] ?? '') . ' ' . ($user['stream_name'] ?? ''),
        'DOB' => $user['date_of_birth'] ?? 'N/A',
    ];
    foreach($details as $label => $value) {
        $pdf->SetXY(5, $y_pos);
        $pdf->SetFont('helvetica', 'B', 6.5);
        $pdf->Cell(10, 3, $label.':', 0, 0);
        $pdf->SetFont('helvetica', '', 6.5);
        $pdf->Cell(0, 3, htmlspecialchars(trim($value)), 0, 1);
        $y_pos += 3;
    }


    // --- BACK SIDE ---
    $pdf->AddPage('P', $page_format);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Rect(0, 0, $cr80_width, $cr80_height, 'F');
    $pdf->SetDrawColor(0, 0, 128);
    $pdf->SetLineWidth(0.5);
    $pdf->Rect(1, 1, $cr80_width - 2, $cr80_height - 2);

    // QR Code
    $qr_data = 'http://localhost/new/user_view.php?id=' . $user['id']; // Example URL
    $qr_code_generator = new QRCode($qr_data, ['s' => 'qrl']);
    ob_start();
    $qr_code_generator->output_image();
    $qr_image_data = ob_get_clean();
    $pdf->Image('@' . $qr_image_data, 28, 5, 30, 30, 'PNG');

    // Dates
    $pdf->SetY(38);
    $pdf->SetFont('helvetica', '', 7);
    $pdf->Cell(0, 4, 'Issue Date: ' . htmlspecialchars($issue_date), 0, 1, 'C');
    $pdf->Cell(0, 4, 'Expiry Date: ' . htmlspecialchars($expiry_date), 0, 1, 'C');

    // Other info
    $pdf->SetY(45);
    $pdf->SetFont('helvetica', 'I', 6);
    $pdf->MultiCell(0, 5, "If found, please return to St. Joseph's VSS.\nThis card remains the property of the school.", 0, 'C');
}

$conn->close();
$pdf->Output('id_cards.pdf', 'I');
exit;
?>
