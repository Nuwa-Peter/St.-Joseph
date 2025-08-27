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
$conn->close();

// --- PDF Generation using TCPDF ---

class MYPDF extends TCPDF {
    // Page header
    public function Header() {
        $this->SetFont('helvetica', 'B', 16);
        $this->Cell(0, 15, 'Requisitions Report', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }
    // Page footer
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('School Management System');
$pdf->SetTitle('Requisitions Report');
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, 'Requisitions Report', 'Generated on: ' . date('Y-m-d H:i:s'));
$pdf->setFooterData(array(0,64,0), array(0,64,128));
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
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
