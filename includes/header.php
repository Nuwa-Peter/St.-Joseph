<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management System</title>
    <?php
    // The url() function is defined in url_helper.php, which is included in index.php before this header.
    // We need to ensure it's available for asset paths.
    if (!function_exists('url')) {
        require_once __DIR__ . '/url_helper.php';
    }
    ?>
    <link rel="icon" type="image/png" href="<?php echo url('images/logo.png'); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?php echo url('assets/libs/cropperjs/cropper.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/modern-theme.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/modern-navbar.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/custom_navbar.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/dark-theme.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/custom.css'); ?>">
</head>
<body>
    <?php
    // This file is included after config.php, so $conn is available.
    $school_name = 'St. Joseph\'s VSS'; // Default value
    $sql = "SELECT setting_value FROM school_settings WHERE setting_key = 'school_name'";
    if ($result = $conn->query($sql)) {
        if ($row = $result->fetch_assoc()) {
            $school_name = $row['setting_value'];
        }
        $result->free();
    }
    ?>
    <header class="top-header">
        <a href="<?php echo (isset($_SESSION['role']) && $_SESSION['role'] === 'parent') ? parent_dashboard_url() : dashboard_url(); ?>" class="text-decoration-none">
            <img src="<?php echo url('images/logo.png'); ?>" alt="School Logo" height="40" class="me-3 header-logo">
            <span class="school-name"><?php echo htmlspecialchars($school_name); ?></span>
        </a>
    </header>
    <?php
    // The navbar also needs the URL helper functions.
    if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
        require_once 'navbar.php';
    }
    ?>
    <main class="container-fluid p-4">
        <!-- Page content will be here -->
