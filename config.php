<?php

require_once __DIR__ . '/vendor/autoload.php';

if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Set the default timezone for the application
date_default_timezone_set('Africa/Kampala');

// Database configuration
$db_host = $_ENV['DB_HOST'] ?? 'localhost';
$db_username = $_ENV['DB_USERNAME'] ?? 'root';
$db_password = $_ENV['DB_PASSWORD'] ?? '';
$db_name = $_ENV['DB_NAME'] ?? 'school_management_simple_db';

// Create a database connection
$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
