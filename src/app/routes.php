<?php

// Create a new router instance
$router = new \Bramus\Router\Router();

// Require controllers
require_once __DIR__ . '/../controllers/AuthController.php';

// Custom 404 Handler
$router->set404(function () {
    header('HTTP/1.1 404 Not Found');
    echo '<h4>404 - Page Not Found</h4>';
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
    include __DIR__ . '/../views/dashboard.php';
});

$router->get('/parent-dashboard', function () {
    global $conn;
    include 'parent_dashboard.php';
});

// Login/Logout
$router->all('/login', function () {
    global $conn;
    $authController = new AuthController($conn);
    $authController->login();
});

$router->get('/logout', function () {
    global $conn;
    include 'logout.php';
});

// Profile
$router->get('/profile', function () {
    global $conn;
    include 'profile.php';
});

// Student management
$router->get('/students', function () {
    global $conn;
    include __DIR__ . '/../views/students.php';
});

$router->get('/students/create', function () {
    global $conn;
    include 'student_create.php';
});

$router->post('/students/create', function () {
    global $conn;
    include 'student_create.php';
});

$router->get('/students/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include 'student_edit.php';
});

$router->post('/students/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include 'student_edit.php';
});

$router->get('/students/view/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include 'student_view.php';
});

$router->get('/students/import-export', function () {
    global $conn;
    include 'student_import_export.php';
});

$router->get('/students/unregistered', function () {
    global $conn;
    include 'unregistered_students.php';
});

$router->get('/students/analytics', function () {
    global $conn;
    include 'student_analytics.php';
});

$router->get('/students/accounts', function () {
    global $conn;
    include 'student_accounts.php';
});


// Teacher management
$router->get('/teachers', function () {
    global $conn;
    include 'teachers.php';
});

$router->get('/teachers/create', function () {
    global $conn;
    include 'teacher_create.php';
});

$router->post('/teachers/create', function () {
    global $conn;
    include 'teacher_create.php';
});

$router->get('/teachers/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include 'teacher_edit.php';
});

$router->post('/teachers/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include 'teacher_edit.php';
});

// User management
$router->get('/users', function () {
    global $conn;
    include 'users.php';
});

$router->get('/users/create', function () {
    global $conn;
    include 'user_create.php';
});

$router->post('/users/create', function () {
    global $conn;
    include 'user_create.php';
});

$router->get('/users/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include 'user_edit.php';
});

$router->post('/users/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include 'user_edit.php';
});


// Academic management
$router->get('/subjects', function () {
    global $conn;
    include __DIR__ . '/../views/subjects.php';
});

$router->get('/subjects/create', function () {
    global $conn;
    include 'subject_create.php';
});

$router->post('/subjects/create', function () {
    global $conn;
    include 'subject_create.php';
});

$router->get('/subjects/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include 'subject_edit.php';
});

$router->post('/subjects/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include 'subject_edit.php';
});

$router->get('/classes', function () {
    global $conn;
    include 'class_levels.php';
});

$router->get('/classes/create', function () {
    global $conn;
    include 'class_level_create.php';
});

$router->post('/classes/create', function () {
    global $conn;
    include 'class_level_create.php';
});

$router->get('/classes/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include 'class_level_edit.php';
});

$router->post('/classes/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include 'class_level_edit.php';
});

$router->get('/streams', function () {
    global $conn;
    include 'streams.php';
});

$router->get('/streams/create', function () {
    global $conn;
    include 'stream_create.php';
});

$router->post('/streams/create', function () {
    global $conn;
    include 'stream_create.php';
});

$router->get('/streams/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include 'stream_edit.php';
});

$router->post('/streams/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include 'stream_edit.php';
});


// Assignment management
$router->get('/assignments', function () {
    global $conn;
    include 'assignments.php';
});

$router->all('/assignments/create', function () {
    global $conn;
    include 'assignment_create.php';
});

$router->get('/assignments/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include 'assignment_edit.php';
});

$router->post('/assignments/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include 'assignment_edit.php';
});

$router->get('/assignments/submissions', function () {
    global $conn;
    include 'assignment_submissions.php';
});


// Attendance
$router->get('/attendance', function () {
    global $conn;
    include 'class_attendance.php';
});

$router->get('/attendance/take', function () {
    global $conn;
    include 'take_attendance.php';
});

$router->get('/attendance/view', function () {
    global $conn;
    include 'view_attendance.php';
});

$router->get('/attendance/exam', function () {
    global $conn;
    include 'exam_attendance.php';
});

$router->post('/attendance/save', function () {
    global $conn;
    include 'save_class_attendance.php';
});

