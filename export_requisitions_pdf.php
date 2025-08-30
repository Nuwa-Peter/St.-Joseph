<?php
session_start();
require_once 'config.php';
require_once 'vendor/autoload.php'; // Assuming TCPDF is installed via Composer

// Authorization
$admin_roles = ['bursar', 'headteacher', 'root'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $admin_roles)) {
    die("Unauthorized Access");
}

// Get requisitions data
$requisition_id = $_GET['id'] ?? null;
$filter_status = $_GET['status'] ?? '';

$sql = "SELECT r.id, r.item_name, r.quantity, r.total_price, r.status, r.created_at,
               CONCAT(u.first_name, ' ', u.last_name) as requester_name
        FROM requisitions r
        JOIN users u ON r.user_id = u.id";

$params = [];
$types = '';

if ($requisition_id) {
    $sql .= " WHERE r.id = ?";
    $params[] = $requisition_id;
    $types .= 'i';
} elseif (!empty($filter_status)) {
    $sql .= " WHERE r.status = ?";
    $params[] = $filter_status;
    $types .= 's';
}
$sql .= " ORDER BY r.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$requisitions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch school settings using the same connection
$school_settings = [];
$settings_sql = "SELECT setting_key, setting_value FROM school_settings";
if ($settings_result = $conn->query($settings_sql)) {
    while ($row = $settings_result->fetch_assoc()) {
        $school_settings[$row['setting_key']] = $row['setting_value'];
    }
}

$conn->close(); // Now close the single connection


// --- PDF Generation using TCPDF ---

class MYPDF extends TCPDF {
    public $school_settings;

    // Page header
    public function Header() {
        // Logo
        $logo_path = $this->school_settings['school_logo_path'] ?? 'images/logo.png';
        if (file_exists($logo_path)) {
            $this->Image($logo_path, 15, 10, 25, 25, 'PNG');
        }

        // School Info
        $this->SetFont('helvetica', 'B', 16);
        $this->SetTextColor(0, 64, 128); // #004080
        $this->Cell(0, 8, $this->school_settings['school_name'] ?? 'School Name', 0, 1, 'C', 0, '', 0, false, 'T', 'M');

        $this->SetFont('helvetica', '', 9);
        $this->SetTextColor(0, 0, 0);
        $contact_info = "TEL: " . ($this->school_settings['school_tel'] ?? '') . " | Email: " . ($this->school_settings['school_email'] ?? '');
        $this->Cell(0, 6, $contact_info, 0, 1, 'C');

        $this->SetFont('helvetica', 'I', 10);
        $this->SetTextColor(0, 86, 179); // #0056b3
        $this->Cell(0, 6, '"' . ($this->school_settings['school_motto'] ?? '') . '"', 0, 1, 'C');

        // Line break
        $this->Ln(5);

        // Draw a double line
        $this->SetLineStyle(array('width' => 0.5, 'color' => array(0, 64, 128)));
        $this->Line(15, $this->GetY(), $this->getPageWidth() - 15, $this->GetY());
        $this->Ln(0.5);
        $this->SetLineStyle(array('width' => 0.2, 'color' => array(0, 64, 128)));
        $this->Line(15, $this->GetY(), $this->getPageWidth() - 15, $this->GetY());

        // Title
        $this->Ln(5);
        $this->SetFont('helvetica', 'B', 14);
        $this->SetTextColor(0, 64, 128);
        $this->Cell(0, 10, 'Requisitions Report', 0, 1, 'C');
    }

    // Page footer
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->school_settings = $school_settings; // Pass settings to the PDF object

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('School Management System');
$pdf->SetTitle('Requisitions Report');
// The Header() method now handles all header data, so SetHeaderData is not needed.
$pdf->setFooterData(array(0,64,0), array(0,64,128));
$pdf->SetMargins(PDF_MARGIN_LEFT, 45, PDF_MARGIN_RIGHT); // Increase top margin for new header
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 9);

// Create HTML table
$html = '<table border="1" cellpadding="4">
            <thead>
                <tr style="background-color:#cccccc; font-weight:bold;">
                    <th>ID</th>
                    <th>Date</th>
                    <th>Requested By</th>
                    <th>Item</th>
                    <th>Qty</th>
                    <th align="right">Total (UGX)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>';

if (!empty($requisitions)) {
    foreach ($requisitions as $req) {
        $html .= '<tr>
                    <td>'.$req['id'].'</td>
                    <td>'.date("d-M-Y", strtotime($req['created_at'])).'</td>
                    <td>'.htmlspecialchars($req['requester_name']).'</td>
                    <td>'.htmlspecialchars($req['item_name']).'</td>
                    <td>'.$req['quantity'].'</td>
                    <td align="right">'.number_format($req['total_price'], 2).'</td>
                    <td>'.ucfirst($req['status']).'</td>
                  </tr>';
    }
} else {
    $html .= '<tr><td colspan="7" align="center">No requisitions found.</td></tr>';
}

$html .= '</tbody></table>';

// Write HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// Close and output PDF document
$pdf->Output('requisitions_report.pdf', 'I');
?>
