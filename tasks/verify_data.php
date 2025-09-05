<?php
require_once 'config.php';
require_once 'includes/header.php';

echo "<h2>Curriculum Data Verification</h2>";

// --- Query 1: Summary by Subject and Class Level ---
$summary_sql = "
    SELECT s.name as subject_name, cl.name as class_level_name, COUNT(t.id) as topic_count
    FROM curriculum_topics t
    JOIN subjects s ON t.subject_id = s.id
    JOIN class_levels cl ON t.class_level_id = cl.id
    GROUP BY s.name, cl.name
    ORDER BY s.name, cl.name;
";
$summary_result = $conn->query($summary_sql);

echo "<h3>Summary of Imported Topics</h3>";
if ($summary_result && $summary_result->num_rows > 0) {
    echo "<table class='table table-bordered table-striped'>";
    echo "<thead><tr><th>Subject</th><th>Class Level</th><th>Topic Count</th></tr></thead>";
    echo "<tbody>";
    while ($row = $summary_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['subject_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['class_level_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['topic_count']) . "</td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
} else {
    echo "<p class='text-warning'>No summary data found. The curriculum_topics table might be empty.</p>";
}


// --- Query 2: Sample of Raw Topic Titles ---
$sample_sql = "SELECT title, theme FROM curriculum_topics ORDER BY id DESC LIMIT 20";
$sample_result = $conn->query($sample_sql);

echo "<h3 class='mt-4'>Sample of Last 20 Imported Topics</h3>";
if ($sample_result && $sample_result->num_rows > 0) {
    echo "<table class='table table-bordered table-striped'>";
    echo "<thead><tr><th>Topic Title</th><th>Theme</th></tr></thead>";
    echo "<tbody>";
    while ($row = $sample_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td>" . htmlspecialchars($row['theme']) . "</td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
} else {
    echo "<p class='text-warning'>No sample topics found.</p>";
}


$conn->close();
require_once 'includes/footer.php';
?>
