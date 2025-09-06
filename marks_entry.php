<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Authorization check
$allowed_roles = ['teacher', 'headteacher', 'root', 'admin'];
if (!isset($_SESSION["loggedin"]) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: " . login_url());
    exit;
}

$errors = [];
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['bulk_upload'])) {
    $file = $_FILES['marks_file']['tmp_name'] ?? null;
    $paper_id = $_POST['paper_id'] ?? null;
    $class_id = $_POST['class_id'] ?? null;

    if ($file && $paper_id && $class_id) {
        try {
            $spreadsheet = IOFactory::load($file);
            $worksheet = $spreadsheet->getActiveSheet();

            $conn->begin_transaction();

            $sql = "INSERT INTO marks (user_id, paper_id, stream_id, score, created_at, updated_at)
                    VALUES (?, ?, ?, ?, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE score = VALUES(score), updated_at = NOW()";
            $stmt = $conn->prepare($sql);

            $sql_stream = "SELECT stream_id FROM stream_user su JOIN streams s ON su.stream_id = s.id WHERE su.user_id = ? AND s.class_level_id = ? LIMIT 1";
            $stmt_stream = $conn->prepare($sql_stream);

            $firstRow = true;
            foreach ($worksheet->getRowIterator() as $row) {
                if ($firstRow) { $firstRow = false; continue; }

                $student_id = $worksheet->getCell('A' . $row->getRowIndex())->getValue();
                $score = $worksheet->getCell('C' . $row->getRowIndex())->getValue();

                if (empty($student_id)) continue;

                $stmt_stream->bind_param("ii", $student_id, $class_id);
                $stmt_stream->execute();
                $result_stream = $stmt_stream->get_result();
                if ($stream_row = $result_stream->fetch_assoc()) {
                    $stream_id = $stream_row['stream_id'];
                    $db_score = ($score !== '' && is_numeric($score)) ? (int)$score : null;
                    $stmt->bind_param("iiis", $student_id, $paper_id, $stream_id, $db_score);
                    $stmt->execute();
                }
            }

            $stmt->close();
            $stmt_stream->close();
            $conn->commit();
            $_SESSION['success_message'] = "Marks have been successfully imported.";

        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error_message'] = "An error occurred during import: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "File or context not provided correctly.";
    }
    header("Location: " . marks_entry_url());
    exit();
}

$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

$classes_sql = "SELECT id, name FROM class_levels ORDER BY name ASC";
$classes_result = $conn->query($classes_sql);

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center my-4">
        <h2 class="text-primary"><i class="bi bi-pencil-square me-2"></i>Marks Entry</h2>
    </div>

    <?php if($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
    <?php if($error_message): ?><div class="alert alert-danger"><?php echo $error_message; ?></div><?php endif; ?>

    <div class="card">
        <div class="card-header"><ul class="nav nav-tabs card-header-tabs" id="marksEntryTab" role="tablist"><li class="nav-item" role="presentation"><button class="nav-link active" id="online-tab" data-bs-toggle="tab" data-bs-target="#online-entry" type="button" role="tab" aria-controls="online-entry" aria-selected="true">Online Entry</button></li><li class="nav-item" role="presentation"><button class="nav-link" id="bulk-tab" data-bs-toggle="tab" data-bs-target="#bulk-import" type="button" role="tab" aria-controls="bulk-import" aria-selected="false">Bulk Import via Excel</button></li></ul></div>
        <div class="card-body">
            <div class="tab-content" id="marksEntryTabContent">
                <div class="tab-pane fade show active" id="online-entry" role="tabpanel" aria-labelledby="online-tab">
                    <h5 class="card-title">Step 1: Select Examination Context</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3"><label for="class_id_online" class="form-label">Class</label><select id="class_id_online" class="form-select"><option value="">Select Class...</option><?php mysqli_data_seek($classes_result, 0); while($class = $classes_result->fetch_assoc()): ?><option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option><?php endwhile; ?></select></div>
                        <div class="col-md-4 mb-3"><label for="subject_id_online" class="form-label">Subject</label><select id="subject_id_online" class="form-select" disabled><option>Select Class First...</option></select></div>
                        <div class="col-md-4 mb-3"><label for="paper_id_online" class="form-label">Exam</label><select id="paper_id_online" class="form-select" disabled><option>Select Subject First...</option></select></div>
                    </div>
                    <hr>
                    <h5 class="card-title">Step 2: Enter Marks</h5>
                    <div id="marks-entry-container"><p class="text-muted">Please select a class, subject, and exam to load the student list.</p></div>
                </div>
                <div class="tab-pane fade" id="bulk-import" role="tabpanel" aria-labelledby="bulk-tab">
                    <h5 class="card-title">Step 1: Select Examination Context</h5>
                     <div class="row">
                        <div class="col-md-4 mb-3"><label for="class_id_bulk" class="form-label">Class</label><select id="class_id_bulk" class="form-select"><option value="">Select Class...</option><?php mysqli_data_seek($classes_result, 0); while($class = $classes_result->fetch_assoc()): ?><option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option><?php endwhile; ?></select></div>
                        <div class="col-md-4 mb-3"><label for="subject_id_bulk" class="form-label">Subject</label><select id="subject_id_bulk" class="form-select" disabled><option>Select Class First...</option></select></div>
                        <div class="col-md-4 mb-3"><label for="paper_id_bulk" class="form-label">Exam</label><select id="paper_id_bulk" class="form-select" disabled><option>Select Subject First...</option></select></div>
                    </div>
                    <hr>
                    <h5 class="card-title">Step 2: Download & Upload</h5>
                    <p>Once you have selected the context, you can download a pre-filled template with the student list.</p>
                    <div class="mb-3"><a href="#" id="download-template-btn" class="btn btn-primary disabled"><i class="bi bi-file-earmark-arrow-down-fill me-2"></i>Download Template</a></div>
                    <form id="bulk-marks-form" action="<?php echo marks_entry_url(); ?>" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="class_id" id="hidden_class_id_bulk"><input type="hidden" name="subject_id" id="hidden_subject_id_bulk"><input type="hidden" name="paper_id" id="hidden_paper_id_bulk">
                        <div class="mb-3"><label for="marks_file" class="form-label">Upload Completed Template</label><input class="form-control" type="file" name="marks_file" id="marks_file" accept=".xlsx, .xls" disabled></div>
                        <button type="submit" name="bulk_upload" id="upload-marks-btn" class="btn btn-success" disabled>Upload Marks</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function setupDropdownLogic(context) {
        const classSelect = document.getElementById(`class_id_${context}`);
        const subjectSelect = document.getElementById(`subject_id_${context}`);
        const paperSelect = document.getElementById(`paper_id_${context}`);

        classSelect.addEventListener('change', function() {
            const classId = this.value;
            subjectSelect.disabled = true; paperSelect.disabled = true;
            subjectSelect.innerHTML = '<option value="">Loading...</option>';
            paperSelect.innerHTML = '<option value="">Select Subject First...</option>';
            if (context === 'online') document.getElementById('marks-entry-container').innerHTML = '<p class="text-muted">Please select a class, subject, and exam to load the student list.</p>';
            if (!classId) return;
            fetch(`<?php echo url('api/get_subjects_for_class'); ?>?class_level_id=${classId}`).then(response => response.json()).then(data => {
                subjectSelect.innerHTML = '<option value="">Select Subject...</option>';
                data.forEach(subject => { subjectSelect.innerHTML += `<option value="${subject.id}">${subject.name}</option>`; });
                subjectSelect.disabled = false;
            });
        });

        subjectSelect.addEventListener('change', function() {
            const subjectId = this.value;
            paperSelect.disabled = true;
            paperSelect.innerHTML = '<option value="">Loading...</option>';
            if (context === 'online') document.getElementById('marks-entry-container').innerHTML = '<p class="text-muted">Please select a class, subject, and exam to load the student list.</p>';
            if (!subjectId) return;
            fetch(`<?php echo url('api/get_papers_for_subject'); ?>?subject_id=${subjectId}`).then(response => response.json()).then(data => {
                paperSelect.innerHTML = '<option value="">Select Exam...</option>';
                data.forEach(paper => { paperSelect.innerHTML += `<option value="${paper.id}">${paper.name} (${paper.exam_type})</option>`; });
                paperSelect.disabled = false;
            });
        });

        if (context === 'online') {
            paperSelect.addEventListener('change', function() {
                const paperId = this.value;
                const classId = classSelect.value;
                const container = document.getElementById('marks-entry-container');
                container.innerHTML = '<p class="text-muted">Loading students...</p>';
                if (!paperId || !classId) { container.innerHTML = '<p class="text-muted">Please select a class, subject, and exam to load the student list.</p>'; return; }
                fetch(`<?php echo url('api/get_students_for_class'); ?>?class_id=${classId}&paper_id=${paperId}`).then(response => response.json()).then(data => {
                    if (data.error) { container.innerHTML = `<p class="text-danger">${data.error}</p>`; return; }
                    if (data.length === 0) { container.innerHTML = '<p class="text-muted">No students found for the selected class.</p>'; return; }
                    let formHtml = `<form id="online-marks-form"><input type="hidden" name="paper_id" value="${paperId}">`;
                    const table = document.createElement('table');
                    table.className = 'table table-striped';
                    table.innerHTML = `<thead><tr><th>Student Name</th><th style="width: 150px;">Score</th></tr></thead>`;
                    const tbody = document.createElement('tbody');
                    data.forEach(student => {
                        const row = tbody.insertRow();
                        const score = student.score !== null ? student.score : '';
                        row.innerHTML = `<td>${student.first_name} ${student.last_name}</td><td><input type="hidden" name="student_ids[]" value="${student.id}"><input type="hidden" name="stream_ids[]" value="${student.stream_id}"><input type="number" name="scores[]" class="form-control" value="${score}" min="0" max="100"></td>`;
                    });
                    table.appendChild(tbody);
                    container.innerHTML = '';
                    container.appendChild(table);
                    container.innerHTML += `<button type="submit" class="btn btn-primary">Save Marks</button>`;
                    document.getElementById('online-marks-form').addEventListener('submit', handleOnlineFormSubmit);
                });
            });
        } else {
            paperSelect.addEventListener('change', function() {
                const downloadBtn = document.getElementById('download-template-btn');
                const uploadInput = document.getElementById('marks_file');
                const uploadBtn = document.getElementById('upload-marks-btn');
                const classId = classSelect.value;
                const subjectId = subjectSelect.value;
                const paperId = this.value;
                document.getElementById('hidden_class_id_bulk').value = classId;
                document.getElementById('hidden_subject_id_bulk').value = subjectId;
                document.getElementById('hidden_paper_id_bulk').value = paperId;
                if (paperId && classId && subjectId) {
                    downloadBtn.href = `<?php echo marks_template_download_url(); ?>?class_id=${classId}&subject_id=${subjectId}&paper_id=${paperId}`;
                    downloadBtn.classList.remove('disabled');
                    uploadInput.disabled = false;
                    uploadBtn.disabled = false;
                } else {
                    downloadBtn.href = '#';
                    downloadBtn.classList.add('disabled');
                    uploadInput.disabled = true;
                    uploadBtn.disabled = true;
                }
            });
        }
    }

    function handleOnlineFormSubmit(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const submitButton = form.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
        fetch('<?php echo api_save_marks_url(); ?>', { method: 'POST', body: formData }).then(response => response.json()).then(data => {
            if (data.success) { alert('Marks saved successfully!'); }
            else { alert('Error saving marks: ' + (data.error || 'Unknown error')); }
        }).catch(error => { console.error('Error:', error); alert('An unexpected error occurred.');
        }).finally(() => { submitButton.disabled = false; submitButton.innerHTML = 'Save Marks'; });
    }

    setupDropdownLogic('online');
    setupDropdownLogic('bulk');
});
</script>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
