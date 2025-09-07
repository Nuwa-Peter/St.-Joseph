<?php

// Create a new router instance
$router = new \Bramus\Router\Router();

// Require controllers
require_once __DIR__ . '/../controllers/AuthController.php';

// Custom 404 Handler
$router->set404(function () {
    global $conn;
    http_response_code(404);
    include __DIR__ . '/../404.php';
});

// Define a route for the root directory
$router->get('/', function () {
    header('Location: ' . login_url());
    exit();
});

// Before middleware
$router->before('GET|POST', '/admin/.*', function () {
    if (!isset($_SESSION['user_id'])) {
        header('location: ' . login_url());
        exit();
    }
});

// Dashboard
$router->get('/dashboard', function () {
    global $conn;
    include __DIR__ . '/../dashboard.php';
});

$router->get('/parent-dashboard', function () {
    global $conn;
    include __DIR__ . '/../parent_dashboard.php';
});

// Login/Logout
$router->all('/login', function () {
    global $conn;
    $authController = new AuthController($conn);
    $authController->login();
});

$router->get('/logout', function () {
    global $conn;
    include __DIR__ . '/../logout.php';
});

// Profile
$router->get('/profile', function () {
    global $conn;
    include __DIR__ . '/../profile.php';
});

// Student management
$router->get('/students', function () {
    global $conn;
    include __DIR__ . '/../students.php';
});

$router->get('/students/create', function () {
    global $conn;
    include __DIR__ . '/../student_create.php';
});

$router->post('/students/create', function () {
    global $conn;
    include __DIR__ . '/../student_create.php';
});

$router->get('/students/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include __DIR__ . '/../student_edit.php';
});

$router->post('/students/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include __DIR__ . '/../student_edit.php';
});

$router->get('/students/view/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include __DIR__ . '/../student_view.php';
});

$router->get('/students/import-export', function () {
    global $conn;
    include __DIR__ . '/../student_import_export.php';
});

$router->get('/students/unregistered', function () {
    global $conn;
    include __DIR__ . '/../unregistered_students.php';
});

$router->get('/students/analytics', function () {
    global $conn;
    include __DIR__ . '/../student_analytics.php';
});

$router->get('/students/accounts', function () {
    global $conn;
    include __DIR__ . '/../student_accounts.php';
});


// Teacher management
$router->get('/teachers', function () {
    global $conn;
    include __DIR__ . '/../teachers.php';
});

$router->get('/teachers/create', function () {
    global $conn;
    include __DIR__ . '/../teacher_create.php';
});

$router->post('/teachers/create', function () {
    global $conn;
    include __DIR__ . '/../teacher_create.php';
});

$router->get('/teachers/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include __DIR__ . '/../teacher_edit.php';
});

$router->post('/teachers/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include __DIR__ . '/../teacher_edit.php';
});

// User management
$router->get('/users', function () {
    global $conn;
    include __DIR__ . '/../users.php';
});

$router->get('/users/create', function () {
    global $conn;
    include __DIR__ . '/../user_create.php';
});

$router->post('/users/create', function () {
    global $conn;
    include __DIR__ . '/../user_create.php';
});

$router->get('/users/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include __DIR__ . '/../user_edit.php';
});

$router->post('/users/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include __DIR__ . '/../user_edit.php';
});


// Academic management
$router->get('/subjects', function () {
    global $conn;
    include __DIR__ . '/../subjects.php';
});

$router->get('/subjects/create', function () {
    global $conn;
    include __DIR__ . '/../subject_create.php';
});

$router->post('/subjects/create', function () {
    global $conn;
    include __DIR__ . '/../subject_create.php';
});

$router->get('/subjects/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include __DIR__ . '/../subject_edit.php';
});

$router->post('/subjects/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include __DIR__ . '/../subject_edit.php';
});

$router->get('/subjects/delete/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include __DIR__ . '/../subject_delete.php';
});

$router->get('/classes', function () {
    global $conn;
    include __DIR__ . '/../class_levels.php';
});

$router->get('/classes/create', function () {
    global $conn;
    include __DIR__ . '/../class_level_create.php';
});

$router->post('/classes/create', function () {
    global $conn;
    include __DIR__ . '/../class_level_create.php';
});

$router->get('/classes/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include __DIR__ . '/../class_level_edit.php';
});

$router->post('/classes/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include __DIR__ . '/../class_level_edit.php';
});

$router->get('/streams', function () {
    global $conn;
    include __DIR__ . '/../streams.php';
});

$router->get('/streams/create', function () {
    global $conn;
    include __DIR__ . '/../stream_create.php';
});

$router->post('/streams/create', function () {
    global $conn;
    include __DIR__ . '/../stream_create.php';
});

$router->get('/streams/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include __DIR__ . '/../stream_edit.php';
});

