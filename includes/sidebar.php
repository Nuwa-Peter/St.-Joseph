<div class="sidebar offcanvas-lg offcanvas-start text-white bg-custom-darkblue" tabindex="-1" id="offcanvasSidebar" aria-labelledby="offcanvasSidebarLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasSidebarLabel">St. Joseph's VSS</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#offcanvasSidebar" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column p-0">
        <div class="flex-shrink-0 p-3">
            <a href="dashboard.php" class="d-flex flex-column align-items-center text-center text-white text-decoration-none">
                <img src="images/logo.png" alt="St. Joseph's VSS Logo" class="sidebar-logo mb-2">
                <span class="fs-4">St. Joseph's VSS</span>
            </a>
            <hr class="mt-0">
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link text-white" aria-current="page">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>
                <?php // Example of role-based menu item
                // if ($_SESSION['role'] === 'root' || $_SESSION['role'] === 'headteacher') { ?>
                    <li class="nav-item">
                        <a href="users.php" class="nav-link text-white">
                            <i class="bi bi-people me-2"></i> User Management
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="teachers.php" class="nav-link text-white">
                            <i class="bi bi-person-video3 me-2"></i> Teachers
                        </a>
                    </li>
                <?php // } ?>
                <li class="nav-item">
                    <a href="#students-submenu" data-bs-toggle="collapse" class="nav-link text-white d-flex justify-content-between align-items-center">
                        <span class="sidebar-link-text">
                            <i class="bi bi-person-rolodex me-2"></i> Students
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-right sidebar-chevron" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/></svg>
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
                        <li class="nav-item">
                            <a href="unregistered_students.php" class="nav-link text-white"><i class="bi bi-person-x me-2"></i> Unregistered Students</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="#academics-submenu" data-bs-toggle="collapse" class="nav-link text-white d-flex justify-content-between align-items-center">
                        <span class="sidebar-link-text">
                            <i class="bi bi-journal-bookmark me-2"></i> Academics
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-right sidebar-chevron" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/></svg>
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
                        <li class="nav-item">
                            <a href="grading_scales.php" class="nav-link text-white"><i class="bi bi-rulers me-2"></i> Grading Scales</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="#documents-submenu" data-bs-toggle="collapse" class="nav-link text-white d-flex justify-content-between align-items-center">
                        <span class="sidebar-link-text">
                            <i class="bi bi-file-earmark-text me-2"></i> Documents
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-right sidebar-chevron" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/></svg>
                    </a>
                    <ul class="collapse nav flex-column ms-3" id="documents-submenu" data-bs-parent="#nav-pills">
                        <li class="nav-item">
                            <a href="report_card_generator.php" class="nav-link text-white"><i class="bi bi-file-earmark-pdf me-2"></i> Generate Report Cards</a>
                        </li>
                        <li class="nav-item">
                            <a href="id_card_generator.php" class="nav-link text-white"><i class="bi bi-person-vcard me-2"></i> Generate ID Cards</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="#communications-submenu" data-bs-toggle="collapse" class="nav-link text-white d-flex justify-content-between align-items-center">
                        <span class="sidebar-link-text">
                            <i class="bi bi-telephone me-2"></i> Communications
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-right sidebar-chevron" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/></svg>
                    </a>
                    <ul class="collapse nav flex-column ms-3" id="communications-submenu" data-bs-parent="#nav-pills">
                        <li class="nav-item">
                            <a href="#" class="nav-link text-white"><i class="bi bi-chat-square-dots me-2"></i> Social Chat</a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link text-white"><i class="bi bi-chat-text me-2"></i> Bulk SMS</a>
                        </li>
                        <li class="nav-item">
                            <a href="announcements.php" class="nav-link text-white"><i class="bi bi-megaphone me-2"></i> Announcements</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="#exams-submenu" data-bs-toggle="collapse" class="nav-link text-white d-flex justify-content-between align-items-center">
                        <span class="sidebar-link-text">
                            <i class="bi bi-card-checklist me-2"></i> Examinations
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-right sidebar-chevron" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/></svg>
                    </a>
                    <ul class="collapse nav flex-column ms-3" id="exams-submenu" data-bs-parent="#nav-pills">
                        <li class="nav-item">
                            <a href="set_exam.php" class="nav-link text-white"><i class="bi bi-pencil-square me-2"></i> Set Exams</a>
                        </li>
                        <li class="nav-item">
                            <a href="marks_entry.php" class="nav-link text-white"><i class="bi bi-card-list me-2"></i> Marks Entry</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link text-white">
                        <i class="bi bi-cash-coin me-2"></i> Finance
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#library-submenu" data-bs-toggle="collapse" class="nav-link text-white d-flex justify-content-between align-items-center">
                        <span class="sidebar-link-text">
                            <i class="bi bi-book-half me-2"></i> Library
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-right sidebar-chevron" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/></svg>
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
    </div>
</div>
