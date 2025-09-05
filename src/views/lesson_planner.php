<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../src/includes/header.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Fetch class levels for the dropdown
$class_levels_sql = "SELECT id, name FROM class_levels ORDER BY name ASC";
$class_levels_result = $conn->query($class_levels_sql);

// Fetch subjects for the dropdown
$subjects_sql = "SELECT id, name FROM subjects ORDER BY name ASC";
$subjects_result = $conn->query($subjects_sql);

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>NCDC Curriculum Lesson Planner</h2>
</div>

<div class="card">
    <div class="card-header">
        Select Curriculum
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="class_level_id" class="form-label">Class Level</label>
                    <select id="class_level_id" class="form-select">
                        <option value="">Select Class Level...</option>
                        <?php while($class = $class_levels_result->fetch_assoc()): ?>
                            <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="subject_id" class="form-label">Subject</label>
                    <select id="subject_id" class="form-select">
                        <option value="">Select Subject...</option>
                         <?php while($subject = $subjects_result->fetch_assoc()): ?>
                            <option value="<?php echo $subject['id']; ?>"><?php echo htmlspecialchars($subject['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="topic_id" class="form-label">Topic</label>
                    <select id="topic_id" class="form-select" disabled>
                        <option value="">Select Class and Subject first...</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        Curriculum Details
    </div>
    <div class="card-body" id="curriculum-details-container">
        <p class="text-muted">Select a topic to view its details.</p>
    </div>
</div>


<?php
$conn->close();
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const classLevelSelect = document.getElementById('class_level_id');
    const subjectSelect = document.getElementById('subject_id');
    const topicSelect = document.getElementById('topic_id');
    const detailsContainer = document.getElementById('curriculum-details-container');

    function fetchTopics() {
        const classLevelId = classLevelSelect.value;
        const subjectId = subjectSelect.value;

        // Reset subsequent fields
        topicSelect.innerHTML = '<option value="">Loading...</option>';
        topicSelect.disabled = true;
        detailsContainer.innerHTML = '<p class="text-muted">Select a topic to view its details.</p>';

        if (!classLevelId || !subjectId) {
            topicSelect.innerHTML = '<option value="">Select Class and Subject first...</option>';
            return;
        }

        fetch(`api_get_curriculum_topics.php?class_level_id=${classLevelId}&subject_id=${subjectId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    topicSelect.innerHTML = `<option value="">Error: ${data.error}</option>`;
                    return;
                }
                if (data.length === 0) {
                    topicSelect.innerHTML = '<option value="">No topics found</option>';
                    return;
                }

                topicSelect.innerHTML = '<option value="">Select a Topic...</option>';
                data.forEach(topic => {
                    topicSelect.innerHTML += `<option value="${topic.id}">${topic.title}</option>`;
                });
                topicSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error fetching topics:', error);
                topicSelect.innerHTML = '<option value="">Failed to load topics</option>';
            });
    }

    // Add event listeners
    classLevelSelect.addEventListener('change', fetchTopics);
    subjectSelect.addEventListener('change', fetchTopics);

    topicSelect.addEventListener('change', function() {
        const topicId = this.value;
        detailsContainer.innerHTML = '<p class="text-muted">Loading details...</p>';

        if (!topicId) {
            detailsContainer.innerHTML = '<p class="text-muted">Select a topic to view its details.</p>';
            return;
        }

        fetch(`api_get_topic_details.php?topic_id=${topicId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    detailsContainer.innerHTML = `<p class="text-danger">Error: ${data.error}</p>`;
                    return;
                }

                let html = '<h4>Learning Outcomes</h4>';
                if (data.outcomes && data.outcomes.length > 0) {
                    html += '<ul>';
                    data.outcomes.forEach(outcome => {
                        html += `<li>${escapeHtml(outcome.outcome_text)}</li>`;
                    });
                    html += '</ul>';
                } else {
                    html += '<p>No learning outcomes specified.</p>';
                }

                html += '<h4 class="mt-4">Suggested Activities</h4>';
                if (data.activities && data.activities.length > 0) {
                    data.activities.forEach(activity => {
                        html += `<h5>${escapeHtml(activity.activity_title)}</h5>`;
                        html += `<p><strong>Instructions:</strong> ${escapeHtml(activity.instructions || '')}</p>`;
                        html += `<p><strong>Possible Responses:</strong> ${escapeHtml(activity.possible_responses || '')}</p>`;
                    });
                } else {
                    html += '<p>No activities specified.</p>';
                }

                detailsContainer.innerHTML = html;
            })
            .catch(error => {
                console.error('Error fetching topic details:', error);
                detailsContainer.innerHTML = '<p class="text-danger">Failed to load details.</p>';
            });
    });

    function escapeHtml(unsafe) {
        return unsafe
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }

});
</script>

<?php
require_once __DIR__ . '/../../src/includes/footer.php';
?>
