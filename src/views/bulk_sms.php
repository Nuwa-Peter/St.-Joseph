<?php
require_once __DIR__ . '/../../config.php';

$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_sms'])) {
    $recipient_group = $_POST['recipient_group'];
    $message = trim($_POST['message']);

    if (empty($recipient_group) || empty($message)) {
        $error_message = "Please select a recipient group and write a message.";
    } else {
        $sql_recipients = "";
        $param_type = "";
        $param_value = null;

        switch ($recipient_group) {
            case 'all_students':
                $sql_recipients = "SELECT phone_number FROM users WHERE role = 'student' AND status = 'active' AND phone_number IS NOT NULL AND phone_number != ''";
                break;
            case 'all_parents':
                $sql_recipients = "SELECT u.phone_number FROM users u JOIN parent_student ps ON u.id = ps.parent_id JOIN users s ON ps.student_id = s.id WHERE u.role = 'parent' AND s.status = 'active' AND u.phone_number IS NOT NULL AND u.phone_number != ''";
                break;
            case 'all_teachers':
                $sql_recipients = "SELECT phone_number FROM users WHERE role = 'teacher' AND status = 'active' AND phone_number IS NOT NULL AND phone_number != ''";
                break;
            default:
                if (strpos($recipient_group, 'class_') === 0) {
                    $class_id = (int)str_replace('class_', '', $recipient_group);
                    $sql_recipients = "SELECT u.phone_number FROM users u JOIN stream_user su ON u.id = su.user_id JOIN streams s ON su.stream_id = s.id WHERE s.class_level_id = ? AND u.role = 'student' AND u.status = 'active' AND u.phone_number IS NOT NULL AND u.phone_number != ''";
                    $param_type = "i";
                    $param_value = $class_id;
                }
                break;
        }

        if (!empty($sql_recipients)) {
            $stmt = $conn->prepare($sql_recipients);
            if ($param_type && !is_null($param_value)) {
                $stmt->bind_param($param_type, $param_value);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            $phone_numbers = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $phone_numbers[] = $row['phone_number'];
                }
            }
            $stmt->close();

            if (!empty($phone_numbers)) {
                $log_content = "--- BULK SMS BATCH ---\n";
                $log_content .= "Time: " . date("Y-m-d H:i:s") . "\n";
                $log_content .= "Message: " . $message . "\n";
                $log_content .= "Recipients (" . count($phone_numbers) . "):\n";
                foreach ($phone_numbers as $number) {
                    $log_content .= "- " . $number . "\n";
                }
                $log_content .= "--- END BATCH ---\n\n";

                file_put_contents('sms_log.txt', $log_content, FILE_APPEND);
                $success_message = "Successfully processed messages for " . count($phone_numbers) . " recipients. (Simulated)";
            } else {
                $error_message = "No recipients with phone numbers found for the selected group.";
            }
        } else {
            $error_message = "Invalid recipient group selected.";
        }
    }
}

require_once __DIR__ . '/../../src/includes/header.php';

// Check if the user is logged in and is an admin, otherwise redirect
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], ['root', 'headteacher'])) {
    header("location: dashboard.php");
    exit;
}

// Fetch classes for the dropdown
$class_levels = [];
$sql_classes = "SELECT id, name FROM class_levels ORDER BY name";
$result_classes = $conn->query($sql_classes);
if ($result_classes && $result_classes->num_rows > 0) {
    while ($row = $result_classes->fetch_assoc()) {
        $class_levels[] = $row;
    }
}

$conn->close();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-chat-dots-fill me-2"></i>Bulk SMS Service</h2>
    </div>
    <p>Send messages to entire groups of users at once. This service simulates sending SMS for now.</p>

    <?php if(!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if(!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            Compose Message
        </div>
        <div class="card-body">
            <form action="bulk_sms.php" method="post">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="recipient_group" class="form-label">Recipient Group</label>
                        <select class="form-select" id="recipient_group" name="recipient_group" required>
                            <option value="" disabled selected>Select a group...</option>
                            <option value="all_students">All Students</option>
                            <option value="all_parents">All Parents</option>
                            <option value="all_teachers">All Teachers</option>
                            <optgroup label="Specific Class">
                                <?php foreach ($class_levels as $class): ?>
                                    <option value="class_<?php echo $class['id']; ?>">
                                        Class: <?php echo htmlspecialchars($class['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="message" class="form-label">Message</label>
                    <textarea class="form-control" id="message" name="message" rows="5" required placeholder="Type your message here..."></textarea>
                    <div id="char-count" class="form-text text-end">0/160 characters</div>
                </div>
                <button type="submit" name="send_sms" class="btn btn-primary"><i class="bi bi-send-fill me-2"></i>Send Message</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const messageTextarea = document.getElementById('message');
    const charCount = document.getElementById('char-count');

    messageTextarea.addEventListener('input', function() {
        const count = this.value.length;
        const sms_count = Math.ceil(count / 160);
        charCount.textContent = `${count}/160 characters (${sms_count} SMS)`;
        if (count > 160) {
            charCount.classList.add('text-danger');
        } else {
            charCount.classList.remove('text-danger');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../../src/includes/footer.php'; ?>
