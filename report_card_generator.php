<?php
require_once 'config.php';

// Authorization check
$allowed_roles = ['admin', 'headteacher', 'root', 'teacher'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: " . login_url());
    exit;
}

// Fetch data for dropdowns
$classes_sql = "SELECT id, name FROM class_levels ORDER BY name ASC";
$classes_result = $conn->query($classes_sql);

$scales_sql = "SELECT id, name FROM grading_scales ORDER BY name ASC";
$scales_result = $conn->query($scales_sql);

// For simplicity, we'll use a hardcoded list of academic years and terms.
$academic_years = ["2024/2025", "2025/2026", "2026/2027"];
$terms = ["Term 1", "Term 2", "Term 3"];

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center my-4">
        <h2 class="text-primary"><i class="bi bi-file-earmark-person-fill me-2"></i>Generate Report Cards</h2>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            Report Card Options
        </div>
        <div class="card-body">
            <form action="<?php echo reports_url(); ?>" method="post" target="_blank">
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
                             <?php mysqli_data_seek($scales_result, 0); // Reset pointer for second loop ?>
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
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-secondary" formaction="<?php echo reports_url(); ?>" name="generate_old_report">
                        <i class="bi bi-file-earmark-pdf-fill me-2"></i>Generate Old Report Card (PDF)
                    </button>
                    <button type="submit" class="btn btn-primary" formaction="<?php echo competency_reports_url(); ?>" name="generate_new_report">
                        <i class="bi bi-award-fill me-2"></i>Generate New Competency-Based Report
                    </button>
                </div>
            </form>
        </div>
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
                if(classSelect.value) fetchStudents(classSelect.value);
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
            fetchStudents(classId);
        } else {
            studentSelect.innerHTML = '<option value="">Select a class first...</option>';
        }
    });

    function fetchStudents(classId) {
        studentSelect.disabled = true;
        studentSelect.innerHTML = '<option value="">Loading...</option>';
        const api_url = '<?php echo url("api/get_students_for_class"); ?>';

        fetch(`${api_url}?class_level_id=${classId}`)
            .then(response => response.json())
            .then(data => {
                studentSelect.innerHTML = '<option value="">Select Student...</option>';
                if(data.error) {
                    console.error(data.error);
                    studentSelect.innerHTML = '<option value="">Error loading students</option>';
                    return;
                }
                if(data.length > 0) {
                    data.forEach(student => {
                        const option = document.createElement('option');
                        option.value = student.id;
                        option.textContent = `${student.first_name} ${student.last_name}`;
                        studentSelect.appendChild(option);
                    });
                } else {
                    studentSelect.innerHTML = '<option value="">No students in this class</option>';
                }
                studentSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error fetching students:', error);
                studentSelect.innerHTML = '<option value="">Failed to load students</option>';
            });
    }
});
</script>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
