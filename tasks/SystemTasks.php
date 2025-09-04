<?php

use Crunz\Schedule;

// Create a new schedule
$schedule = new Schedule();

// Define the 'SendLeaveAlerts' task
$task = $schedule->run('php ' . __DIR__ . '/send_leave_alerts.php');
$task->daily() // This is equivalent to running at midnight '0 0 * * *'
     ->description('Send alerts for ended leave requests.')
     ->preventOverlapping(); // A good practice to prevent the same job from running if the previous one is still active.

// Crunz requires the schedule to be returned
return $schedule;
