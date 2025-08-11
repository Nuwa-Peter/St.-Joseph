<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'includes/header.php';
?>

<div class="container mt-5">
    <h2>Dashboard</h2>
    <h4>Welcome, <?php echo htmlspecialchars($_SESSION["name"]); ?>!</h4>
    <p>This is your dashboard. You are logged in.</p>
    <a href="logout.php" class="btn btn-danger">Logout</a>
</div>
