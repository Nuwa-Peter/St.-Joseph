<?php
require_once __DIR__ . '/../../config.php';
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$upload_errors = [];
$upload_success = '';

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
                if ($firstRow) { $firstRow = false; continue; } // Skip header

                $student_id = $worksheet->getCell('A' . $row->getRowIndex())->getValue();
                $score = $worksheet->getCell('C' . $row->getRowIndex())->getValue();

                if (empty($student_id)) continue;

                // Fetch stream_id for the student in the given class
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
            $upload_success = "Marks have been successfully imported.";

        } catch (Exception $e) {
            $conn->rollback();
            $upload_errors[] = "An error occurred during import: " . $e->getMessage();
        }
    } else {
        $upload_errors[] = "File or context not provided correctly.";
    }
}


require_once __DIR__ . '/../../src/includes/header.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// For a teacher, you would fetch only their classes. For admin, all classes.
// This is a simplified example assuming an admin is viewing.
$classes_sql = "SELECT id, name FROM class_levels ORDER BY name ASC";
$classes_result = $conn->query($classes_sql);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Marks Entry</h2>
</div>

<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" id="marksEntryTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="online-tab" data-bs-toggle="tab" data-bs-target="#online-entry" type="button" role="tab" aria-controls="online-entry" aria-selected="true">Online Entry</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="bulk-tab" data-bs-toggle="tab" data-bs-target="#bulk-import" type="button" role="tab" aria-controls="bulk-import" aria-selected="false">Bulk Import via Excel</button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="marksEntryTabContent">
            <!-- Online Entry Tab -->
            <div class="tab-pane fade show active" id="online-entry" role="tabpanel" aria-labelledby="online-tab">
                <h5 class="card-title">Step 1: Select Examination Context</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="class_id_online" class="form-label">Class</label>
                        <select id="class_id_online" class="form-select">
                            <option value="">Select Class...</option>
                            <?php mysqli_data_seek($classes_result, 0); while($class = $classes_result->fetch_assoc()): ?>
                                <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="subject_id_online" class="form-label">Subject</label>
                        <select id="subject_id_online" class="form-select" disabled><option>Select Class First...</option></select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="paper_id_online" class="form-label">Exam</label>
                        <select id="paper_id_online" class="form-select" disabled><option>Select Subject First...</option></select>
                    </div>
                </div>
                <hr>
                <h5 class="card-title">Step 2: Enter Marks</h5>
                <div id="marks-entry-container">
                    <!-- Student list will be loaded here via AJAX -->
                    <p class="text-muted">Please select a class, subject, and exam to load the student list.</p>
                </div>
            </div>

            <!-- Bulk Import Tab -->
            <div class="tab-pane fade" id="bulk-import" role="tabpanel" aria-labelledby="bulk-tab">
                <h5 class="card-title">Step 1: Select Examination Context</h5>
                 <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="class_id_bulk" class="form-label">Class</label>
                        <select id="class_id_bulk" class="form-select">
                            <option value="">Select Class...</option>
                            <?php mysqli_data_seek($classes_result, 0); while($class = $classes_result->fetch_assoc()): ?>
                                <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="subject_id_bulk" class="form-label">Subject</label>
                        <select id="subject_id_bulk" class="form-select" disabled><option>Select Class First...</option></select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="paper_id_bulk" class="form-label">Exam</label>
                        <select id="paper_id_bulk" class="form-select" disabled><option>Select Subject First...</option></select>
                    </div>
                </div>
                <hr>
                <h5 class="card-title">Step 2: Download & Upload</h5>
                <p>Once you have selected the context, you can download a pre-filled template with the student list.</p>

                <?php if(!empty($upload_errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach($upload_errors as $error): ?><p class="mb-0"><?php echo htmlspecialchars($error); ?></p><?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if($upload_success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($upload_success); ?></div>
                <?php endif; ?>

                <div class="mb-3">
                    <a href="#" id="download-template-btn" class="btn btn-primary disabled"><i class="bi bi-file-earmark-arrow-down-fill me-2"></i>Download Template</a>
                </div>
                <form id="bulk-marks-form" action="marks_entry.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="class_id" id="hidden_class_id_bulk">
                    <input type="hidden" name="subject_id" id="hidden_subject_id_bulk">
                    <input type="hidden" name="paper_id" id="hidden_paper_id_bulk">
                    <div class="mb-3">
                        <label for="marks_file" class="form-label">Upload Completed Template</label>
                        <input class="form-control" type="file" name="marks_file" id="marks_file" accept=".xlsx, .xls" disabled>
                    </div>
                    <button type="submit" name="bulk_upload" id="upload-marks-btn" class="btn btn-success" disabled>Upload Marks</button>
                </form>
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
            subjectSelect.disabled = true;
            paperSelect.disabled = true;
            subjectSelect.innerHTML = '<option value="">Loading...</option>';
            paperSelect.innerHTML = '<option value="">Select Subject First...</option>';
            if (context === 'online') document.getElementById('marks-entry-container').innerHTML = '<p class="text-muted">Please select a class, subject, and exam to load the student list.</p>';

            if (!classId) return;

            fetch(`api_get_subjects_for_class.php?class_level_id=${classId}`)
                .then(response => response.json())
                .then(data => {
                    subjectSelect.innerHTML = '<option value="">Select Subject...</option>';
                    data.forEach(subject => {
                        subjectSelect.innerHTML += `<option value="${subject.id}">${subject.name}</option>`;
                    });
                    subjectSelect.disabled = false;
                });
        });

        subjectSelect.addEventListener('change', function() {
            const subjectId = this.value;
            paperSelect.disabled = true;
            paperSelect.innerHTML = '<option value="">Loading...</option>';
            if (context === 'online') document.getElementById('marks-entry-container').innerHTML = '<p class="text-muted">Please select a class, subject, and exam to load the student list.</p>';

            if (!subjectId) return;

            fetch(`api_get_papers_for_subject.php?subject_id=${subjectId}`)
                .then(response => response.json())
                .then(data => {
                    paperSelect.innerHTML = '<option value="">Select Exam...</option>';
                    data.forEach(paper => {
                        paperSelect.innerHTML += `<option value="${paper.id}">${paper.name} (${paper.exam_type})</option>`;
                    });
                    paperSelect.disabled = false;
                });
        });

        if (context === 'online') {
            paperSelect.addEventListener('change', function() {
                const paperId = this.value;
                const classId = classSelect.value;
                const container = document.getElementById('marks-entry-container');
                container.innerHTML = '<p class="text-muted">Loading students...</p>';

                if (!paperId || !classId) {
                    container.innerHTML = '<p class="text-muted">Please select a class, subject, and exam to load the student list.</p>';
                    return;
                }

                fetch(`api_get_students_for_class.php?class_id=${classId}&paper_id=${paperId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            container.innerHTML = `<p class="text-danger">${data.error}</p>`;
                            return;
                        }
                        if (data.length === 0) {
                            container.innerHTML = '<p class="text-muted">No students found for the selected class.</p>';
                            return;
                        }

                        let formHtml = '<form id="online-marks-form">';
                        formHtml += '<input type="hidden" name="paper_id" value="' + paperId + '">';

                        const table = document.createElement('table');
                        table.className = 'table table-striped';
                        table.innerHTML = `<thead><tr><th>Student Name</th><th style="width: 150px;">Score</th></tr></thead>`;

                        const tbody = document.createElement('tbody');
                        data.forEach(student => {
                            const row = tbody.insertRow();
                            const score = student.score !== null ? student.score : '';
                            row.innerHTML = `
                                <td>${student.first_name} ${student.last_name}</td>
                                <td>
                                    <input type="hidden" name="student_ids[]" value="${student.id}">
                                    <input type="hidden" name="stream_ids[]" value="${student.stream_id}">
                                    <input type="number" name="scores[]" class="form-control" value="${score}" min="0" max="100">
                                </td>
                            `;
                        });
                        table.appendChild(tbody);

                        container.innerHTML = '';
                        container.appendChild(table);
                        container.innerHTML += `<button type="submit" class="btn btn-primary">Save Marks</button>`;

                        document.getElementById('online-marks-form').addEventListener('submit', handleOnlineFormSubmit);
                    });
            });
        } else { // context === 'bulk'
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
                    const href = `marks_template_download.php?class_id=${classId}&subject_id=${subjectId}&paper_id=${paperId}`;
                    downloadBtn.href = href;
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
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';

        fetch('api_save_marks.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Marks saved successfully!');
            } else {
                alert('Error saving marks: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An unexpected error occurred.');
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = 'Save Marks';
        });
    }

    setupDropdownLogic('online');
    setupDropdownLogic('bulk');
});
</script>

<?php
$conn->close();
require_once __DIR__ . '/../../src/includes/footer.php';
?>
