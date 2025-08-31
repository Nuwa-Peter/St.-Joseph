<?php

// Create a new router instance
$router = new \Bramus\Router\Router();

// Custom 404 Handler
$router->set404(function () {
    header('HTTP/1.1 404 Not Found');
    echo '<h4>404 - Page Not Found</h4>';
});

// ரூட் டைரக்டரிக்கு ஒரு பாதை வரையறுக்கவும்
$router->get('/', function () {
    header('Location: /login');
    exit();
});

// Before middleware
$router->before('GET|POST', '/admin/.*', function () {
    if (!isset($_SESSION['user_id'])) {
        header('location: /login');
        exit();
    }
});

// Dashboard
$router->get('/dashboard', function () {
    include 'dashboard.php';
});

$router->get('/parent-dashboard', function () {
    include 'parent_dashboard.php';
});

// Login/Logout
$router->get('/login', function () {
    include 'login.php';
});

$router->post('/login', function () {
    include 'login.php';
});

$router->get('/logout', function () {
    include 'logout.php';
});

// Profile
$router->get('/profile', function () {
    include 'profile.php';
});

// Student management
$router->get('/students', function () {
    include 'students.php';
});

$router->get('/students/create', function () {
    include 'student_create.php';
});

$router->post('/students/create', function () {
    include 'student_create.php';
});

$router->get('/students/edit/(\d+)', function ($id) {
    $_GET['id'] = $id;
    include 'student_edit.php';
});

$router->post('/students/edit/(\d+)', function ($id) {
    $_GET['id'] = $id;
    include 'student_edit.php';
});

$router->get('/students/view/(\d+)', function ($id) {
    $_GET['id'] = $id;
    include 'student_view.php';
});

$router->get('/students/import-export', function () {
    include 'student_import_export.php';
});

$router->get('/students/unregistered', function () {
    include 'unregistered_students.php';
});

$router->get('/students/analytics', function () {
    include 'student_analytics.php';
});

$router->get('/students/accounts', function () {
    include 'student_accounts.php';
});


// Teacher management
$router->get('/teachers', function () {
    include 'teachers.php';
});

$router->get('/teachers/create', function () {
    include 'teacher_create.php';
});

$router->post('/teachers/create', function () {
    include 'teacher_create.php';
});

$router->get('/teachers/edit/(\d+)', function ($id) {
    $_GET['id'] = $id;
    include 'teacher_edit.php';
});

$router->post('/teachers/edit/(\d+)', function ($id) {
    $_GET['id'] = $id;
    include 'teacher_edit.php';
});

// User management
$router->get('/users', function () {
    include 'users.php';
});

$router->get('/users/create', function () {
    include 'user_create.php';
});

$router->post('/users/create', function () {
    include 'user_create.php';
});

$router->get('/users/edit/(\d+)', function ($id) {
    $_GET['id'] = $id;
    include 'user_edit.php';
});

$router->post('/users/edit/(\d+)', function ($id) {
    $_GET['id'] = $id;
    include 'user_edit.php';
});


// Academic management
$router->get('/subjects', function () {
    include 'subjects.php';
});

$router->get('/subjects/create', function () {
    include 'subject_create.php';
});

$router->post('/subjects/create', function () {
    include 'subject_create.php';
});

$router->get('/subjects/edit/(\d+)', function ($id) {
    $_GET['id'] = $id;
    include 'subject_edit.php';
});

$router->post('/subjects/edit/(\d+)', function ($id) {
    $_GET['id'] = $id;
    include 'subject_edit.php';
});

$router->get('/classes', function () {
    include 'class_levels.php';
});

$router->get('/classes/create', function () {
    include 'class_level_create.php';
});

$router->post('/classes/create', function () {
    include 'class_level_create.php';
});

$router->get('/classes/edit/(\d+)', function ($id) {
    $_GET['id'] = $id;
    include 'class_level_edit.php';
});

$router->post('/classes/edit/(\d+)', function ($id) {
    $_GET['id'] = $id;
    include 'class_level_edit.php';
});

$router->get('/streams', function () {
    include 'streams.php';
});

$router->get('/streams/create', function () {
    include 'stream_create.php';
});

