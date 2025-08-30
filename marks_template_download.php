<?php
require_once 'config.php';
require_once 'vendor/autoload.php';
session_start();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    exit('Unauthorized');
}

$class_id = $_GET['class_id'] ?? null;
$subject_id = $_GET['subject_id'] ?? null;
$paper_id = $_GET['paper_id'] ?? null;

if (!$class_id || !$subject_id || !$paper_id) {
    exit('Missing required parameters.');
}

// Fetch students for the given context
$sql = "
    SELECT u.id, u.first_name, u.last_name, s.name as stream_name, cl.name as class_name
    FROM users u
    JOIN stream_user su ON u.id = su.user_id
    JOIN streams s ON su.stream_id = s.id
    JOIN class_levels cl ON s.class_level_id = cl.id
    WHERE s.class_level_id = ? AND u.role = 'student'
    ORDER BY u.last_name, u.first_name
";

// We need to check if the subject is taught in at least one stream of the class
$check_sql = "SELECT COUNT(*) FROM stream_subject ss JOIN streams s ON ss.stream_id = s.id WHERE s.class_level_id = ? AND ss.subject_id = ?";
$stmt_check = $conn->prepare($check_sql);
$stmt_check->bind_param("ii", $class_id, $subject_id);
$stmt_check->execute();
if ($stmt_check->get_result()->fetch_row()[0] == 0) {
    exit('This subject is not taught in the selected class.');
}
$stmt_check->close();


$students = [];
$class_name_for_file = '';
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
        if (empty($class_name_for_file)) {
            $class_name_for_file = $row['class_name'];
        }
    }
    $stmt->close();
}

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Marks Entry');

// Define headers
$headers = ['Student ID', 'Student Name', 'Score'];
$sheet->fromArray($headers, NULL, 'A1');

// Write student data
$row_num = 2;
foreach ($students as $student) {
    $sheet->setCellValue('A' . $row_num, $student['id']);
    $sheet->setCellValue('B' . $row_num, $student['first_name'] . ' ' . $student['last_name']);
    // The Score column C is left empty for the user
    $row_num++;
}

// Style the header row
$header_style = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']]
];
$sheet->getStyle('A1:C1')->applyFromArray($header_style);

// Auto-size columns
$sheet->getColumnDimension('A')->setAutoSize(true);
$sheet->getColumnDimension('B')->setAutoSize(true);
$sheet->getColumnDimension('C')->setWidth(15);

// Create a safe filename
$safe_class_name = preg_replace('/[^a-zA-Z0-9_ -]/', '', $class_name_for_file);
$filename = "marks_template_{$safe_class_name}.xlsx";

// Set headers to force download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
$conn->close();
exit;
?>
