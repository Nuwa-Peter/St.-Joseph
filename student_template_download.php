<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$spreadsheet->removeSheetByIndex(0); // Remove the default sheet

// Fetch all streams with their class names
$sql = "
    SELECT s.name as stream_name, cl.name as class_name
    FROM streams s
    JOIN class_levels cl ON s.class_level_id = cl.id
    ORDER BY cl.name, s.name
";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $sheet_name = $row['class_name'] . ' ' . $row['stream_name'];
        // Sanitize sheet name (max 31 chars, no invalid chars)
        $safe_sheet_name = substr(preg_replace('/[\\\\\/?*\[\]:]/', '', $sheet_name), 0, 31);

        // Create a new worksheet for each stream
        $worksheet = $spreadsheet->createSheet();
        $worksheet->setTitle($safe_sheet_name);

        // Define headers
        $headers = [
            'First Name (Required)',
            'Last Name (Required)',
            'Username (Auto-generated if blank)',
            'LIN (Learner ID Number)',
            'Parent/Guardian Phone',
            'Date of Birth (YYYY-MM-DD)',
            'Gender (Male/Female)',
            'Student Type (Day/Boarding)'
        ];

        // Write headers to the first row
        $worksheet->fromArray($headers, NULL, 'A1');

        // Style the header row
        $header_style = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']]
        ];
        $worksheet->getStyle('A1:H1')->applyFromArray($header_style);

        // Auto-size columns for better readability
        foreach (range('A', 'H') as $col) {
            $worksheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
} else {
    // If no streams, create a default sheet with instructions
    $worksheet = $spreadsheet->createSheet();
    $worksheet->setTitle('Instructions');
    $worksheet->getCell('A1')->setValue('No streams found in the database. Please add classes and streams before using this template.');
}

$conn->close();

// Set headers to force download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="student_import_template.xlsx"');
header('Cache-Control: max-age=0');

// Create writer and output the file
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
