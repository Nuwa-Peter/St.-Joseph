<?php
// Include the main TCPDF library and autoloader.
require_once('vendor/autoload.php');
require_once('config.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {
    //Page header
    public function Header() {
        // Logo
        $image_file = K_PATH_IMAGES.'logo.png';
        $this->Image($image_file, 10, 10, 15, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', 'B', 20);
        // Title
        $this->Cell(0, 15, 'Student List', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('School Management System');
$pdf->SetTitle('Student List');
$pdf->SetSubject('List of Students');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// add a page
$pdf->AddPage();

// set font
$pdf->SetFont('helvetica', '', 10);

// -----------------------------------------------------------------------------

// Build the query based on filters
$filter_title = "All Students";
$sql = "
    SELECT
        u.first_name, u.last_name, u.lin, u.student_type,
        s.name AS stream_name, cl.name AS class_level_name
    FROM users u
    LEFT JOIN stream_user su ON u.id = su.user_id
    LEFT JOIN streams s ON su.stream_id = s.id
    LEFT JOIN class_levels cl ON s.class_level_id = cl.id
    WHERE u.role = 'student'
";

if(isset($_GET['stream_id']) && !empty($_GET['stream_id'])){
    $stream_id = $_GET['stream_id'];
    $sql .= " AND s.id = ?";
    // We need to get the stream/class name for the title
    $title_sql = "SELECT s.name as stream_name, cl.name as class_name FROM streams s JOIN class_levels cl ON s.class_level_id = cl.id WHERE s.id = " . intval($stream_id);
    $title_result = $conn->query($title_sql);
    if($title_row = $title_result->fetch_assoc()){
        $filter_title = "Students of " . $title_row['class_name'] . " " . $title_row['stream_name'];
    }
} else if (isset($_GET['class_level_id']) && !empty($_GET['class_level_id'])){
    $class_level_id = $_GET['class_level_id'];
    $sql .= " AND cl.id = ?";
    // We need to get the class name for the title
    $title_sql = "SELECT name FROM class_levels WHERE id = " . intval($class_level_id);
    $title_result = $conn->query($title_sql);
    if($title_row = $title_result->fetch_assoc()){
        $filter_title = "Students of " . $title_row['name'];
    }
}

$sql .= " ORDER BY cl.name, s.name, u.last_name, u.first_name";

$stmt = $conn->prepare($sql);

if(isset($stream_id)){
    $stmt->bind_param("i", $stream_id);
} else if (isset($class_level_id)){
    $stmt->bind_param("i", $class_level_id);
}

$stmt->execute();
$result = $stmt->get_result();

// Set report title
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, $filter_title, 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('helvetica', '', 10);
// HTML content
$html = '<table border="1" cellpadding="4">
    <thead>
        <tr style="background-color:#cccccc;">
            <th width="5%">#</th>
            <th width="30%">Name</th>
            <th width="20%">LIN</th>
            <th width="30%">Class/Stream</th>
            <th width="15%">Type</th>
        </tr>
    </thead>
    <tbody>';

$count = 1;
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $html .= '<tr>';
        $html .= '<td width="5%">' . $count++ . '</td>';
        $html .= '<td width="30%">' . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . '</td>';
        $html .= '<td width="20%">' . htmlspecialchars($row['lin']) . '</td>';
        $html .= '<td width="30%">' . htmlspecialchars($row['class_level_name'] . ' ' . $row['stream_name']) . '</td>';
        $html .= '<td width="15%">' . htmlspecialchars(ucfirst($row['student_type'])) . '</td>';
        $html .= '</tr>';
    }
} else {
    $html .= '<tr><td colspan="5" style="text-align:center;">No students found.</td></tr>';
}

$html .= '</tbody></table>';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output('student_list.pdf', 'I');

?>
