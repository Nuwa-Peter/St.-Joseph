<?php
// This script is intended to be run by a cron job or a task scheduler.
require_once __DIR__ . '/../config.php';

echo "Running Daily Attendance Reminder Script...\n";

// 1. Get all streams that have an assigned class teacher.
$sql = "SELECT s.id as stream_id, s.class_teacher_id, cl.name as class_name, s.name as stream_name
        FROM streams s
        JOIN class_levels cl ON s.class_level_id = cl.id
        WHERE s.class_teacher_id IS NOT NULL";

$streams_with_teachers = $conn->query($sql);

if ($streams_with_teachers->num_rows > 0) {

    $today = date('Y-m-d');

    // Prepare statements for checking and notifying.
    $check_sql = "SELECT 1 FROM attendances a JOIN stream_user su ON a.user_id = su.user_id WHERE su.stream_id = ? AND a.date = ? LIMIT 1";
    $check_stmt = $conn->prepare($check_sql);

    $notify_sql = "INSERT INTO app_notifications (user_id, message, link) VALUES (?, ?, ?)";
    $notify_stmt = $conn->prepare($notify_sql);

    while ($stream = $streams_with_teachers->fetch_assoc()) {
        $stream_id = $stream['stream_id'];
        $teacher_id = $stream['class_teacher_id'];
        $class_full_name = $stream['class_name'] . ' ' . $stream['stream_name'];

        // 2. For each stream, check if attendance has been taken today.
        $check_stmt->bind_param("is", $stream_id, $today);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows === 0) {
            // 3. If no attendance, send a notification.
            echo "Attendance not taken for $class_full_name. Sending reminder to teacher ID #$teacher_id.\n";

            $message = "Reminder: Please take attendance for your class, " . $class_full_name . ", for today.";
            $link = "class_attendance.php";

            $notify_stmt->bind_param("iss", $teacher_id, $message, $link);
            $notify_stmt->execute();
        } else {
            echo "Attendance already taken for $class_full_name.\n";
        }
    }

    $check_stmt->close();
    $notify_stmt->close();

} else {
    echo "No classes with assigned class teachers found.\n";
}

echo "Script finished.\n";
$conn->close();
?>
