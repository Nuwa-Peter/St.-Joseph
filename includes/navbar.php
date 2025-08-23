<?php
// This file assumes session_start() has been called in the parent file
$user_role = $_SESSION['role'] ?? '';

// Define role groups for easier checking
$admin_roles = ['root', 'headteacher'];
$finance_roles = ['bursar', 'headteacher', 'root'];

$is_admin = in_array($user_role, $admin_roles);
$is_finance_user = in_array($user_role, $finance_roles);
$is_lab_attendant = $user_role === 'lab_attendant';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">
            <img src="images/logo.png" alt="Logo" style="height: 40px; border-radius: 50%;">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <!-- Main Navigation Links -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
                </li>

                <!-- Academics Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="academicsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-journal-bookmark me-1"></i> Academics
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="academicsDropdown">
                        <li><a class="dropdown-item" href="class_levels.php">Classes & Streams</a></li>
                        <li><a class="dropdown-item" href="subjects.php">Subjects</a></li>
                        <li><a class="dropdown-item" href="teacher_assignments.php">Teacher Assignments</a></li>
                        <li><a class="dropdown-item" href="student_assignments.php">Student Assignments</a></li>
                        <li><a class="dropdown-item" href="grading_scales.php">Grading Scales</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header">Exams Attendance</h6></li>
                        <li><a class="dropdown-item" href="exam_attendance.php">Take Exams Attendance</a></li>
                        <li><a class="dropdown-item" href="view_exam_attendance.php">View Exams Attendance Report</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header">Examinations</h6></li>
                        <li><a class="dropdown-item" href="set_exam.php">Set Exams</a></li>
                        <li><a class="dropdown-item" href="marks_entry.php">Marks Entry</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header">Documents</h6></li>
                        <li><a class="dropdown-item" href="report_card_generator.php">Generate Report Cards</a></li>
                        <li><a class="dropdown-item" href="id_card_generator.php">Generate ID Cards</a></li>
                    </ul>
                </li>

                <!-- Students Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="studentsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-rolodex me-1"></i> Students
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="studentsDropdown">
                        <li><a class="dropdown-item" href="students.php">All Students</a></li>
                        <li><a class="dropdown-item" href="student_create.php">Add Student</a></li>
                        <li><a class="dropdown-item" href="student_import_export.php">Import/Export</a></li>
                        <li><a class="dropdown-item" href="unregistered_students.php">Unregistered Students</a></li>
                    </ul>
                </li>

                <!-- Library Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="libraryDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-book-half me-1"></i> Library
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="libraryDropdown">
                         <li><a class="dropdown-item" href="books.php">Books</a></li>
                         <li><a class="dropdown-item" href="checkouts.php">Manage Checkouts</a></li>
                         <li><a class="dropdown-item" href="checkout_history.php">Checkout History</a></li>
                    </ul>
                </li>

                <!-- Files Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="filesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-folder-fill me-1"></i> Files
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="filesDropdown">
                        <!-- Requisitions Sub-menu -->
                        <li class="dropdown-submenu">
                             <a class="dropdown-item dropdown-toggle" href="#">Requisitions</a>
                             <ul class="dropdown-menu">
                                 <li><a class="dropdown-item" href="make_requisition.php">Make a Requisition</a></li>
                                 <li><a class="dropdown-item" href="view_requisitions.php">View Requisitions</a></li>
                             </ul>
                         </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="request_leave.php">Request Leave</a></li>
                        <li><a class="dropdown-item" href="view_my_leave.php">My Leave Requests</a></li>
                    </ul>
                </li>

                <!-- Finance Dropdown (Role-based) -->
                <?php if ($is_finance_user): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="financeDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-cash-coin me-1"></i> Finance
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="financeDropdown">
                        <li><a class="dropdown-item" href="finance_dashboard.php">Finance Dashboard</a></li>
                        <li><a class="dropdown-item" href="accountability.php">Accountability</a></li>
                        <li><a class="dropdown-item" href="fee_structures.php">Fee Structures</a></li>
                        <li><a class="dropdown-item" href="invoices.php">Invoices & Payments</a></li>
                        <li><a class="dropdown-item" href="student_accounts.php">Student Accounts</a></li>
                        <li><a class="dropdown-item" href="expenses.php">Expenses</a></li>
                        <li><a class="dropdown-item" href="finance_reports.php">Reports</a></li>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Admin Dropdown (Role-based) -->
                <?php if ($is_admin): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-gear me-1"></i> Admin
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                        <li><a class="dropdown-item" href="student_analytics.php">Student Analytics</a></li>
                        <li><a class="dropdown-item" href="admin_leave_requests.php">Manage Leave Requests</a></li>
                        <?php if ($_SESSION['role'] === 'root'): ?>
                        <li><a class="dropdown-item" href="audit_trail.php">System Audit Trail</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="users.php">User Management</a></li>
                        <li><a class="dropdown-item" href="teachers.php">Teachers</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header">Communications</h6></li>
                        <li><a class="dropdown-item" href="#">Social Chat</a></li>
                        <li><a class="dropdown-item" href="#">Bulk SMS</a></li>
                        <li><a class="dropdown-item" href="announcements.php">Announcements</a></li>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Laboratory Link (Role-based) -->
                <?php if ($is_lab_attendant): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="labDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-eyedropper me-1"></i> Laboratory
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="labDropdown">
                        <li><a class="dropdown-item" href="lab_dashboard.php">Lab Dashboard</a></li>
                        <li><a class="dropdown-item" href="lab_inventory.php">Manage Inventory</a></li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>

            <!-- Right-aligned items -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                <!-- Search Bar -->
                <li class="nav-item me-2">
                     <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input class="form-control form-control-sm" type="search" id="live-search-input" placeholder="Search" aria-label="Search" autocomplete="off">
                    </div>
                    <div class="list-group" id="live-search-results"></div>
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
                        <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                        <li><a class="dropdown-item" href="about.php">About</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">Sign out</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Toast Container from original navbar -->
<div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

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
