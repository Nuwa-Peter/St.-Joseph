<div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 280px; height: 100vh;">
    <a href="dashboard.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span class="fs-4">St. Joseph's VSS</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link text-white" aria-current="page">
                Dashboard
            </a>
        </li>
        <li>
            <a href="announcements.php" class="nav-link text-white">
                Announcements
            </a>
        </li>
        <?php // Example of role-based menu item
        // if ($_SESSION['role'] === 'root' || $_SESSION['role'] === 'headteacher') { ?>
            <li>
                <a href="users.php" class="nav-link text-white">
                    User Management
                </a>
            </li>
        <?php // } ?>
    </ul>
    <hr>
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
            <strong><?php echo htmlspecialchars($_SESSION["name"]); ?></strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="logout.php">Sign out</a></li>
        </ul>
    </div>
</div>
