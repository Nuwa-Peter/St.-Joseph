<?php
/**
 * Script to update all redirects in the codebase to use clean URLs
 * Run this script once to update all header() redirects
 */

// Common redirect mappings
$redirect_mappings = [
    // Main pages
    'dashboard.php' => 'dashboard',
    'parent_dashboard.php' => 'parent-dashboard',
    'login.php' => 'login',
    'logout.php' => 'logout',
    'profile.php' => 'profile',
    
    // Student management
    'students.php' => 'students',
    'student_create.php' => 'students/create',
    'student_edit.php' => 'students/edit',
    'student_view.php' => 'students/view',
    'student_import_export.php' => 'students/import-export',
    'student_analytics.php' => 'students/analytics',
    'student_accounts.php' => 'students/accounts',
    'unregistered_students.php' => 'students/unregistered',
    
    // Teacher management
    'teachers.php' => 'teachers',
    'teacher_create.php' => 'teachers/create',
    'teacher_edit.php' => 'teachers/edit',
    
    // User management
    'users.php' => 'users',
    'user_create.php' => 'users/create',
    'user_edit.php' => 'users/edit',
    
    // Academic management
    'subjects.php' => 'subjects',
    'subject_create.php' => 'subjects/create',
    'subject_edit.php' => 'subjects/edit',
    'class_levels.php' => 'classes',
    'class_level_create.php' => 'classes/create',
    'class_level_edit.php' => 'classes/edit',
    'streams.php' => 'streams',
    'stream_create.php' => 'streams/create',
    'stream_edit.php' => 'streams/edit',
    
    // Assignment management
    'assignments.php' => 'assignments',
    'assignment_create.php' => 'assignments/create',
    'assignment_edit.php' => 'assignments/edit',
    'assignment_submissions.php' => 'assignments/submissions',
    
    // Attendance
    'class_attendance.php' => 'attendance',
    'take_attendance.php' => 'attendance/take',
    'view_attendance.php' => 'attendance/view',
    'exam_attendance.php' => 'attendance/exam',
    
    // Financial management
    'finance_dashboard.php' => 'finance',
    'finance_reports.php' => 'finance/reports',
    'invoices.php' => 'finance/invoices',
    'fee_structures.php' => 'finance/fees',
    'expenses.php' => 'finance/expenses',
    
    // Library management
    'books.php' => 'library',
    'book_create.php' => 'library/books/create',
    'book_edit.php' => 'library/books/edit',
    'book_view.php' => 'library/books/view',
    'checkouts.php' => 'library/checkouts',
    
    // Communication
    'announcements.php' => 'announcements',
    'announcement_create.php' => 'announcements/create',
    'announcement_edit.php' => 'announcements/edit',
    'messages.php' => 'messages',
    
    // Events and calendar
    'events.php' => 'events',
    'event_create.php' => 'events/create',
    'event_edit.php' => 'events/edit',
    'calendar.php' => 'calendar',
    
    // Clubs
    'clubs.php' => 'clubs',
    
    // Reports
    'generate_report_pdf.php' => 'reports',
    'generate_competency_based_report.php' => 'reports/competency',
    'id_card_generator.php' => 'reports/id-cards',
    
    // Settings
    'school_settings.php' => 'settings',
    'audit_trail.php' => 'settings/audit',
];

// Get all PHP files in the current directory
$php_files = glob('*.php');

echo "<h1>URL Redirect Update Script</h1>";
echo "<p>This script will update all redirects in PHP files to use clean URLs.</p>";

$total_files = 0;
$updated_files = 0;

foreach ($php_files as $file) {
    if ($file === 'update_redirects.php' || $file === 'test_urls.php') {
        continue; // Skip this script and test file
    }
    
    $total_files++;
    $content = file_get_contents($file);
    $original_content = $content;
    $file_updated = false;
    
    // Update header() redirects
    foreach ($redirect_mappings as $old_url => $new_url) {
        // Pattern 1: header("location: filename.php");
        $pattern1 = '/header\s*\(\s*["\']location:\s*' . preg_quote($old_url, '/') . '["\']\s*\)/i';
        $replacement1 = 'header("location: ' . $new_url . '")';
        
        // Pattern 2: header("location: filename.php?param=value");
        $pattern2 = '/header\s*\(\s*["\']location:\s*' . preg_quote($old_url, '/') . '\?([^"\']*)["\']\s*\)/i';
        $replacement2 = 'header("location: ' . $new_url . '?$1")';
        
        // Pattern 3: header("location: filename.php&param=value");
        $pattern3 = '/header\s*\(\s*["\']location:\s*' . preg_quote($old_url, '/') . '&([^"\']*)["\']\s*\)/i';
        $replacement3 = 'header("location: ' . $new_url . '?$1")';
        
        $new_content = preg_replace($pattern1, $replacement1, $content);
        $new_content = preg_replace($pattern2, $replacement2, $new_content);
        $new_content = preg_replace($pattern3, $replacement3, $new_content);
        
        if ($new_content !== $content) {
            $content = $new_content;
            $file_updated = true;
        }
    }
    
    // Update href links in PHP strings
    foreach ($redirect_mappings as $old_url => $new_url) {
        // Pattern: href="filename.php"
        $pattern1 = '/href\s*=\s*["\']' . preg_quote($old_url, '/') . '["\']/i';
        $replacement1 = 'href="' . $new_url . '"';
        
        // Pattern: href="filename.php?id=value"
        $pattern2 = '/href\s*=\s*["\']' . preg_quote($old_url, '/') . '\?([^"\']*)["\']/i';
        $replacement2 = 'href="' . $new_url . '?$1"';
        
        $new_content = preg_replace($pattern1, $replacement1, $content);
        $new_content = preg_replace($pattern2, $replacement2, $new_content);
        
        if ($new_content !== $content) {
            $content = $new_content;
            $file_updated = true;
        }
    }
    
    if ($file_updated) {
        file_put_contents($file, $content);
        $updated_files++;
        echo "<p style='color: green;'>✓ Updated: $file</p>";
    } else {
        echo "<p style='color: gray;'>- No changes: $file</p>";
    }
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p>Total files processed: $total_files</p>";
echo "<p>Files updated: $updated_files</p>";
echo "<p><strong>URL rewriting has been implemented successfully!</strong></p>";

echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>Test the URLs by visiting: <a href='test_urls.php'>test_urls.php</a></li>";
echo "<li>Make sure your Apache server has mod_rewrite enabled</li>";
echo "<li>Verify that the .htaccess file is working correctly</li>";
echo "<li>Test navigation throughout your application</li>";
echo "</ol>";

echo "<h2>Clean URLs Now Available:</h2>";
echo "<ul>";
foreach ($redirect_mappings as $old => $new) {
    echo "<li><strong>$old</strong> → <a href='$new'>$new</a></li>";
}
echo "</ul>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1, h2 { color: #333; }
hr { border: 1px solid #ddd; margin: 20px 0; }
ul, ol { margin-left: 20px; }
</style>
