<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <style>
        .main-layout {
            display: flex;
            min-height: 100vh;
        }
        .content-wrapper {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
    </style>
</head>
<body>
    <div class="main-layout">
        <?php require_once 'sidebar.php'; ?>
        <div class="content-wrapper">
            <?php require_once 'navbar.php'; ?>
            <main class="flex-grow-1 p-4">
                <!-- Page content will be here -->
