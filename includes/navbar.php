<nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">
        <img src="images/logo.png" alt="Logo" style="height: 20px;">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
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
