<?php

require_once 'controllers/AuthController.php';

// The global $conn variable is available here because this file is included
// from within a route closure in app/routes.php where `global $conn` is declared.
$authController = new AuthController($conn);
$authController->login();
