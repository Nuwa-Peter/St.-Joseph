<?php
// This script is intended to be run by a cron job or a task scheduler.
require_once __DIR__ . '/../config.php';

echo "Running End-of-Leave Alert Script...\n";

// 1. Find all approved leave requests that have ended.
$sql = "SELECT lr.id, lr.end_date, u.first_name, u.last_name
        FROM leave_requests lr
        JOIN users u ON lr.user_id = u.id
        WHERE lr.status = 'approved' AND lr.end_date <= CURDATE()";

$ended_leaves = $conn->query($sql);

if ($ended_leaves->num_rows > 0) {
    // 2. Get the list of administrators to notify.
    $admin_roles = "'headteacher', 'root', 'director', 'dos', 'deputy headteacher'";
    $admin_ids_sql = "SELECT id FROM users WHERE role IN ($admin_roles)";
    $admin_result = $conn->query($admin_ids_sql);
    $admin_ids = [];
    while ($row = $admin_result->fetch_assoc()) {
        $admin_ids[] = $row['id'];
    }

    if (!empty($admin_ids)) {
        // Prepare notification and status update statements
        $notify_stmt = $conn->prepare("INSERT INTO app_notifications (user_id, message, link) VALUES (?, ?, ?)");
        $update_stmt = $conn->prepare("UPDATE leave_requests SET status = 'completed' WHERE id = ?");

        while ($leave = $ended_leaves->fetch_assoc()) {
            $requester_name = $leave['first_name'] . ' ' . $leave['last_name'];
            $leave_id = $leave['id'];

            echo "Processing leave ID #$leave_id for $requester_name...\n";

            // 3. Send notification to each admin.
            $message = "$requester_name's approved leave period has ended.";
            $link = "admin_leave_requests.php";
            foreach ($admin_ids as $admin_id) {
                $notify_stmt->bind_param("iss", $admin_id, $message, $link);
                $notify_stmt->execute();
            }

            // 4. Update the status to 'completed' to prevent re-sending.
            $update_stmt->bind_param("i", $leave_id);
            $update_stmt->execute();
            echo "Marked as completed.\n";
        }
        $notify_stmt->close();
        $update_stmt->close();
    }
} else {
    echo "No ended leave requests to process.\n";
}

echo "Script finished.\n";
$conn->close();
?>
