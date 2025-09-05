<?php
/**
 * Script to update all role-based access controls to include Director role
 * This script will add 'director' to all admin role arrays throughout the codebase
 */

// Files to update with their specific patterns
$files_to_update = [
    // Files that need director added to admin roles
    'accountability.php' => [
        'pattern' => '/\$authorized_roles\s*=\s*\[[\'"]bursar[\'"],\s*[\'"]headteacher[\'"],\s*[\'"]root[\'"]\];/',
        'replacement' => '$authorized_roles = [\'bursar\', \'headteacher\', \'root\', \'director\'];'
    ],
    'expenses.php' => [
        'pattern' => '/\$authorized_roles\s*=\s*\[[\'"]bursar[\'"],\s*[\'"]headteacher[\'"],\s*[\'"]root[\'"]\];/',
        'replacement' => '$authorized_roles = [\'bursar\', \'headteacher\', \'root\', \'director\'];'
    ],
    'fee_items.php' => [
        'pattern' => '/\$authorized_roles\s*=\s*\[[\'"]bursar[\'"],\s*[\'"]headteacher[\'"],\s*[\'"]root[\'"]\];/',
        'replacement' => '$authorized_roles = [\'bursar\', \'headteacher\', \'root\', \'director\'];'
    ],
    'fee_structures.php' => [
        'pattern' => '/\$authorized_roles\s*=\s*\[[\'"]bursar[\'"],\s*[\'"]headteacher[\'"],\s*[\'"]root[\'"]\];/',
        'replacement' => '$authorized_roles = [\'bursar\', \'headteacher\', \'root\', \'director\'];'
    ],
    'finance_dashboard.php' => [
        'pattern' => '/\$authorized_roles\s*=\s*\[[\'"]bursar[\'"],\s*[\'"]headteacher[\'"],\s*[\'"]root[\'"]\];/',
        'replacement' => '$authorized_roles = [\'bursar\', \'headteacher\', \'root\', \'director\'];'
    ],
    'finance_reports.php' => [
        'pattern' => '/\$authorized_roles\s*=\s*\[[\'"]bursar[\'"],\s*[\'"]headteacher[\'"],\s*[\'"]root[\'"]\];/',
        'replacement' => '$authorized_roles = [\'bursar\', \'headteacher\', \'root\', \'director\'];'
    ],
    'export_requisitions_pdf.php' => [
        'pattern' => '/\$admin_roles\s*=\s*\[[\'"]bursar[\'"],\s*[\'"]headteacher[\'"],\s*[\'"]root[\'"]\];/',
        'replacement' => '$admin_roles = [\'bursar\', \'headteacher\', \'root\', \'director\'];'
    ],
    'assign_class_teacher.php' => [
        'pattern' => '/\$admin_roles\s*=\s*\[[\'"]root[\'"],\s*[\'"]headteacher[\'"]\];/',
        'replacement' => '$admin_roles = [\'root\', \'headteacher\', \'director\'];'
    ],
    'clubs.php' => [
        'pattern' => '/\$admin_roles\s*=\s*\[[\'"]root[\'"],\s*[\'"]headteacher[\'"]\];/',
        'replacement' => '$admin_roles = [\'root\', \'headteacher\', \'director\'];'
    ],
    'dormitories.php' => [
        'pattern' => '/\$admin_roles\s*=\s*\[[\'"]root[\'"],\s*[\'"]headteacher[\'"]\];/',
        'replacement' => '$admin_roles = [\'root\', \'headteacher\', \'director\'];'
    ],
    'api_get_club_details.php' => [
        'pattern' => '/\$admin_roles\s*=\s*\[[\'"]root[\'"],\s*[\'"]headteacher[\'"]\];/',
        'replacement' => '$admin_roles = [\'root\', \'headteacher\', \'director\'];'
    ],
    'assignments.php' => [
        'pattern' => '/\$allowed_roles\s*=\s*\[[\'"]teacher[\'"],\s*[\'"]headteacher[\'"],\s*[\'"]root[\'"]\];/',
        'replacement' => '$allowed_roles = [\'teacher\', \'headteacher\', \'root\', \'director\'];'
    ],
    'assignment_create.php' => [
        'pattern' => '/\$allowed_roles\s*=\s*\[[\'"]teacher[\'"],\s*[\'"]headteacher[\'"],\s*[\'"]root[\'"]\];/',
        'replacement' => '$allowed_roles = [\'teacher\', \'headteacher\', \'root\', \'director\'];'
    ],
    'assignment_delete.php' => [
        'pattern' => '/\$allowed_roles\s*=\s*\[[\'"]teacher[\'"],\s*[\'"]headteacher[\'"],\s*[\'"]root[\'"]\];/',
        'replacement' => '$allowed_roles = [\'teacher\', \'headteacher\', \'root\', \'director\'];'
    ],
    'assignment_edit.php' => [
        'pattern' => '/\$allowed_roles\s*=\s*\[[\'"]teacher[\'"],\s*[\'"]headteacher[\'"],\s*[\'"]root[\'"]\];/',
        'replacement' => '$allowed_roles = [\'teacher\', \'headteacher\', \'root\', \'director\'];'
    ],
    'assignment_submissions.php' => [
        'pattern' => '/\$allowed_roles\s*=\s*\[[\'"]teacher[\'"],\s*[\'"]headteacher[\'"],\s*[\'"]root[\'"]\];/',
        'replacement' => '$allowed_roles = [\'teacher\', \'headteacher\', \'root\', \'director\'];'
    ],
    'grade_submission.php' => [
        'pattern' => '/\$allowed_roles\s*=\s*\[[\'"]teacher[\'"],\s*[\'"]headteacher[\'"],\s*[\'"]root[\'"]\];/',
        'replacement' => '$allowed_roles = [\'teacher\', \'headteacher\', \'root\', \'director\'];'
    ],
    'class_attendance.php' => [
        'pattern' => '/\$allowed_roles\s*=\s*\[[\'"]teacher[\'"],\s*[\'"]class teacher[\'"],\s*[\'"]headteacher[\'"],\s*[\'"]root[\'"]\];/',
        'replacement' => '$allowed_roles = [\'teacher\', \'class teacher\', \'headteacher\', \'root\', \'director\'];'
    ],
    'exam_attendance.php' => [
        'pattern' => '/\$allowed_roles\s*=\s*\[[\'"]teacher[\'"],\s*[\'"]headteacher[\'"],\s*[\'"]root[\'"]\];/',
        'replacement' => '$allowed_roles = [\'teacher\', \'headteacher\', \'root\', \'director\'];'
    ],
    'discipline.php' => [
        'pattern' => '/\$allowed_roles\s*=\s*\[[\'"]root[\'"],\s*[\'"]headteacher[\'"],\s*[\'"]teacher[\'"]\];/',
        'replacement' => '$allowed_roles = [\'root\', \'headteacher\', \'teacher\', \'director\'];'
    ],
    'bulk_sms.php' => [
        'pattern' => '/!in_array\(\$_SESSION\[\'role\'\],\s*\[[\'"]root[\'"],\s*[\'"]headteacher[\'"]\]\)/',
        'replacement' => '!in_array($_SESSION[\'role\'], [\'root\', \'headteacher\', \'director\'])'
    ]
];

