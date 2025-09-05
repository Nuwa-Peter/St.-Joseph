<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management System</title>
    <link rel="icon" type="image/png" href="images/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/libs/cropperjs/cropper.min.css">
    <link rel="stylesheet" href="assets/css/modern-theme.css">
    <link rel="stylesheet" href="assets/css/modern-navbar.css">
    <link rel="stylesheet" href="assets/css/custom_navbar.css">
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body>
    <?php
    // Include URL helper functions
    require_once __DIR__ . '/url_helper.php';
    
    if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
        require_once 'navbar.php';
    }
    ?>
    <main class="container-fluid p-4">
        <!-- Page content will be here -->