$router->post('/streams/create', function () {
    include 'stream_create.php';
});

$router->get('/streams/edit/(\d+)', function ($id) {
    $_GET['id'] = $id;
    include 'stream_edit.php';
});

$router->post('/streams/edit/(\d+)', function ($id) {
    $_GET['id'] = $id;
    include 'stream_edit.php';
});


// Assignment management
$router->get('/assignments', function () {
    include 'assignments.php';
});

$router->get('/assignments/create', function () {
    include 'assignment_create.php';
});

$router->post('/assignments/create', function () {
    include 'assignment_create.php';
});

$router->get('/assignments/edit/(\d+)', function ($id) {
    $_GET['id'] = $id;
    include 'assignment_edit.php';
});

$router->post('/assignments/edit/(\d+)', function ($id) {
    $_GET['id'] = $id;
    include 'assignment_edit.php';
});

$router->get('/assignments/submissions', function () {
    include 'assignment_submissions.php';
});


// Attendance
$router->get('/attendance', function () {
    include 'class_attendance.php';
});

$router->get('/attendance/take', function () {
    include 'take_attendance.php';
});

$router->get('/attendance/view', function () {
    include 'view_attendance.php';
});

$router->get('/attendance/exam', function () {
    include 'exam_attendance.php';
});

// Financial management
$router->get('/finance', function () {
    include 'finance_dashboard.php';
});

$router->get('/finance/reports', function () {
    include 'finance_reports.php';
});

$router->get('/finance/invoices', function () {
    include 'invoices.php';
});

$router->get('/finance/fees', function () {
    include 'fee_structures.php';
});

$router->get('/finance/expenses', function () {
    include 'expenses.php';
});


// Library management
$router->get('/library', function () {
    include 'books.php';
});

$router->get('/library/books/create', function () {
    include 'book_create.php';
});

$router->post('/library/books/create', function () {
    include 'book_create.php';
});

$router->get('/library/books/edit/(\d+)', function ($id) {
    $_GET['id'] = $id;
    include 'book_edit.php';
});

$router->post('/library/books/edit/(\d+)', function ($id) {
    $_GET['id'] = $id;
    include 'book_edit.php';
});

$router->get('/library/books/view/(\d+)', function ($id) {
    $_GET['id'] = $id;
    include 'book_view.php';
});

$router.get('/library/checkouts', function () {
    include 'checkouts.php';
});


// Communication
$router->get('/announcements', function () {
    include 'announcements.php';
});

$router->get('/announcements/create', function () {
    include 'announcement_create.php';
});

$router->post('/announcements/create', function () {
    include 'announcement_create.php';
});

$router->get('/announcements/edit/(\d+)', function ($id) {
    $_GET['id'] = $id;
    include 'announcement_edit.php';
});

$router->post('/announcements/edit/(\d+)', function ($id) {
    $_GET['id'] = $id;
    include 'announcement_edit.php';
});

$router->get('/messages', function () {
    include 'messages.php';
});


// Events and calendar
$router->get('/events', function () {
    include 'events.php';
});

$router->get('/events/create', function () {
    include 'event_create.php';
});

$router->post('/events/create', function () {
    include 'event_create.php';
});

$router->get('/events/edit/(\d+)', function ($id) {
    $_GET['id'] = $id;
    include 'event_edit.php';
});

$router->post('/events/edit/(\d+)', function ($id) {
    $_GET['id'] = $id;
    include 'event_edit.php';
});

$router->get('/calendar', function () {
    include 'calendar.php';
});


// Clubs
$router->get('/clubs', function () {
    include 'clubs.php';
});


// Reports
$router->get('/reports', function () {
    include 'generate_report_pdf.php';
});

$router->get('/reports/competency', function () {
    include 'generate_competency_based_report.php';
});

$router->get('/reports/id-cards', function () {
    include 'id_card_generator.php';
});


// Settings
$router->get('/settings', function () {
    include 'school_settings.php';
});

$router->get('/settings/audit', function () {
    include 'audit_trail.php';
});


// API
$router->all('/api/(.*)', function ($path) {
    include 'api_' . $path . '.php';
});

// Run the router
$router->run();