$router->post('/streams/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include __DIR__ . '/../stream_edit.php';
});

$router->get('/academics/grading-scales', function () {
    global $conn;
    include __DIR__ . '/../grading_scales.php';
});


// Assignment management
$router->get('/assignments', function () {
    global $conn;
    include __DIR__ . '/../assignments.php';
});

$router->get('/assignments/create', function () {
    global $conn;
    include __DIR__ . '/../assignment_create.php';
});

$router->post('/assignments/create', function () {
    global $conn;
    include __DIR__ . '/../assignment_create.php';
});

$router->get('/assignments/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include __DIR__ . '/../assignment_edit.php';
});

$router->post('/assignments/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include __DIR__ . '/../assignment_edit.php';
});

$router->get('/assignments/submissions', function () {
    global $conn;
    include __DIR__ . '/../assignment_submissions.php';
});


// Attendance
$router->get('/attendance', function () {
    global $conn;
    include __DIR__ . '/../class_attendance.php';
});

$router->get('/attendance/take', function () {
    global $conn;
    include __DIR__ . '/../take_attendance.php';
});

$router->get('/attendance/view', function () {
    global $conn;
    include __DIR__ . '/../view_attendance.php';
});

$router->get('/attendance/exam', function () {
    global $conn;
    include __DIR__ . '/../exam_attendance.php';
});

// Financial management
$router->get('/finance', function () {
    global $conn;
    include __DIR__ . '/../finance_dashboard.php';
});

$router->get('/finance/reports', function () {
    global $conn;
    include __DIR__ . '/../finance_reports.php';
});

$router->get('/finance/invoices', function () {
    global $conn;
    include __DIR__ . '/../invoices.php';
});

$router->get('/finance/fees', function () {
    global $conn;
    include __DIR__ . '/../fee_structures.php';
});

$router->get('/finance/expenses', function () {
    global $conn;
    include __DIR__ . '/../expenses.php';
});


// Library management
$router->get('/library', function () {
    global $conn;
    include __DIR__ . '/../books.php';
});

$router->get('/library/books/create', function () {
    global $conn;
    include __DIR__ . '/../book_create.php';
});

$router->post('/library/books/create', function () {
    global $conn;
    include __DIR__ . '/../book_create.php';
});

$router->get('/library/books/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include __DIR__ . '/../book_edit.php';
});

$router->post('/library/books/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include __DIR__ . '/../book_edit.php';
});

$router->get('/library/books/view/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include __DIR__ . '/../book_view.php';
});

$router->get('/library/checkouts', function () {
    global $conn;
    include __DIR__ . '/../checkouts.php';
});


// Communication
$router->get('/announcements', function () {
    global $conn;
    include __DIR__ . '/../announcements.php';
});

$router->get('/announcements/create', function () {
    global $conn;
    include __DIR__ . '/../announcement_create.php';
});

$router->post('/announcements/create', function () {
    global $conn;
    include __DIR__ . '/../announcement_create.php';
});

$router->get('/announcements/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include __DIR__ . '/../announcement_edit.php';
});

$router->post('/announcements/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include __DIR__ . '/../announcement_edit.php';
});

$router->get('/messages', function () {
    global $conn;
    include __DIR__ . '/../messages.php';
});


// Events and calendar
$router->get('/events', function () {
    global $conn;
    include __DIR__ . '/../events.php';
});

$router->get('/events/create', function () {
    global $conn;
    include __DIR__ . '/../event_create.php';
});

$router->post('/events/create', function () {
    global $conn;
    include __DIR__ . '/../event_create.php';
});

$router->get('/events/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include __DIR__ . '/../event_edit.php';
});

$router->post('/events/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include __DIR__ . '/../event_edit.php';
});

$router->get('/calendar', function () {
    global $conn;
    include __DIR__ . '/../calendar.php';
});


// Clubs
$router->get('/clubs', function () {
    global $conn;
    include __DIR__ . '/../clubs.php';
});


// Reports
$router->get('/reports', function () {
    global $conn;
    include __DIR__ . '/../generate_report_pdf.php';
});

$router->get('/reports/competency', function () {
    global $conn;
    include __DIR__ . '/../generate_competency_based_report.php';
});

$router->get('/reports/id-cards', function () {
    global $conn;
    include __DIR__ . '/../id_card_generator.php';
});


// Settings
$router->get('/settings', function () {
    global $conn;
    include __DIR__ . '/../school_settings.php';
});

$router->get('/settings/audit', function () {
    global $conn;
    include __DIR__ . '/../audit_trail.php';
});


// API
$router->all('/api/(.*)', function ($path) {
    global $conn;
    include __DIR__ . '/../api_' . $path . '.php';
});

// Run the router
$router->run();
