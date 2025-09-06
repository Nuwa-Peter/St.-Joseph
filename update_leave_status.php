<?php
require_once 'config.php';

// Ensure user is an admin
$admin_roles = ['root', 'headteacher', 'director', 'dos', 'deputy headteacher', 'admin'];
if (!isset($_SESSION["loggedin"]) || !in_array($_SESSION['role'], $admin_roles)) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request_id = intval($_POST['request_id'] ?? 0);
    $new_status = $_POST['status'] ?? '';
    $reviewer_id = $_SESSION['id'];

    if ($new_status !== 'approved' && $new_status !== 'rejected') {
        $_SESSION['error_message'] = "Invalid status provided.";
        header("location: " . admin_leave_requests_url());
        exit;
    }

    if ($request_id > 0) {
        $sql = "UPDATE leave_requests SET status = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sii", $new_status, $reviewer_id, $request_id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Leave request status updated successfully.";

                // Send notification to the user who made the request
                $req_info_sql = "SELECT user_id, start_date, end_date FROM leave_requests WHERE id = ?";
                $req_stmt = $conn->prepare($req_info_sql);
                $req_stmt->bind_param("i", $request_id);
                $req_stmt->execute();
                $req_result = $req_stmt->get_result();
                if($req_info = $req_result->fetch_assoc()) {
                    $requester_id = $req_info['user_id'];
                    $message = "Your leave request from " . date("d-M-Y", strtotime($req_info['start_date'])) . " to " . date("d-M-Y", strtotime($req_info['end_date'])) . " has been " . $new_status . ".";
                    $link = view_my_leave_url();
                    $notify_sql = "INSERT INTO app_notifications (user_id, message, link) VALUES (?, ?, ?)";
                    $notify_stmt = $conn->prepare($notify_sql);
                    $notify_stmt->bind_param("iss", $requester_id, $message, $link);
                    $notify_stmt->execute();
                    $notify_stmt->close();
                }
                $req_stmt->close();
            } else {
                $_SESSION['error_message'] = "Failed to update leave request status.";
            }
            $stmt->close();
        }
    } else {
        $_SESSION['error_message'] = "Invalid request ID.";
    }
}

header("location: " . admin_leave_requests_url());
exit;

$conn->close();
?>
