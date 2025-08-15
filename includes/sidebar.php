<div class="sidebar d-flex flex-column flex-shrink-0 p-3 text-white bg-custom-lightblue" style="width: 280px; height: 100vh;">
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
        <?php // Example of role-based menu item
        // if ($_SESSION['role'] === 'root' || $_SESSION['role'] === 'headteacher') { ?>
            <li>
                <a href="users.php" class="nav-link text-white">
                    <i class="bi bi-people me-2"></i> User Management
                </a>
            </li>
        <?php // } ?>
        <li class="nav-item">
            <a href="#students-submenu" data-bs-toggle="collapse" class="nav-link text-white">
                <i class="bi bi-person-rolodex me-2"></i> Students
            </a>
            <ul class="collapse nav flex-column ms-3" id="students-submenu" data-bs-parent="#nav-pills">
                <li class="nav-item">
                    <a href="students.php" class="nav-link text-white"><i class="bi bi-people-fill me-2"></i> All Students</a>
                </li>
                <li class="nav-item">
                    <a href="student_create.php" class="nav-link text-white"><i class="bi bi-person-plus-fill me-2"></i> Add Student</a>
                </li>
                <li class="nav-item">
                    <a href="student_import_export.php" class="nav-link text-white"><i class="bi bi-file-earmark-arrow-up-fill me-2"></i> Import/Export</a>
                </li>
            </ul>
        </li>
        <li class="nav-item">
            <a href="#academics-submenu" data-bs-toggle="collapse" class="nav-link text-white">
                <i class="bi bi-journal-bookmark me-2"></i> Academics
            </a>
            <ul class="collapse nav flex-column ms-3" id="academics-submenu" data-bs-parent="#nav-pills">
                <li class="nav-item">
                    <a href="class_levels.php" class="nav-link text-white"><i class="bi bi-bar-chart-steps me-2"></i> Classes & Streams</a>
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
        <li class="nav-item">
            <a href="#" class="nav-link text-white">
                <i class="bi bi-file-earmark-text me-2"></i> Documents
            </a>
        </li>
        <li class="nav-item">
            <a href="#communications-submenu" data-bs-toggle="collapse" class="nav-link text-white">
                <i class="bi bi-chat-dots me-2"></i> Communications
            </a>
            <ul class="collapse nav flex-column ms-3" id="communications-submenu" data-bs-parent="#nav-pills">
                <li class="nav-item">
                    <a href="announcements.php" class="nav-link text-white"><i class="bi bi-megaphone me-2"></i> Announcements</a>
                </li>
            </ul>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link text-white">
                <i class="bi bi-card-checklist me-2"></i> Examinations
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link text-white">
                <i class="bi bi-cash-coin me-2"></i> Finance
            </a>
        </li>
        <li class="nav-item">
            <a href="#library-submenu" data-bs-toggle="collapse" class="nav-link text-white">
                <i class="bi bi-book-half me-2"></i> Library
            </a>
            <ul class="collapse nav flex-column ms-3" id="library-submenu" data-bs-parent="#nav-pills">
                <li class="nav-item">
                    <a href="books.php" class="nav-link text-white"><i class="bi bi-bookshelf me-2"></i> Books</a>
                </li>
                <li class="nav-item">
                    <a href="checkouts.php" class="nav-link text-white"><i class="bi bi-arrow-left-right me-2"></i> Manage Checkouts</a>
                </li>
                <li class="nav-item">
                    <a href="checkout_history.php" class="nav-link text-white"><i class="bi bi-clock-history me-2"></i> Checkout History</a>
                </li>
            </ul>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link text-white">
                <i class="bi bi-dpad me-2"></i> Welfare & Activities
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link text-white">
                <i class="bi bi-gear me-2"></i> Advanced
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link text-white">
                <i class="bi bi-info-circle me-2"></i> About
            </a>
        </li>
    </ul>
    <hr>
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
            <?php if(isset($_SESSION["initials"])): ?>
                <div class="avatar-initials">
                    <?php echo htmlspecialchars($_SESSION["initials"]); ?>
                </div>
            <?php endif; ?>
            <strong><?php if(isset($_SESSION["name"])) echo htmlspecialchars($_SESSION["name"]); ?></strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="logout.php">Sign out</a></li>
        </ul>
    </div>
</div>
