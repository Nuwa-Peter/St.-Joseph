<?php
// Test file to verify URL rewriting is working
require_once 'includes/url_helper.php';

echo "<h1>URL Rewriting Test</h1>";
echo "<p>This page tests if the URL rewriting is working correctly.</p>";

echo "<h2>Generated URLs:</h2>";
echo "<ul>";
echo "<li><strong>Dashboard:</strong> <a href='" . dashboard_url() . "'>" . dashboard_url() . "</a></li>";
echo "<li><strong>Students:</strong> <a href='" . students_url() . "'>" . students_url() . "</a></li>";
echo "<li><strong>Create Student:</strong> <a href='" . student_create_url() . "'>" . student_create_url() . "</a></li>";
echo "<li><strong>Teachers:</strong> <a href='" . teachers_url() . "'>" . teachers_url() . "</a></li>";
echo "<li><strong>Subjects:</strong> <a href='" . subjects_url() . "'>" . subjects_url() . "</a></li>";
echo "<li><strong>Classes:</strong> <a href='" . classes_url() . "'>" . classes_url() . "</a></li>";
echo "<li><strong>Finance:</strong> <a href='" . finance_url() . "'>" . finance_url() . "</a></li>";
echo "<li><strong>Library:</strong> <a href='" . library_url() . "'>" . library_url() . "</a></li>";
echo "<li><strong>Announcements:</strong> <a href='" . announcements_url() . "'>" . announcements_url() . "</a></li>";
echo "<li><strong>Events:</strong> <a href='" . events_url() . "'>" . events_url() . "</a></li>";
echo "<li><strong>Reports:</strong> <a href='" . reports_url() . "'>" . reports_url() . "</a></li>";
echo "</ul>";

echo "<h2>Current URL Info:</h2>";
echo "<p><strong>Current URL:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>Script Name:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p><strong>Base URL:</strong> " . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . "</p>";

echo "<h2>Test Navigation:</h2>";
echo "<p><a href='dashboard' class='btn btn-primary'>Go to Dashboard</a></p>";
echo "<p><a href='students' class='btn btn-success'>Go to Students</a></p>";
echo "<p><a href='teachers' class='btn btn-info'>Go to Teachers</a></p>";
echo "<p><a href='finance' class='btn btn-warning'>Go to Finance</a></p>";
echo "<p><a href='library' class='btn btn-secondary'>Go to Library</a></p>";

echo "<h2>Active Navigation Test:</h2>";
echo "<p>Current page active: " . (is_current_url('test') ? 'YES' : 'NO') . "</p>";
echo "<p>Dashboard active: " . (is_current_url('dashboard') ? 'YES' : 'NO') . "</p>";
echo "<p>Students active: " . (is_current_url('students') ? 'YES' : 'NO') . "</p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.btn { display: inline-block; padding: 10px 20px; margin: 5px; text-decoration: none; border-radius: 5px; }
.btn-primary { background: #007bff; color: white; }
.btn-success { background: #28a745; color: white; }
.btn-info { background: #17a2b8; color: white; }
.btn-warning { background: #ffc107; color: black; }
.btn-secondary { background: #6c757d; color: white; }
</style>
