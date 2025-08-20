<nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
  <div class="container-fluid">
    <!-- Offcanvas Toggler -->
    <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar" aria-controls="offcanvasSidebar">
        <span class="navbar-toggler-icon"></span>
    </button>

    <a class="navbar-brand" href="dashboard.php">
        <img src="images/logo.png" alt="Logo" style="height: 40px;">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
        <!-- Notifications Dropdown -->
        <li class="nav-item dropdown">
            <a class="nav-link" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell-fill fs-5"></i>
                <span class="position-absolute top-1 start-100 translate-middle badge rounded-pill bg-danger" id="notification-count-badge" style="display: none;">
                    <span id="notification-count">0</span>
                </span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown" id="notification-dropdown-menu" style="width: 350px;">
                <!-- Notifications will be loaded here by JavaScript -->
                <li><a class="dropdown-item text-center" href="#">No new notifications</a></li>
            </ul>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <?php if(isset($_SESSION["initials"])): ?>
                <div class="avatar-initials">
                    <?php echo htmlspecialchars($_SESSION["initials"]); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION["name"])) echo htmlspecialchars($_SESSION["name"]); ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
  <!-- Toasts will be appended here -->
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
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

                // Update badge
                if (data.unread_count > 0) {
                    notificationCount.textContent = data.unread_count;
                    notificationBadge.style.display = 'inline-block';
                } else {
                    notificationBadge.style.display = 'none';
                }

                // Show toast for new notifications
                if (data.unread_count > lastNotificationCount) {
                    createToast(data.notifications[0].message); // Show toast for the newest one
                }
                lastNotificationCount = data.unread_count;

                // Update dropdown menu
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

    // When dropdown is shown, mark notifications as read
    notificationDropdown.addEventListener('show.bs.dropdown', function () {
        if (lastNotificationCount > 0) {
            // Optimistically update UI
            notificationBadge.style.display = 'none';
            lastNotificationCount = 0;
            // Tell server to mark as read
            fetch('api_check_notifications.php?mark_as_read=true');
        }
    });

    // Fetch notifications on page load and then every 30 seconds
    fetchNotifications();
    setInterval(fetchNotifications, 30000);
});
</script>