echo "<h1>Director Role Access Update Script</h1>";
echo "<p>This script will update all role-based access controls to include the Director role.</p>";

$total_files = 0;
$updated_files = 0;

foreach ($files_to_update as $file => $update_info) {
    if (!file_exists($file)) {
        echo "<p style='color: orange;'>⚠ File not found: $file</p>";
        continue;
    }
    
    $total_files++;
    $content = file_get_contents($file);
    $original_content = $content;
    
    // Apply the pattern replacement
    $new_content = preg_replace($update_info['pattern'], $update_info['replacement'], $content);
    
    if ($new_content !== $content) {
        file_put_contents($file, $new_content);
        $updated_files++;
        echo "<p style='color: green;'>✓ Updated: $file</p>";
    } else {
        echo "<p style='color: gray;'>- No changes needed: $file</p>";
    }
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p>Total files processed: $total_files</p>";
echo "<p>Files updated: $updated_files</p>";

echo "<h2>Director Role Features:</h2>";
echo "<ul>";
echo "<li><strong>Full System Access:</strong> Director has access to all administrative features</li>";
echo "<li><strong>User Management:</strong> Can manage all users (students, teachers, staff)</li>";
echo "<li><strong>Financial Management:</strong> Full access to finance dashboard, reports, and accounting</li>";
echo "<li><strong>Academic Management:</strong> Can manage classes, subjects, assignments, and attendance</li>";
echo "<li><strong>Library Management:</strong> Full access to library operations</li>";
echo "<li><strong>System Settings:</strong> Access to audit trails and system configuration</li>";
echo "<li><strong>Communication:</strong> Can send announcements and bulk SMS</li>";
echo "<li><strong>Reports:</strong> Access to all system reports and analytics</li>";
echo "</ul>";

echo "<h2>Login Credentials for Director:</h2>";
echo "<ul>";
echo "<li><strong>Email:</strong> director@school.app</li>";
echo "<li><strong>Password:</strong> password123</li>";
echo "<li><strong>Username:</strong> director</li>";
echo "</ul>";

echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>Run the SQL script: <code>add_director_role.sql</code> to create the Director user</li>";
echo "<li>Test the Director login with the credentials above</li>";
echo "<li>Verify that the Director has access to all system features</li>";
echo "<li>Test navigation and functionality as the Director role</li>";
echo "</ol>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1, h2 { color: #333; }
hr { border: 1px solid #ddd; margin: 20px 0; }
ul, ol { margin-left: 20px; }
code { background: #f4f4f4; padding: 2px 4px; border-radius: 3px; }
</style>
