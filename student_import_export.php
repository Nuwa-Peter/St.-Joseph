<?php
session_start();
require_once 'config.php';
require_once 'includes/header.php';
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$errors = [];
$success_count = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['student_file'])) {
    $file = $_FILES['student_file']['tmp_name'];

    try {
        $spreadsheet = IOFactory::load($file);
        $conn->begin_transaction();

        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            $worksheet = $spreadsheet->getSheetByName($sheetName);

            // Get stream_id from sheet name
            $parts = explode(' ', $sheetName);
            $class_name = $parts[0];
            $stream_name = implode(' ', array_slice($parts, 1));

            $stmt_stream_id = $conn->prepare("SELECT s.id FROM streams s JOIN class_levels cl ON s.class_level_id = cl.id WHERE cl.name = ? AND s.name = ?");
            $stmt_stream_id->bind_param("ss", $class_name, $stream_name);
            $stmt_stream_id->execute();
            $result_stream_id = $stmt_stream_id->get_result();
            if($result_stream_id->num_rows == 0) {
                continue; // Skip sheet if no matching stream is found
            }
            $stream_row = $result_stream_id->fetch_assoc();
            $stream_id = $stream_row['id'];
            $stmt_stream_id->close();

            $firstRow = true;
            foreach ($worksheet->getRowIterator() as $row) {
                if ($firstRow) { // Skip header row
                    $firstRow = false;
                    continue;
                }

                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(FALSE);
                $data = [];
                foreach ($cellIterator as $cell) {
                    $data[] = $cell->getValue();
                }

                // Assuming columns are in order: FName, LName, Username, LIN, Phone, DOB, Gender, Type
                list($first_name, $last_name, $username, $lin, $phone_number, $date_of_birth, $gender, $student_type) = $data;

                if(empty($username) || empty($first_name) || empty($last_name)) continue;

                // Create user
                $default_password = password_hash('password123', PASSWORD_DEFAULT);
                $role = 'student';
                $sql_user = "INSERT INTO users (first_name, last_name, username, lin, password, role, gender, phone_number, date_of_birth, student_type, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
                $stmt_user = $conn->prepare($sql_user);
                $stmt_user->bind_param("ssssssssss", $first_name, $last_name, $username, $lin, $default_password, $role, $gender, $phone_number, $date_of_birth, $student_type);

                if($stmt_user->execute()){
                    $new_user_id = $stmt_user->insert_id;
                    // Assign to stream
                    $sql_stream = "INSERT INTO stream_user (user_id, stream_id) VALUES (?, ?)";
                    $stmt_stream = $conn->prepare($sql_stream);
                    $stmt_stream->bind_param("ii", $new_user_id, $stream_id);
                    $stmt_stream->execute();
                    $stmt_stream->close();
                    $success_count++;
                }
                $stmt_user->close();
            }
        }
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        $errors[] = "An error occurred during import: " . $e->getMessage();
    }
}
?>

<h2>Import & Export Students</h2>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4>Import Students from Excel</h4>
            </div>
            <div class="card-body">
                <p>Upload an Excel file with student data. The file should have separate sheets for each stream (e.g., "S1 A", "S5 ARTS").</p>
                <p>
                    <a href="student_template_download.php" class="btn btn-primary">Download Excel Template</a>
                </p>
                <hr>
                <?php if($success_count > 0): ?>
                    <div class="alert alert-success"><?php echo $success_count; ?> students were successfully imported.</div>
                <?php endif; ?>
                <?php if(!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <form action="student_import_export.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="student_file" class="form-label">Select Excel File</label>
                        <input class="form-control" type="file" name="student_file" id="student_file" required accept=".xlsx, .xls">
                    </div>
                    <button type="submit" class="btn btn-success">Upload and Import</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4>Export Students to PDF</h4>
            </div>
            <div class="card-body">
                <p>Select filters to export a list of students to a PDF document.</p>
                <!-- PDF Export Form from students.php could be reused here -->
                 <form action="student_export_pdf.php" method="get" target="_blank">
                    <div class="modal-body">
                        <p>Select a filter for the PDF export. Leave blank to export all students.</p>
                        <div class="mb-3">
                            <label for="class_level_id" class="form-label">Filter by Class</label>
                            <select name="class_level_id" class="form-select">
                                <option value="">All Classes</option>
                                <?php
                                $class_sql = "SELECT id, name FROM class_levels ORDER BY name";
                                $class_result = $conn->query($class_sql);
                                while($class = $class_result->fetch_assoc()){
                                    echo "<option value='{$class['id']}'>".htmlspecialchars($class['name'])."</option>";
                                }
                                ?>
                            </select>
                        </div>
                         <div class="mb-3">
                            <label for="stream_id" class="form-label">Filter by Stream</label>
                            <select name="stream_id" class="form-select">
                                <option value="">All Streams</option>
                                 <?php
                                $stream_sql = "SELECT s.id, s.name, cl.name as class_name FROM streams s JOIN class_levels cl ON s.class_level_id = cl.id ORDER BY cl.name, s.name";
                                $stream_result = $conn->query($stream_sql);
                                while($stream = $stream_result->fetch_assoc()){
                                    echo "<option value='{$stream['id']}'>".htmlspecialchars($stream['class_name'] . ' ' . $stream['name'])."</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <small class="text-muted">Note: Filtering by stream will override class filter.</small>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Export PDF</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
