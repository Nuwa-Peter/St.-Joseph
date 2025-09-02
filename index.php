<?php

// Configure session for 15-minute inactivity timeout
ini_set('session.gc_maxlifetime', 900);
session_set_cookie_params(900);

// Start session
session_start();

// Check for session timeout
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 900)) {
    // Last request was more than 15 minutes ago
    session_unset();     // Unset $_SESSION variable
    session_destroy();   // Destroy session data
    header('Location: ' . login_url()); // Redirect to login page
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time(); // Update last activity time stamp


// Include Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Include the configuration file
require_once __DIR__ . '/config.php';

// Include the URL helper
require_once __DIR__ . '/includes/url_helper.php';

// Include the router
require_once __DIR__ . '/app/routes.php';
