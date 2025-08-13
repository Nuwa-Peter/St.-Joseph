<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();

// Fetch all streams to create sheets
$streams_sql = "SELECT s.name as stream_name, cl.name as class_name FROM streams s JOIN class_levels cl ON s.class_level_id = cl.id ORDER BY cl.name, s.name";
$streams_result = $conn->query($streams_sql);

$sheetIndex = 0;
while($stream = $streams_result->fetch_assoc()) {
    $sheetName = $stream['class_name'] . ' ' . $stream['stream_name'];
    // Sanitize sheet name (max 31 chars, no invalid chars)
    $safeSheetName = substr(preg_replace('/[\\\\?*\\[\\]:\\/]/', '', $sheetName), 0, 31);

    if ($sheetIndex == 0) {
        // Use the first sheet that's already there
        $spreadsheet->getActiveSheet()->setTitle($safeSheetName);
    } else {
        // Create a new sheet
        $spreadsheet->createSheet()->setTitle($safeSheetName);
    }
    $spreadsheet->setActiveSheetIndex($sheetIndex);

    // Add headers
    $headers = ['First Name', 'Last Name', 'Username', 'LIN', 'Parent Phone', 'Date of Birth (YYYY-MM-DD)', 'Gender (Male/Female)', 'Student Type (day/boarding)'];
    $spreadsheet->getActiveSheet()->fromArray($headers, NULL, 'A1');

    // Style header
    $headerStyle = [
        'font' => ['bold' => true],
        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF00']],
    ];
    $spreadsheet->getActiveSheet()->getStyle('A1:H1')->applyFromArray($headerStyle);

    // Set column widths
    foreach(range('A','H') as $col) {
        $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
    }

    $sheetIndex++;
}

// If no streams, create a default empty sheet
if ($sheetIndex == 0) {
    $spreadsheet->getActiveSheet()->setTitle('Students');
    $headers = ['First Name', 'Last Name', 'Username', 'LIN', 'Parent Phone', 'Date of Birth (YYYY-MM-DD)', 'Gender (Male/Female)', 'Student Type (day/boarding)'];
    $spreadsheet->getActiveSheet()->fromArray($headers, NULL, 'A1');
}

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$spreadsheet->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Xlsx)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="student_import_template.xlsx"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;

?>
