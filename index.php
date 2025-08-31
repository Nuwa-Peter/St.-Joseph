<?php

// Start session
session_start();

// Include Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Include the configuration file
require_once __DIR__ . '/config.php';

// Include the URL helper
require_once __DIR__ . '/includes/url_helper.php';

// Include the router
require_once __DIR__ . '/app/routes.php';
