<?php
require_once __DIR__ . '/vendor/autoload.php';

use Jobby\Jobby;

$jobby = new Jobby();

// --- Define Scheduled Jobs ---

// Job to send alerts for ended leave requests.
$jobby->add('SendLeaveAlerts', [
    'command' => 'php ' . __DIR__ . '/tasks/send_leave_alerts.php',
    'schedule' => '0 0 * * *', // Run once a day at midnight
    'output' => __DIR__ . '/logs/scheduler.log',
    'enabled' => true,
]);


// --- Run the Scheduler ---
// This command will be executed by the system's master cron job every minute.
// The Jobby scheduler will then decide if it's time to run any of the defined jobs.
try {
    $jobby->run();
} catch (Exception $e) {
    // Log any exceptions during the run.
    file_put_contents(__DIR__ . '/logs/scheduler_error.log', $e->getMessage() . "\n", FILE_APPEND);
}
?>
