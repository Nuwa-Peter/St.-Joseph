<?php

// 1. Configure session
ini_set('session.gc_maxlifetime', 900);
session_set_cookie_params(900);
session_start();

// Define a constant to check in included files to prevent direct access
define('APP_RAN', true);

// 2. Include all dependencies and helpers
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/url_helper.php';
require_once __DIR__ . '/includes/csrf_helper.php';

// 3. Perform session-related logic now that helpers are loaded
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 900)) {
    session_unset();
    session_destroy();
    header('Location: ' . login_url());
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

// Generate a CSRF token for the session.
generate_csrf_token();

// 4. Set up the view engine
$twig = require_once __DIR__ . '/includes/view_loader.php';

// 5. Run the application router
// All requests are handled from this point onwards.
require_once __DIR__ . '/app/routes.php';
