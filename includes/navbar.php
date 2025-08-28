<?php
// This file assumes session_start() has been called in the parent file
$user_role = $_SESSION['role'] ?? '';

// Define role groups for easier checking
$admin_roles = ['root', 'headteacher'];
$teacher_roles = ['root', 'headteacher', 'teacher'];
$finance_roles = ['bursar', 'headteacher', 'root'];

$is_admin = in_array($user_role, $admin_roles);
$is_teacher_or_admin = in_array($user_role, $teacher_roles);
$is_finance_user = in_array($user_role, $finance_roles);
$is_lab_attendant = $user_role === 'lab_attendant';
$is_parent = $user_role === 'parent';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo $is_parent ? 'parent_dashboard.php' : 'dashboard.php'; ?>">
            <img src="images/logo.png" alt="Logo" class="navbar-logo">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <!-- Main Navigation Links -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $is_parent ? 'parent_dashboard.php' : 'dashboard.php'; ?>"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
                </li>

                <?php if (!$is_parent): // Hide most links from parents ?>

                <!-- Academics Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="academicsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-mortarboard me-1"></i>Academics</a>
                    <ul class="dropdown-menu" aria-labelledby="academicsDropdown">
                        <li><a class="dropdown-item" href="class_levels.php"><i class="bi bi-collection me-2"></i>Classes</a></li>
                        <li><a class="dropdown-item" href="subjects.php"><i class="bi bi-journal-text me-2"></i>Subjects</a></li>
                        <li><a class="dropdown-item" href="lesson_planner.php"><i class="bi bi-book-half me-2"></i>Lesson Planner</a></li>
                        <li><a class="dropdown-item" href="assignments.php"><i class="bi bi-file-earmark-text me-2"></i>Assignments</a></li>
                        <li class="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#"><i class="bi bi-pencil-square me-2"></i>Exams</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="set_exam.php"><i class="bi bi-pencil me-2"></i>Set Exams</a></li>
                                <li><a class="dropdown-item" href="marks_entry.php"><i class="bi bi-card-checklist me-2"></i>Marks Entry</a></li>
                                <li><a class="dropdown-item" href="report_card_generator.php"><i class="bi bi-file-pdf me-2"></i>Report Cards</a></li>
                            </ul>
                        </li>
                        <li><a class="dropdown-item" href="grading_scales.php"><i class="bi bi-patch-check me-2"></i>Grading Scales</a></li>
                    </ul>
                </li>

                <!-- People Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="peopleDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-people-fill me-1"></i>People</a>
                    <ul class="dropdown-menu" aria-labelledby="peopleDropdown">
                        <li><a class="dropdown-item" href="students.php"><i class="bi bi-person me-2"></i>Students</a></li>
                        <li><a class="dropdown-item" href="teachers.php"><i class="bi bi-person-video me-2"></i>Teachers</a></li>
                        <li><a class="dropdown-item" href="health_record.php"><i class="bi bi-heart-pulse me-2"></i>Health Records</a></li>
                        <?php if ($is_admin): ?>
                            <li><a class="dropdown-item" href="users.php"><i class="bi bi-people me-2"></i>User Management</a></li>
                        <?php endif; ?>
                    </ul>
                </li>

                <!-- Finance Dropdown -->
                <?php if ($is_finance_user): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="financeDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-cash-coin me-1"></i>Finance</a>
                    <ul class="dropdown-menu" aria-labelledby="financeDropdown">
                        <li><a class="dropdown-item" href="finance_dashboard.php"><i class="bi bi-graph-up me-2"></i>Finance Dashboard</a></li>
                        <li><a class="dropdown-item" href="invoices.php"><i class="bi bi-receipt me-2"></i>Invoices</a></li>
                        <li><a class="dropdown-item" href="expenses.php"><i class="bi bi-graph-up-arrow me-2"></i>Expenses</a></li>
                        <li class="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#"><i class="bi bi-journal-check me-2"></i>Requisitions</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="make_requisition.php"><i class="bi bi-plus-square me-2"></i>Make a Requisition</a></li>
                                <li><a class="dropdown-item" href="view_requisitions.php"><i class="bi bi-list-ul me-2"></i>View Requisitions</a></li>
                            </ul>
                        </li>
                        <li><a class="dropdown-item" href="student_accounts.php"><i class="bi bi-person-rolodex me-2"></i>Student Accounts</a></li>
                        <li><a class="dropdown-item" href="finance_reports.php"><i class="bi bi-file-earmark-bar-graph me-2"></i>Finance Reports</a></li>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Library Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="libraryDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-book-half me-1"></i>Library</a>
                    <ul class="dropdown-menu" aria-labelledby="libraryDropdown">
                         <li><a class="dropdown-item" href="books.php"><i class="bi bi-book me-2"></i>Books</a></li>
                         <li><a class="dropdown-item" href="checkouts.php"><i class="bi bi-arrow-right-square me-2"></i>Checkouts</a></li>
                    </ul>
                </li>

                <!-- Resources Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="resourcesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-building me-1"></i>Resources</a>
                    <ul class="dropdown-menu" aria-labelledby="resourcesDropdown">
                         <li><a class="dropdown-item" href="bookings.php"><i class="bi bi-calendar-plus me-2"></i>Book a Resource</a></li>
                         <li><a class="dropdown-item" href="resources.php"><i class="bi bi-collection-fill me-2"></i>Manage Resources</a></li>
                         <?php if ($is_admin): ?>
                            <li><a class="dropdown-item" href="dormitories.php"><i class="bi bi-house-door me-2"></i>Dormitories</a></li>
                         <?php endif; ?>
                    </ul>
                </li>

                <!-- Communications Dropdown -->
                <?php if ($is_admin): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="communicationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-chat-dots me-1"></i>Communications</a>
                    <ul class="dropdown-menu" aria-labelledby="communicationsDropdown">
                        <li><a class="dropdown-item" href="messages.php"><i class="bi bi-chat-left-text me-2"></i>Direct Messages</a></li>
                        <li><a class="dropdown-item" href="announcements.php"><i class="bi bi-megaphone me-2"></i>Announcements</a></li>
                        <li><a class="dropdown-item" href="bulk_sms.php"><i class="bi bi-chat-right-text me-2"></i>Bulk SMS</a></li>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Administration Dropdown -->
                <?php if ($is_admin): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-gear me-1"></i>Administration</a>
                    <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                         <li><a class="dropdown-item" href="view_class_attendance.php"><i class="bi bi-check2-square me-2"></i>Attendance</a></li>
                         <li><a class="dropdown-item" href="discipline.php"><i class="bi bi-person-exclamation me-2"></i>Discipline</a></li>
                         <li><a class="dropdown-item" href="clubs.php"><i class="bi bi-collection-play me-2"></i>Clubs</a></li>
                         <li><a class="dropdown-item" href="calendar.php"><i class="bi bi-calendar3 me-2"></i>School Calendar</a></li>
                         <li><a class="dropdown-item" href="events.php"><i class="bi bi-calendar-event me-2"></i>Events</a></li>
                         <li><a class="dropdown-item" href="admin_leave_requests.php"><i class="bi bi-calendar-check me-2"></i>Leave Requests</a></li>
                         <?php if ($_SESSION['role'] === 'root'): ?>
                            <li><a class="dropdown-item" href="audit_trail.php"><i class="bi bi-file-earmark-zip me-2"></i>Audit Trail</a></li>
                         <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <?php endif; // End parent check ?>
            </ul>

            <!-- Right-aligned items -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                <!-- Search Bar -->
                <li class="nav-item me-2">
                    <div class="search-container">
                        <i class="bi bi-search"></i>
                        <input class="form-control form-control-sm navbar-search-input" type="search" id="live-search-input" placeholder="Search..." aria-label="Search" autocomplete="off">
                    </div>
                    <div class="list-group position-absolute" id="live-search-results" style="z-index: 1050; width: 300px;"></div>
                </li>
                <!-- Notifications Dropdown -->
                <li class="nav-item dropdown mx-2">
                    <a class="nav-link" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell-fill fs-5"></i>
                        <span class="position-absolute top-1 start-100 translate-middle badge rounded-pill bg-danger" id="notification-count-badge" style="display: none;">
                            <span id="notification-count">0</span>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown" id="notification-dropdown-menu" style="width: 350px;">
                        <li><a class="dropdown-item text-center" href="#">No new notifications</a></li>
                    </ul>
                </li>
                <!-- User Profile Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php if(isset($_SESSION["initials"])): ?>
                            <div class="avatar-initials-sm me-2">
                                <?php echo htmlspecialchars($_SESSION["initials"]); ?>
                            </div>
                        <?php endif; ?>
                        <span class="d-none d-lg-inline"><?php if (isset($_SESSION["name"])) echo htmlspecialchars($_SESSION["name"]); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person-circle me-2"></i>Profile</a></li>
                        <?php if ($user_role === 'teacher'): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="request_leave.php"><i class="bi bi-plus-circle me-2"></i>Request Leave</a></li>
                            <li><a class="dropdown-item" href="view_my_leave.php"><i class="bi bi-calendar-check me-2"></i>View My Leave</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="about.php"><i class="bi bi-info-circle me-2"></i>About</a></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sign out</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Toast Container from original navbar -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100; margin-top: 60px;"></div>

