<?php
/**
 * URL Helper Functions for School Management System
 * Provides clean URL generation for better UX and security
 */

/**
 * Generate a clean URL for the application
 * @param string $path The path without .php extension
 * @param array $params Optional query parameters
 * @return string Clean URL
 */
function url($path, $params = []) {
    $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    // If the base URL is the root, don't include it in the path
    if ($base_url === '/') {
        $base_url = '';
    }
    $url = $base_url . '/' . ltrim($path, '/');

    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }

    return $url;
}

/**
 * Generate clean URLs for common pages
 */
function dashboard_url() { return url('dashboard'); }
function parent_dashboard_url() { return url('parent-dashboard'); }
function login_url() { return url('login'); }
function logout_url() { return url('logout'); }
function profile_url() { return url('profile'); }
function about_url() { return url('about'); }


/**
 * Student management URLs
 */
function students_url() { return url('students'); }
function student_create_url() { return url('students/create'); }
function student_edit_url($id) { return url('students/edit/' . $id); }
function student_view_url($id) { return url('students/view/' . $id); }
function student_import_export_url() { return url('students/import-export'); }
function student_analytics_url() { return url('students/analytics'); }
function student_accounts_url() { return url('students/accounts'); }
function unregistered_students_url() { return url('students/unregistered'); }
function link_student_to_parent_url() { return url('link-student-to-parent'); }


/**
 * Teacher management URLs
 */
function teachers_url() { return url('teachers'); }
function teacher_create_url() { return url('teachers/create'); }
function teacher_edit_url($id) { return url('teachers/edit/' . $id); }

/**
 * User management URLs
 */
function users_url() { return url('users'); }
function user_create_url() { return url('users/create'); }
function user_edit_url($id) { return url('users/edit/' . $id); }
function create_staff_group_url() { return url('create-staff-group'); }


/**
 * Academic management URLs
 */
function subjects_url() { return url('subjects'); }
function subject_create_url() { return url('subjects/create'); }
function subject_edit_url($id) { return url('subjects/edit/' . $id); }
function subject_delete_url($id) { return url('subjects/delete/' . $id); }
function classes_url() { return url('classes'); }
function class_create_url() { return url('classes/create'); }
function class_edit_url($id) { return url('classes/edit/' . $id); }
function class_delete_url($id) { return url('classes/delete/' . $id); }
function streams_url() { return url('streams'); }
function stream_create_url() { return url('streams/create'); }
function stream_edit_url($id) { return url('streams/edit/' . $id); }
function lesson_planner_url() { return url('lesson-planner'); }
function grading_scales_url() { return url('academics/grading-scales'); }
function assign_class_teacher_url() { return url('assign-class-teacher'); }
function assign_subjects_to_stream_url() { return url('assign-subjects-to-stream'); }


/**
 * Assignment & Exam URLs
 */
function assignments_url() { return url('assignments'); }
function assignment_create_url() { return url('assignments/create'); }
function assignment_edit_url($id) { return url('assignments/edit/' . $id); }
function assignment_submissions_url() { return url('assignments/submissions'); }
function set_exam_url() { return url('set-exam'); }
function marks_entry_url() { return url('marks-entry'); }


/**
 * Attendance URLs
 */
function attendance_url() { return url('attendance'); }
function take_attendance_url() { return url('attendance/take'); }
function view_attendance_url() { return url('attendance/view'); }
function exam_attendance_url() { return url('attendance/exam'); }


/**
 * Financial management URLs
 */
function finance_url() { return url('finance'); }
function finance_reports_url() { return url('finance/reports'); }
function invoices_url() { return url('invoices'); }
function fees_url() { return url('finance/fees'); }
function expenses_url() { return url('finance/expenses'); }
function fee_items_url() { return url('fee-items'); }
function accountability_url() { return url('accountability'); }
function view_requisitions_url() { return url('view-requisitions'); }
function make_requisition_url() { return url('make-requisition'); }


/**
 * Library management URLs
 */
function library_url() { return url('library'); }
function book_create_url() { return url('library/books/create'); }
function book_edit_url($id) { return url('library/books/edit/' . $id); }
function book_view_url($id) { return url('library/books/view/' . $id); }
function checkouts_url() { return url('library/checkouts'); }
function checkout_history_url() { return url('checkout-history'); }


/**
 * Communication URLs
 */
function announcements_url() { return url('announcements'); }
function announcement_create_url() { return url('announcements/create'); }
function announcement_edit_url($id) { return url('announcements/edit/' . $id); }
function messages_url() { return url('messages'); }
function bulk_sms_url() { return url('bulk-sms'); }


/**
 * Events and calendar URLs
 */
function events_url() { return url('events'); }
function event_create_url() { return url('events/create'); }
function event_edit_url($id) { return url('events/edit/' . $id); }
function calendar_url() { return url('calendar'); }


/**
 * Resources and Facilities URLs
 */
function bookings_url() { return url('bookings'); }
function resources_url() { return url('resources'); }
function dormitories_url() { return url('dormitories'); }
function manage_rooms_url() { return url('manage-rooms'); }
function room_assignments_url() { return url('room-assignments'); }


/**
 * Student Life URLs
 */
function clubs_url() { return url('clubs'); }
function discipline_url() { return url('discipline'); }
function health_record_url() { return url('health-record'); }


/**
 * Reports & ID Cards URLs
 */
function reports_url() { return url('reports'); }
function competency_reports_url() { return url('reports/competency'); }
function id_cards_url() { return url('reports/id-cards'); }
function id_card_history_url() { return url('id-card-history'); }
function report_card_generator_url() { return url('report-card-generator'); }


/**
 * Staff URLs
 */
function request_leave_url() { return url('request-leave'); }
function view_my_leave_url() { return url('view-my-leave'); }
function admin_leave_requests_url() { return url('admin-leave-requests'); }


/**
 * Settings URLs
 */
function settings_url() { return url('settings'); }
function audit_url() { return url('settings/audit'); }


/**
 * Check if current URL matches the given path
 * @param string $path The path to check
 * @return bool True if current URL matches
 */
function is_current_url($path) {
    $current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $check_path = parse_url(url($path), PHP_URL_PATH);
    return $current_path === $check_path;
}

/**
 * Add active class to navigation links
 * @param string $path The path to check
 * @return string 'active' if current URL matches, empty string otherwise
 */
function nav_active($path) {
    return is_current_url($path) ? 'active' : '';
}
?>
