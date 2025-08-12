<div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 280px; height: 100vh;">
    <a href="dashboard.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span class="fs-4">St. Joseph's VSS</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link text-white" aria-current="page">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="announcements.php" class="nav-link text-white">
                <i class="bi bi-megaphone me-2"></i> Announcements
            </a>
        </li>

        <li class="nav-item">
            <a href="#academics-submenu" data-bs-toggle="collapse" class="nav-link text-white">
                <i class="bi bi-journal-bookmark me-2"></i> Academics
            </a>
            <ul class="collapse nav flex-column ms-3" id="academics-submenu" data-bs-parent="#nav-pills">
                <li class="nav-item">
                    <a href="class_levels.php" class="nav-link text-white"><i class="bi bi-bar-chart-steps me-2"></i> Class Levels</a>
                </li>
                <li class="nav-item">
                    <a href="subjects.php" class="nav-link text-white"><i class="bi bi-book me-2"></i> Subjects</a>
                </li>
                <li class="nav-item">
                    <a href="teacher_assignments.php" class="nav-link text-white"><i class="bi bi-person-video3 me-2"></i> Teacher Assignments</a>
                </li>
                <li class="nav-item">
                    <a href="student_assignments.php" class="nav-link text-white"><i class="bi bi-person-badge me-2"></i> Student Assignments</a>
                </li>
            </ul>
        </li>

        <?php // Example of role-based menu item
        // if ($_SESSION['role'] === 'root' || $_SESSION['role'] === 'headteacher') { ?>
            <li>
                <a href="users.php" class="nav-link text-white">
                    <i class="bi bi-people me-2"></i> User Management
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