<!-- All JS from original navbar -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // JS for notifications and search bar
    const notificationBadge = document.getElementById('notification-count-badge');
    const notificationCount = document.getElementById('notification-count');
    const notificationMenu = document.getElementById('notification-dropdown-menu');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const toastContainer = document.querySelector('.toast-container');
    let lastNotificationCount = 0;

    function createToast(message) {
        const toastEl = document.createElement('div');
        toastEl.className = 'toast';
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');
        toastEl.innerHTML = `
            <div class="toast-header">
                <i class="bi bi-info-circle-fill me-2 text-primary"></i>
                <strong class="me-auto">New Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">${message}</div>
        `;
        toastContainer.appendChild(toastEl);
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    }

    function fetchNotifications() {
        fetch('api_check_notifications.php')
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error fetching notifications:', data.error);
                    return;
                }

                if (data.unread_count > 0) {
                    notificationCount.textContent = data.unread_count;
                    notificationBadge.style.display = 'inline-block';
                } else {
                    notificationBadge.style.display = 'none';
                }

                if (data.unread_count > lastNotificationCount) {
                    createToast(data.notifications[0].message);
                }
                lastNotificationCount = data.unread_count;

                notificationMenu.innerHTML = '';
                if (data.notifications.length > 0) {
                    data.notifications.forEach(notif => {
                        const item = document.createElement('li');
                        item.innerHTML = `<a class="dropdown-item" href="${notif.link}">
                            <div class="fw-bold">${notif.message}</div>
                            <div class="small text-muted">${new Date(notif.created_at).toLocaleString()}</div>
                        </a>`;
                        notificationMenu.appendChild(item);
                    });
                } else {
                    notificationMenu.innerHTML = '<li><a class="dropdown-item text-center" href="#">No new notifications</a></li>';
                }
            })
            .catch(error => console.error('Failed to fetch notifications:', error));
    }

    notificationDropdown.addEventListener('show.bs.dropdown', function () {
        if (lastNotificationCount > 0) {
            notificationBadge.style.display = 'none';
            lastNotificationCount = 0;
            fetch('api_check_notifications.php?mark_as_read=true');
        }
    });

    fetchNotifications();
    setInterval(fetchNotifications, 30000);
});
</script>
