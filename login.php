<?php
session_start();

require_once 'controllers/AuthController.php';

$authController = new AuthController();
$authController->login();
