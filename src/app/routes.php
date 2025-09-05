<?php

// Create a new router instance
$router = new \Bramus\Router\Router();

// Define a base path for includes
$base_path = __DIR__ . '/../';

// Require controllers
require_once $base_path . 'controllers/AuthController.php';

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
$router->get('/dashboard', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/dashboard.php';
});

$router->get('/parent-dashboard', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/parent_dashboard.php';
});

// Login/Logout
$router->all('/login', function () use ($base_path) {
    global $conn;
    $authController = new AuthController($conn);
    $authController->login();
});

$router->get('/logout', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/logout.php';
});

// Profile
$router->get('/profile', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/profile.php';
});

// Student management
$router->get('/students', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/students.php';
});

$router->get('/students/create', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/student_create.php';
});

$router->post('/students/create', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/student_create.php';
});

$router->get('/students/edit/(\d+)', function ($id) use ($base_path) {
    global $conn;
    $_GET['id'] = $id;
    include $base_path . 'views/student_edit.php';
});

$router->post('/students/edit/(\d+)', function ($id) use ($base_path) {
    global $conn;
    $_GET['id'] = $id;
    include $base_path . 'views/student_edit.php';
});

$router->get('/students/view/(\d+)', function ($id) use ($base_path) {
    global $conn;
    $_GET['id'] = $id;
    include $base_path . 'views/student_view.php';
});

$router->get('/students/import-export', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/student_import_export.php';
});

$router->get('/students/unregistered', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/unregistered_students.php';
});

$router->get('/students/analytics', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/student_analytics.php';
});

$router->get('/students/accounts', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/student_accounts.php';
});


// Teacher management
$router->get('/teachers', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/teachers.php';
});

$router->get('/teachers/create', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/teacher_create.php';
});

$router->post('/teachers/create', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/teacher_create.php';
});

$router->get('/teachers/edit/(\d+)', function ($id) use ($base_path) {
    global $conn;
    $_GET['id'] = $id;
    include $base_path . 'views/teacher_edit.php';
});

$router->post('/teachers/edit/(\d+)', function ($id) use ($base_path) {
    global $conn;
    $_GET['id'] = $id;
    include $base_path . 'views/teacher_edit.php';
});

// User management
$router->get('/users', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/users.php';
});

$router->get('/users/create', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/user_create.php';
});

$router->post('/users/create', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/user_create.php';
});

$router->get('/users/edit/(\d+)', function ($id) use ($base_path) {
    global $conn;
    $_GET['id'] = $id;
    include $base_path . 'views/user_edit.php';
});

$router->post('/users/edit/(\d+)', function ($id) use ($base_path) {
    global $conn;
    $_GET['id'] = $id;
    include $base_path . 'views/user_edit.php';
});


// Academic management
$router->get('/subjects', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/subjects.php';
});

$router->get('/subjects/create', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/subject_create.php';
});

$router->post('/subjects/create', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/subject_create.php';
});

$router->get('/subjects/edit/(\d+)', function ($id) use ($base_path) {
    global $conn;
    $_GET['id'] = $id;
    include $base_path . 'views/subject_edit.php';
});

$router->post('/subjects/edit/(\d+)', function ($id) use ($base_path) {
    global $conn;
    $_GET['id'] = $id;
    include $base_path . 'views/subject_edit.php';
});

$router->get('/classes', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/class_levels.php';
});

$router->get('/classes/create', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/class_level_create.php';
});

$router->post('/classes/create', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/class_level_create.php';
});

$router->get('/classes/edit/(\d+)', function ($id) use ($base_path) {
    global $conn;
    $_GET['id'] = $id;
    include $base_path . 'views/class_level_edit.php';
});

$router->post('/classes/edit/(\d+)', function ($id) use ($base_path) {
    global $conn;
    $_GET['id'] = $id;
    include $base_path . 'views/class_level_edit.php';
});

$router->get('/streams', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/streams.php';
});

$router->get('/streams/create', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/stream_create.php';
});

$router->post('/streams/create', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/stream_create.php';
});

$router->get('/streams/edit/(\d+)', function ($id) use ($base_path) {
    global $conn;
    $_GET['id'] = $id;
    include $base_path . 'views/stream_edit.php';
});

$router->post('/streams/edit/(\d+)', function ($id) use ($base_path) {
    global $conn;
    $_GET['id'] = $id;
    include $base_path . 'views/stream_edit.php';
});


// Assignment management
$router->get('/assignments', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/assignments.php';
});

$router->all('/assignments/create', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/assignment_create.php';
});

$router->get('/assignments/edit/(\d+)', function ($id) use ($base_path) {
    global $conn;
    $_GET['id'] = $id;
    include $base_path . 'views/assignment_edit.php';
});

$router->post('/assignments/edit/(\d+)', function ($id) use ($base_path) {
    global $conn;
    $_GET['id'] = $id;
    include $base_path . 'views/assignment_edit.php';
});

