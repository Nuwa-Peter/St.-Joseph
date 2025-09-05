<?php
// This file assumes session_start() and url_helper.php have been included.
$user_role = $_SESSION['role'] ?? '';

// Define role groups for easier checking
$is_admin = in_array($user_role, ['root', 'headteacher', 'director']);
$is_teacher = in_array($user_role, ['teacher', 'headteacher', 'root', 'director']);
$is_finance = in_array($user_role, ['bursar', 'headteacher', 'root', 'director']);
$is_librarian = in_array($user_role, ['librarian', 'headteacher', 'root', 'director']);
$is_parent = $user_role === 'parent';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo $is_parent ? parent_dashboard_url() : dashboard_url(); ?>">
            <img src="<?php echo url('images/logo.png'); ?>" alt="Logo" class="navbar-logo">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <!-- Main Navigation Links -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $is_parent ? parent_dashboard_url() : dashboard_url(); ?>"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a>
                </li>

                <?php if (!$is_parent): ?>
                <!-- Academics Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-mortarboard me-1"></i> Academics</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo classes_url(); ?>">Classes & Streams</a></li>
                        <li><a class="dropdown-item" href="<?php echo subjects_url(); ?>">Subjects</a></li>
                        <li><a class="dropdown-item" href="<?php echo assignments_url(); ?>">Assignments</a></li>
                        <li><a class="dropdown-item" href="<?php echo grading_scales_url(); ?>">Grading Scales</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo set_exam_url(); ?>">Examinations</a></li>
                        <li><a class="dropdown-item" href="<?php echo report_card_generator_url(); ?>">Report Cards</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo lesson_planner_url(); ?>">Lesson Planner</a></li>
                        <li><a class="dropdown-item" href="<?php echo student_analytics_url(); ?>">Student Analytics</a></li>
                    </ul>
                </li>

                <!-- People Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-people-fill me-1"></i> People</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo students_url(); ?>">Students</a></li>
                        <li><a class="dropdown-item" href="<?php echo teachers_url(); ?>">Teachers</a></li>
                        <li><a class="dropdown-item" href="<?php echo users_url(); ?>">All Users</a></li>
                        <?php if ($is_admin): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo link_student_to_parent_url(); ?>">Link Parent to Student</a></li>
                        <li><a class="dropdown-item" href="<?php echo create_staff_group_url(); ?>">Staff Groups</a></li>
                        <li><a class="dropdown-item" href="<?php echo url('alumni'); ?>">Alumni</a></li> <!-- Placeholder for new feature -->
                        <?php endif; ?>
                    </ul>
                </li>

                <!-- Finance Dropdown -->
                <?php if ($is_finance): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-cash-coin me-1"></i> Finance</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo finance_url(); ?>">Finance Dashboard</a></li>
                        <li><a class="dropdown-item" href="<?php echo invoices_url(); ?>">Invoices & Payments</a></li>
                        <li><a class="dropdown-item" href="<?php echo student_accounts_url(); ?>">Student Accounts</a></li>
                        <li><a class="dropdown-item" href="<?php echo expenses_url(); ?>">Expenses</a></li>
                        <li><a class="dropdown-item" href="<?php echo fees_url(); ?>">Fee Structures</a></li>
                        <li><a class="dropdown-item" href="<?php echo accountability_url(); ?>">Accountability</a></li>
                        <li><a class="dropdown-item" href="<?php echo view_requisitions_url(); ?>">Requisitions</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo finance_reports_url(); ?>">Finance Reports</a></li>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Operations Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-buildings me-1"></i> Operations</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo library_url(); ?>">Library</a></li>
                        <li><a class="dropdown-item" href="<?php echo resources_url(); ?>">Manage Resources</a></li>
                        <li><a class="dropdown-item" href="<?php echo bookings_url(); ?>">Book a Resource</a></li>
                        <li><a class="dropdown-item" href="<?php echo dormitories_url(); ?>">Dormitories</a></li>
                        <li><a class="dropdown-item" href="<?php echo url('inventory'); ?>">Inventory</a></li> <!-- Placeholder for new feature -->
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo announcements_url(); ?>">Announcements</a></li>
                        <li><a class="dropdown-item" href="<?php echo messages_url(); ?>">Direct Messages</a></li>
                        <?php if ($is_admin): ?>
                        <li><a class="dropdown-item" href="<?php echo bulk_sms_url(); ?>">Bulk SMS</a></li>
                        <?php endif; ?>
                    </ul>
                </li>

                <!-- Student Life Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-heart-pulse me-1"></i> Student Life</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo attendance_url(); ?>">Student Attendance</a></li>
                        <li><a class="dropdown-item" href="<?php echo discipline_url(); ?>">Discipline</a></li>
                        <li><a class="dropdown-item" href="<?php echo health_record_url(); ?>">Health Records</a></li>
                        <li><a class="dropdown-item" href="<?php echo clubs_url(); ?>">Clubs</a></li>
                        <li><a class="dropdown-item" href="<?php echo events_url(); ?>">Events</a></li>
                        <li><a class="dropdown-item" href="<?php echo calendar_url(); ?>">School Calendar</a></li>
                    </ul>
                </li>

                <!-- Settings Dropdown -->
                <?php if ($is_admin): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-gear-fill me-1"></i> Settings</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo settings_url(); ?>">School Settings</a></li>
                        <li><a class="dropdown-item" href="<?php echo id_cards_url(); ?>">ID Card Generator</a></li>
                        <li><a class="dropdown-item" href="<?php echo url('staff-attendance'); ?>">Staff Attendance</a></li> <!-- Placeholder for new feature -->
                        <li><a class="dropdown-item" href="<?php echo url('video-library'); ?>">Video Library</a></li> <!-- Placeholder for new feature -->
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo audit_url(); ?>">Audit Trail</a></li>
                    </ul>
                </li>
                <?php endif; ?>

                <?php endif; // End parent check ?>
            </ul>

            <!-- Right-aligned items -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                <!-- Search Bar -->
                <li class="nav-item me-2">
                    <div class="search-container position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3" style="z-index: 10;"></i>
                        <input class="form-control form-control-sm navbar-search-input ps-4" type="search" id="live-search-input" placeholder="Search..." aria-label="Search" autocomplete="off">
                        <div class="list-group position-absolute" id="live-search-results" style="z-index: 1050; width: 300px; top: 100%; left: 0;"></div>
                    </div>
                </li>
                <!-- User Profile Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php if(isset($_SESSION["initials"])): ?>
                            <div class="avatar-initials-sm me-2"><?php echo htmlspecialchars($_SESSION["initials"]); ?></div>
                        <?php endif; ?>
                        <span class="d-none d-lg-inline"><?php echo htmlspecialchars($_SESSION["name"] ?? 'Guest'); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?php echo profile_url(); ?>"><i class="bi bi-person-circle me-2"></i>Profile</a></li>
                        <?php if ($user_role === 'teacher'): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo request_leave_url(); ?>">Request Leave</a></li>
                            <li><a class="dropdown-item" href="<?php echo view_my_leave_url(); ?>">View My Leave</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo about_url(); ?>">About</a></li>
                        <li><a class="dropdown-item" href="<?php echo logout_url(); ?>"><i class="bi bi-box-arrow-right me-2"></i>Sign out</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<script>
// Live Search JavaScript
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('live-search-input');
    const resultsContainer = document.getElementById('live-search-results');
    let debounceTimer;

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const query = searchInput.value;

                if (query.length < 2) {
                    resultsContainer.innerHTML = '';
                    resultsContainer.style.display = 'none';
                    return;
                }

                fetch(`<?php echo url('api/live-search'); ?>?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        resultsContainer.innerHTML = '';
                        if (data && data.length > 0) {
                            resultsContainer.style.display = 'block';
                            data.forEach(user => {
                                const link = document.createElement('a');
                                let userUrl = user.role === 'student' ? '<?php echo student_view_url(0); ?>'.replace('0', user.id) : '<?php echo profile_url(); ?>?id=' + user.id;
                                link.href = userUrl;
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
            }, 300);
        });

        document.addEventListener('click', function (event) {
            if (!searchInput.contains(event.target)) {
                resultsContainer.style.display = 'none';
            }
        });
    }
});
</script>
