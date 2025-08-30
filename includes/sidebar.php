<style>
    #live-search-results {
        position: absolute;
        width: calc(100% - 1rem); /* Match padding of parent */
        z-index: 1000;
        max-height: 400px;
        overflow-y: auto;
    }
    #live-search-results .list-group-item {
        background-color: #f8f9fa;
        color: #212529;
        border-bottom: 1px solid #dee2e6;
    }
     #live-search-results .list-group-item:hover {
        background-color: #e9ecef;
    }
</style>
<div class="sidebar offcanvas-lg offcanvas-start text-white sidebar-modern" tabindex="-1" id="offcanvasSidebar" aria-labelledby="offcanvasSidebarLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasSidebarLabel">St. Joseph's VSS</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#offcanvasSidebar" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column p-0">
        <div class="flex-shrink-0 p-3">
            <a href="<?php echo dashboard_url(); ?>" class="d-flex flex-column align-items-center text-center text-white text-decoration-none">
                <img src="images/logo.png" alt="St. Joseph's VSS Logo" class="sidebar-logo mb-2">
                <span class="fs-4">St. Joseph's VSS</span>
            </a>
            <hr class="mt-0">
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item mb-2 px-2" style="position: relative;">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input class="form-control form-control-sm" type="search" id="live-search-input" placeholder="Search for user..." aria-label="Search" autocomplete="off">
                    </div>
                    <div class="list-group" id="live-search-results"></div>
                </li>
                <li class="nav-item">
                    <a href="<?php echo dashboard_url(); ?>" class="nav-link text-white <?php echo nav_active('dashboard'); ?>" aria-current="page">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>
                <?php 
                $admin_roles = ['root', 'headteacher', 'director'];
                if (isset($_SESSION['role']) && in_array($_SESSION['role'], $admin_roles)): 
                ?>
                    <li class="nav-item">
                        <a href="<?php echo users_url(); ?>" class="nav-link text-white <?php echo nav_active('users'); ?>">
                            <i class="bi bi-people me-2"></i> User Management
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo teachers_url(); ?>" class="nav-link text-white <?php echo nav_active('teachers'); ?>">
                            <i class="bi bi-person-video3 me-2"></i> Teachers
                        </a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="#students-submenu" data-bs-toggle="collapse" class="nav-link text-white d-flex justify-content-between align-items-center">
                        <span class="sidebar-link-text">
                            <i class="bi bi-person-rolodex me-2"></i> Students
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-right sidebar-chevron" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/></svg>
                    </a>
                    <ul class="collapse nav flex-column ms-3" id="students-submenu" data-bs-parent="#nav-pills">
                        <li class="nav-item">
                            <a href="<?php echo students_url(); ?>" class="nav-link text-white <?php echo nav_active('students'); ?>"><i class="bi bi-people-fill me-2"></i> All Students</a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo student_create_url(); ?>" class="nav-link text-white <?php echo nav_active('students/create'); ?>"><i class="bi bi-person-plus-fill me-2"></i> Add Student</a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo student_import_export_url(); ?>" class="nav-link text-white <?php echo nav_active('students/import-export'); ?>"><i class="bi bi-file-earmark-arrow-up-fill me-2"></i> Import/Export</a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo unregistered_students_url(); ?>" class="nav-link text-white <?php echo nav_active('students/unregistered'); ?>"><i class="bi bi-person-x me-2"></i> Unregistered Students</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="#requisitions-submenu" data-bs-toggle="collapse" class="nav-link text-white d-flex justify-content-between align-items-center">
                        <span class="sidebar-link-text">
                            <i class="bi bi-pen me-2"></i> Requisitions
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-right sidebar-chevron" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/></svg>
                    </a>
                    <ul class="collapse nav flex-column ms-3" id="requisitions-submenu" data-bs-parent="#nav-pills">
                        <li class="nav-item">
                            <a href="make_requisition.php" class="nav-link text-white"><i class="bi bi-pencil-square me-2"></i> Make a Requisition</a>
                        </li>
                        <li class="nav-item">
                            <a href="view_requisitions.php" class="nav-link text-white"><i class="bi bi-list-check me-2"></i> View Requisitions</a>
                        </li>
                    </ul>
                </li>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'lab_attendant'): ?>
                <li class="nav-item">
                    <a href="#lab-submenu" data-bs-toggle="collapse" class="nav-link text-white d-flex justify-content-between align-items-center">
                        <span class="sidebar-link-text">
                            <i class="bi bi-eyedropper me-2"></i> Laboratory
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-right sidebar-chevron" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/></svg>
                    </a>
                    <ul class="collapse nav flex-column ms-3" id="lab-submenu" data-bs-parent="#nav-pills">
                        <li class="nav-item">
                            <a href="lab_dashboard.php" class="nav-link text-white"><i class="bi bi-speedometer2 me-2"></i> Lab Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a href="lab_inventory.php" class="nav-link text-white"><i class="bi bi-card-list me-2"></i> Manage Inventory</a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="#academics-submenu" data-bs-toggle="collapse" class="nav-link text-white d-flex justify-content-between align-items-center">
                        <span class="sidebar-link-text">
                            <i class="bi bi-journal-bookmark me-2"></i> Academics
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-right sidebar-chevron" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/></svg>
                    </a>
                    <ul class="collapse nav flex-column ms-3" id="academics-submenu" data-bs-parent="#nav-pills">
                        <li class="nav-item">
                            <a href="<?php echo classes_url(); ?>" class="nav-link text-white <?php echo nav_active('classes'); ?>"><i class="bi bi-bar-chart-steps me-2"></i> Classes & Streams</a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo subjects_url(); ?>" class="nav-link text-white <?php echo nav_active('subjects'); ?>"><i class="bi bi-book me-2"></i> Subjects</a>
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
                            <a href="<?php echo announcements_url(); ?>" class="nav-link text-white <?php echo nav_active('announcements'); ?>"><i class="bi bi-megaphone me-2"></i> Announcements</a>
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
                <?php
                $finance_roles = ['bursar', 'headteacher', 'root', 'director'];
                if (isset($_SESSION['role']) && in_array($_SESSION['role'], $finance_roles)):
                ?>
                <li class="nav-item">
                    <a href="#finance-submenu" data-bs-toggle="collapse" class="nav-link text-white d-flex justify-content-between align-items-center">
                        <span class="sidebar-link-text">
                            <i class="bi bi-cash-coin me-2"></i> Finance
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-right sidebar-chevron" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/></svg>
                    </a>
                    <ul class="collapse nav flex-column ms-3" id="finance-submenu" data-bs-parent="#nav-pills">
                        <li class="nav-item">
                            <a href="<?php echo finance_url(); ?>" class="nav-link text-white <?php echo nav_active('finance'); ?>"><i class="bi bi-graph-up me-2"></i> Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a href="accountability.php" class="nav-link text-white"><i class="bi bi-journal-check me-2"></i> Accountability</a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo fees_url(); ?>" class="nav-link text-white <?php echo nav_active('finance/fees'); ?>"><i class="bi bi-collection me-2"></i> Fee Structures</a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo invoices_url(); ?>" class="nav-link text-white <?php echo nav_active('finance/invoices'); ?>"><i class="bi bi-receipt me-2"></i> Invoices & Payments</a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo student_accounts_url(); ?>" class="nav-link text-white <?php echo nav_active('students/accounts'); ?>"><i class="bi bi-person-lines-fill me-2"></i> Student Accounts</a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo expenses_url(); ?>" class="nav-link text-white <?php echo nav_active('finance/expenses'); ?>"><i class="bi bi-box-arrow-up-right me-2"></i> Expenses</a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo finance_reports_url(); ?>" class="nav-link text-white <?php echo nav_active('finance/reports'); ?>"><i class="bi bi-file-earmark-bar-graph me-2"></i> Reports</a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="#library-submenu" data-bs-toggle="collapse" class="nav-link text-white d-flex justify-content-between align-items-center">
                        <span class="sidebar-link-text">
                            <i class="bi bi-book-half me-2"></i> Library
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-right sidebar-chevron" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/></svg>
                    </a>
                    <ul class="collapse nav flex-column ms-3" id="library-submenu" data-bs-parent="#nav-pills">
                        <li class="nav-item">
                            <a href="<?php echo library_url(); ?>" class="nav-link text-white <?php echo nav_active('library'); ?>"><i class="bi bi-bookshelf me-2"></i> Books</a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo checkouts_url(); ?>" class="nav-link text-white <?php echo nav_active('library/checkouts'); ?>"><i class="bi bi-arrow-left-right me-2"></i> Manage Checkouts</a>
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
                    <li><a class="dropdown-item" href="<?php echo profile_url(); ?>">Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?php echo logout_url(); ?>">Sign out</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('live-search-input');
    const resultsContainer = document.getElementById('live-search-results');
    let debounceTimer;

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const query = searchInput.value;

            if (query.length < 2) {
                resultsContainer.innerHTML = '';
                resultsContainer.style.display = 'none';
                return;
            }

            fetch(`api_live_search.php?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    resultsContainer.innerHTML = '';
                    if (data.length > 0) {
                        resultsContainer.style.display = 'block';
                        data.forEach(user => {
                            const link = document.createElement('a');
                            link.href = `profile.php?id=${user.id}`;
                            link.className = 'list-group-item list-group-item-action d-flex align-items-center';

                            let initials = (user.first_name.charAt(0) + user.last_name.charAt(0)).toUpperCase();

                            link.innerHTML = `
                                <div class="flex-shrink-0">
                                    <div class="avatar-initials-sm">${initials}</div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0">${user.first_name} ${user.last_name}</h6>
                                    <small class="text-muted">${user.role}</small>
                                </div>
                            `;
                            resultsContainer.appendChild(link);
                        });
                    } else {
                        resultsContainer.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error fetching search results:', error);
                    resultsContainer.style.display = 'none';
                });
        }, 300); // 300ms debounce
    });

    // Hide results when clicking outside
    document.addEventListener('click', function (event) {
        if (!searchInput.contains(event.target)) {
            resultsContainer.style.display = 'none';
        }
    });
});
</script>
