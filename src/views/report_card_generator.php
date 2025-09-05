<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../src/includes/header.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Fetch data for dropdowns
$classes_sql = "SELECT id, name FROM class_levels ORDER BY name ASC";
$classes_result = $conn->query($classes_sql);

$scales_sql = "SELECT id, name FROM grading_scales ORDER BY name ASC";
$scales_result = $conn->query($scales_sql);

// For simplicity, we'll use a hardcoded list of academic years and terms.
// In a real application, this might come from a settings table.
$academic_years = ["2024/2025", "2025/2026"];
$terms = ["Term 1", "Term 2", "Term 3"];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Generate Report Cards</h2>
</div>

<div class="card">
    <div class="card-header">Report Card Options</div>
    <div class="card-body">
        <form action="generate_report_pdf.php" method="post" target="_blank">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="academic_year" class="form-label">Academic Year</label>
                    <select name="academic_year" id="academic_year" class="form-select" required>
                        <?php foreach($academic_years as $year): ?>
                            <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="term" class="form-label">Term</label>
                    <select name="term" id="term" class="form-select" required>
                        <?php foreach($terms as $term): ?>
                            <option value="<?php echo $term; ?>"><?php echo $term; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="class_level_id" class="form-label">Class</label>
                    <select name="class_level_id" id="class_level_id" class="form-select" required>
                        <option value="">Select Class...</option>
                        <?php while($class = $classes_result->fetch_assoc()): ?>
                            <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="generation_scope" class="form-label">Generate For</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="generation_scope" id="scope_all" value="all" checked>
                        <label class="form-check-label" for="scope_all">All Students in Class</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="generation_scope" id="scope_individual" value="individual">
                        <label class="form-check-label" for="scope_individual">Individual Student</label>
                    </div>
                </div>
                <div class="col-md-6 mb-3" id="individual-student-container" style="display: none;">
                    <label for="student_id" class="form-label">Select Student</label>
                    <select name="student_id" id="student_id" class="form-select" disabled>
                        <option value="">Select a class first...</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="grading_scale_id" class="form-label">Grading Scale</label>
                    <select name="grading_scale_id" id="grading_scale_id" class="form-select" required>
                        <option value="">Select Grading Scale...</option>
                         <?php while($scale = $scales_result->fetch_assoc()): ?>
                            <option value="<?php echo $scale['id']; ?>"><?php echo htmlspecialchars($scale['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="next_term_begins" class="form-label">Next Term Begins On</label>
                    <input type="date" name="next_term_begins" id="next_term_begins" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="next_term_ends" class="form-label">Next Term Ends On</label>
                    <input type="date" name="next_term_ends" id="next_term_ends" class="form-control" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="class_teacher_remarks" class="form-label">Class Teacher's Remarks</label>
                    <textarea name="class_teacher_remarks" id="class_teacher_remarks" class="form-control" rows="3">A promising term, continue to work hard.</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="headteacher_remarks" class="form-label">Head Teacher's Remarks</label>
                    <textarea name="headteacher_remarks" id="headteacher_remarks" class="form-control" rows="3">A satisfactory performance. Keep up the good effort.</textarea>
                </div>
            </div>

            <hr>
            <button type="submit" class="btn btn-secondary" formaction="generate_report_pdf.php" name="generate_old_report">
                <i class="bi bi-file-earmark-pdf-fill me-2"></i>Generate Old Report Card (PDF)
            </button>
            <button type="submit" class="btn btn-primary" formaction="generate_competency_based_report.php" name="generate_new_report">
                <i class="bi bi-award-fill me-2"></i>Generate New Competency-Based Report
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const scopeRadios = document.querySelectorAll('input[name="generation_scope"]');
    const studentContainer = document.getElementById('individual-student-container');
    const studentSelect = document.getElementById('student_id');
    const classSelect = document.getElementById('class_level_id');

    scopeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'individual') {
                studentContainer.style.display = 'block';
                studentSelect.disabled = !classSelect.value;
            } else {
                studentContainer.style.display = 'none';
                studentSelect.disabled = true;
            }
        });
    });

    classSelect.addEventListener('change', function() {
        const classId = this.value;
        const scope = document.querySelector('input[name="generation_scope"]:checked').value;
        studentSelect.disabled = true;
        studentSelect.innerHTML = '<option value="">Loading...</option>';

        if (scope === 'individual' && classId) {
            // In a real implementation, this would be an AJAX call to fetch students for the class
            // fetch(`api_get_students_for_class.php?class_id=${classId}`)
            console.log('Fetching students for class:', classId);
            studentSelect.innerHTML = '<option value="">Select Student...</option><option value="1">Dummy Student A</option><option value="2">Dummy Student B</option>'; // Placeholder
            studentSelect.disabled = false;
        } else {
            studentSelect.innerHTML = '<option value="">Select a class first...</option>';
        }
    });
});
</script>

<?php
$conn->close();
require_once __DIR__ . '/../../src/includes/footer.php';
?>
