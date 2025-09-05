<?php
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('src/views'));
$files = new RegexIterator($files, '/\.php$/');

$replacements = [
    "action=\"accountability.php\"" => "action=\"<?php echo accountability_url(); ?>\"",
    "action=\"admin_update_password.php\"" => "action=\"<?php echo url('admin_update_password.php'); ?>\"",
    "action=\"assignment_edit.php?id=<?php echo \$assignment_id; ?>\"" => "action=\"<?php echo assignment_edit_url(\$assignment_id); ?>\"",
    "action=\"assignment_submissions.php\"" => "action=\"<?php echo assignment_submissions_url(); ?>\"",
    "action=\"handle_submission.php\"" => "action=\"<?php echo url('handle_submission.php'); ?>\"",
    "action=\"bookings.php\"" => "action=\"<?php echo bookings_url(); ?>\"",
    "action=\"bulk_sms.php\"" => "action=\"<?php echo bulk_sms_url(); ?>\"",
    "action=\"checkouts.php\"" => "action=\"<?php echo checkouts_url(); ?>\"",
    "action=\"discipline.php\"" => "action=\"<?php echo discipline_url(); ?>\"",
    "action=\"dormitories.php\"" => "action=\"<?php echo dormitories_url(); ?>\"",
    "action=\"event_edit.php?id=<?php echo \$event_id; ?>\"" => "action=\"<?php echo event_edit_url(\$event_id); ?>\"",
    "action=\"save_exam_attendance.php\"" => "action=\"<?php echo url('save_exam_attendance.php'); ?>\"",
    "action=\"expenses.php\"" => "action=\"<?php echo expenses_url(); ?>\"",
    "action=\"fee_items.php?structure_id=<?php echo \$structure_id; ?>\"" => "action=\"<?php echo url('fee_items.php', ['structure_id' => \$structure_id]); ?>\"",
    "action=\"fee_structures.php\"" => "action=\"<?php echo fees_url(); ?>\"",
    "action=\"finance_reports.php\"" => "action=\"<?php echo finance_reports_url(); ?>\"",
    "action=\"grading_scales.php\"" => "action=\"<?php echo grading_scales_url(); ?>\"",
    "action=\"health_record.php?student_id=<?php echo \$student_id; ?>\"" => "action=\"<?php echo url('health_record.php', ['student_id' => \$student_id]); ?>\"",
    "action=\"generate_id_card_pdf.php\"" => "action=\"<?php echo url('generate_id_card_pdf.php'); ?>\"",
    "action=\"invoices.php\"" => "action=\"<?php echo invoices_url(); ?>\"",
    "action=\"make_requisition.php\"" => "action=\"<?php echo make_requisition_url(); ?>\"",
    "action=\"manage_rooms.php?dormitory_id=<?php echo \$dormitory_id; ?>\"" => "action=\"<?php echo url('manage_rooms.php', ['dormitory_id' => \$dormitory_id]); ?>\"",
    "action=\"marks_entry.php\"" => "action=\"<?php echo marks_entry_url(); ?>\"",
    "action=\"update_profile.php\"" => "action=\"<?php echo url('update_profile.php'); ?>\"",
    "action=\"profile.php\"" => "action=\"<?php echo profile_url(); ?>\"",
    "action=\"generate_report_pdf.php\"" => "action=\"<?php echo url('generate_report_pdf.php'); ?>\"",
    "action=\"submit_leave_request.php\"" => "action=\"<?php echo url('submit_leave_request.php'); ?>\"",
    "action=\"room_assignments.php\"" => "action=\"<?php echo room_assignments_url(); ?>\"",
    "action=\"school_settings.php\"" => "action=\"<?php echo settings_url(); ?>\"",
    "action=\"assign_class_teacher.php\"" => "action=\"<?php echo assign_class_teacher_url(); ?>\"",
    "action=\"student_accounts.php\"" => "action=\"<?php echo student_accounts_url(); ?>\"",
    "action=\"student_import_export.php\"" => "action=\"<?php echo student_import_export_url(); ?>\"",
    "action=\"student_export_pdf.php\"" => "action=\"<?php echo url('student_export_pdf.php'); ?>\"",
    "action=\"save_attendance.php\"" => "action=\"<?php echo url('save_attendance.php'); ?>\"",
    "action=\"unregistered_students.php\"" => "action=\"<?php echo unregistered_students_url(); ?>\"",
    "action=\"link_student_to_parent.php\"" => "action=\"<?php echo link_student_to_parent_url(); ?>\"",
    "action=\"admin_update_password.php\"" => "action=\"<?php echo url('admin_update_password.php'); ?>\"",
    "action=\"view_attendance.php\"" => "action=\"<?php echo view_attendance_url(); ?>\"",
    "action=\"view_class_attendance.php\"" => "action=\"<?php echo url('view_class_attendance.php'); ?>\"",
    "action=\"view_club.php?id=<?php echo \$club_id; ?>\"" => "action=\"<?php echo url('view_club.php', ['id' => \$club_id]); ?>\"",
    "action=\"view_exam_attendance.php\"" => "action=\"<?php echo url('view_exam_attendance.php'); ?>\"",
    "action=\"view_requisitions.php\"" => "action=\"<?php echo view_requisitions_url(); ?>\"",
    "header(\"location: dashboard.php\")" => "header(\"location: \" . dashboard_url())",
    "header(\"location: login.php\")" => "header(\"location: \" . login_url())",
    "header(\"location: assignments.php\")" => "header(\"location: \" . assignments_url())",
    "header(\"location: assignments.php?error=unauthorized\")" => "header(\"location: \" . assignments_url() . \"?error=unauthorized\")",
    "header(\"location: assignments.php?error=notfound\")" => "header(\"location: \" . assignments_url() . \"?error=notfound\")",
    "header(\"location: class_levels.php\")" => "header(\"location: \" . class_levels_url())",
    "header(\"location: subjects.php\")" => "header(\"location: \" . subjects_url())",
];

foreach ($files as $file) {
    $content = file_get_contents($file->getPathname());
    $new_content = str_replace(array_keys($replacements), array_values($replacements), $content);
    if ($content !== $new_content) {
        file_put_contents($file->getPathname(), $new_content);
        echo "Updated: " . $file->getPathname() . "\n";
    }
}
?>