// Financial management
$router->get('/finance', function () {
    global $conn;
    include 'finance_dashboard.php';
});

$router->get('/finance/reports', function () {
    global $conn;
    include 'finance_reports.php';
});

$router->get('/finance/invoices', function () {
    global $conn;
    include 'invoices.php';
});

$router->get('/finance/fees', function () {
    global $conn;
    include 'fee_structures.php';
});

$router->get('/finance/expenses', function () {
    global $conn;
    include 'expenses.php';
});


// Library management
$router->get('/library', function () {
    global $conn;
    include 'books.php';
});

$router->get('/library/books/create', function () {
    global $conn;
    include 'book_create.php';
});

$router->post('/library/books/create', function () {
    global $conn;
    include 'book_create.php';
});

$router->get('/library/books/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include 'book_edit.php';
});

$router->post('/library/books/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include 'book_edit.php';
});

$router->get('/library/books/view/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include 'book_view.php';
});

$router->get('/library/checkouts', function () {
    global $conn;
    include 'checkouts.php';
});


// Communication
$router->get('/announcements', function () {
    global $conn;
    include 'announcements.php';
});

$router->get('/announcements/create', function () {
    global $conn;
    include 'announcement_create.php';
});

$router->post('/announcements/create', function () {
    global $conn;
    include 'announcement_create.php';
});

$router->get('/announcements/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include 'announcement_edit.php';
});

$router->post('/announcements/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include 'announcement_edit.php';
});

$router->get('/messages', function () {
    global $conn;
    include 'messages.php';
});


// Events and calendar
$router->get('/events', function () {
    global $conn;
    include 'events.php';
});

$router->all('/events/create', function () {
    global $conn;
    include 'event_create.php';
});

$router->get('/events/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include 'event_edit.php';
});

$router->post('/events/edit/(\d+)', function ($id) {
    global $conn;
    $_GET['id'] = $id;
    include 'event_edit.php';
});

$router->get('/calendar', function () {
    global $conn;
    include 'calendar.php';
});


// Clubs
$router->all('/clubs', function () {
    global $conn;
    include 'clubs.php';
});

// Student Life
$router->get('/student-life/discipline', function () {
    global $conn;
    include 'discipline.php';
});

$router->get('/student-life/health-records', function () {
    global $conn;
    include 'health_record.php';
});

$router->get('/student-life/dormitories', function () {
    global $conn;
    include 'dormitories.php';
});

$router->get('/student-life/alumni', function () {
    global $conn;
    include 'alumni.php';
});

// Resources
$router->get('/resources', function () {
    global $conn;
    include 'resources_page.php';
});

$router->get('/resources/video-library', function () {
    global $conn;
    include 'video_library.php';
});

// Bookings
$router->get('/bookings', function () {
    global $conn;
    include 'bookings.php';
});

// Inventory
$router->get('/inventory', function () {
    global $conn;
    include 'inventory.php';
});

// Communication
$router->get('/communication/bulk-sms', function () {
    global $conn;
    include 'bulk_sms.php';
});

// Finance
$router->get('/finance/accountability', function () {
    global $conn;
    include 'accountability.php';
});

$router->get('/finance/requisitions', function () {
    global $conn;
    include 'view_requisitions.php';
});

$router->get('/finance/requisitions/new', function () {
    global $conn;
    include 'make_requisition.php';
});

// Users
$router->get('/users/link-student-to-parent', function () {
    global $conn;
    include 'link_student_to_parent.php';
});

$router->get('/users/create-staff-group', function () {
    global $conn;
    include 'create_staff_group.php';
});

// Academics
$router->get('/academics/grading-scales', function () {
    global $conn;
    include 'grading_scales.php';
});

$router->get('/academics/examinations', function () {
    global $conn;
    include 'examinations.php';
});

$router->get('/academics/lesson-planner', function () {
    global $conn;
    include 'lesson_planner.php';
});

// Reports
$router->get('/reports', function () {
    global $conn;
    include 'generate_report_pdf.php';
});

$router->get('/reports/competency', function () {
    global $conn;
    include 'generate_competency_based_report.php';
});

$router->get('/reports/id-cards', function () {
    global $conn;
    include 'id_card_generator.php';
});

$router->get('/reports/report-cards', function () {
    global $conn;
    include 'report_card_generator.php';
});


// Settings
$router->get('/settings', function () {
    global $conn;
    include 'school_settings.php';
});

$router->get('/settings/audit', function () {
    global $conn;
    include 'audit_trail.php';
});


// API
$router->all('/api/(.*)', function ($path) {
    global $conn;
    include 'api_' . $path . '.php';
});

// Run the router
$router->run();