$router->get('/assignments/submissions', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/assignment_submissions.php';
});


// Attendance
$router->get('/attendance', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/class_attendance.php';
});

$router->get('/attendance/take', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/take_attendance.php';
});

$router->get('/attendance/view', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/view_attendance.php';
});

$router->get('/attendance/exam', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/exam_attendance.php';
});

$router->post('/attendance/save', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/save_class_attendance.php';
});

// Financial management
$router->get('/finance', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/finance_dashboard.php';
});

$router->get('/finance/reports', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/finance_reports.php';
});

$router->get('/finance/invoices', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/invoices.php';
});

$router->get('/finance/fees', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/fee_structures.php';
});

$router->get('/finance/expenses', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/expenses.php';
});


// Library management
$router->get('/library', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/books.php';
});

$router->get('/library/books/create', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/book_create.php';
});

$router->post('/library/books/create', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/book_create.php';
});

$router->get('/library/books/edit/(\d+)', function ($id) use ($base_path) {
    global $conn;
    $_GET['id'] = $id;
    include $base_path . 'views/book_edit.php';
});

$router->post('/library/books/edit/(\d+)', function ($id) use ($base_path) {
    global $conn;
    $_GET['id'] = $id;
    include $base_path . 'views/book_edit.php';
});

$router->get('/library/books/view/(\d+)', function ($id) use ($base_path) {
    global $conn;
    $_GET['id'] = $id;
    include $base_path . 'views/book_view.php';
});

$router->get('/library/checkouts', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/checkouts.php';
});


// Communication
$router->get('/announcements', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/announcements.php';
});

$router->get('/announcements/create', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/announcement_create.php';
});

$router->post('/announcements/create', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/announcement_create.php';
});

$router->get('/announcements/edit/(\d+)', function ($id) use ($base_path) {
    global $conn;
    $_GET['id'] = $id;
    include $base_path . 'views/announcement_edit.php';
});

$router->post('/announcements/edit/(\d+)', function ($id) use ($base_path) {
    global $conn;
    $_GET['id'] = $id;
    include $base_path . 'views/announcement_edit.php';
});

$router->get('/messages', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/messages.php';
});


// Events and calendar
$router->get('/events', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/events.php';
});

$router->all('/events/create', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/event_create.php';
});

$router->get('/events/edit/(\d+)', function ($id) use ($base_path) {
    global $conn;
    $_GET['id'] = $id;
    include $base_path . 'views/event_edit.php';
});

$router->post('/events/edit/(\d+)', function ($id) use ($base_path) {
    global $conn;
    $_GET['id'] = $id;
    include $base_path . 'views/event_edit.php';
});

$router->get('/calendar', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/calendar.php';
});


// Clubs
$router->all('/clubs', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/clubs.php';
});

// Student Life
$router->get('/student-life/discipline', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/discipline.php';
});

$router->get('/student-life/health-records', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/health_record.php';
});

$router->get('/student-life/dormitories', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/dormitories.php';
});

$router->get('/student-life/alumni', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/alumni.php';
});

// Resources
$router->get('/resources', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/resources_page.php';
});

$router->get('/resources/video-library', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/video_library.php';
});

// Bookings
$router->get('/bookings', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/bookings.php';
});

// Inventory
$router->get('/inventory', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/inventory.php';
});

// Communication
$router->get('/communication/bulk-sms', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/bulk_sms.php';
});

// Finance
$router->get('/finance/accountability', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/accountability.php';
});

$router->get('/finance/requisitions', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/view_requisitions.php';
});

$router->get('/finance/requisitions/new', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/make_requisition.php';
});

// Users
$router->get('/users/link-student-to-parent', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/link_student_to_parent.php';
});

$router->get('/users/create-staff-group', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/create_staff_group.php';
});

// Academics
$router->get('/academics/grading-scales', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/grading_scales.php';
});

$router->get('/academics/examinations', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/examinations.php';
});

$router->get('/academics/lesson-planner', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/lesson_planner.php';
});

// Reports
$router->get('/reports', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/generate_report_pdf.php';
});

$router->get('/reports/competency', function () use ($base_action) {
    global $conn;
    include $base_path . 'views/generate_competency_based_report.php';
});

$router->get('/reports/id-cards', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/id_card_generator.php';
});

$router->get('/reports/report-cards', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/report_card_generator.php';
});


// Settings
$router->get('/settings', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/school_settings.php';
});

// Leave
$router->post('/leave/update', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/update_leave_status.php';
});

$router->get('/settings/audit', function () use ($base_path) {
    global $conn;
    include $base_path . 'views/audit_trail.php';
});


// API
$router->all('/api/(.*)', function ($path) use ($base_path) {
    global $conn;
    include $base_path . 'api/' . $path . '.php';
});

// Run the router
$router->run();
