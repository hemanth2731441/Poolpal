<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>

<nav class="navbar navbar-expand navbar-dark static-top admin-navbar">
    <a class="navbar-brand mr-1 brand-wrapper" href="admin_panel.php">
        <img src="/poolpal/images/poolpal.jpg" alt="Poolpal Logo" class="img-fluid logo-transition" style="max-height: 40px; width: auto;">
    </a>

    <button class="btn btn-link btn-sm text-white order-1 order-sm-0 sidebar-toggle" id="sidebarToggle" href="#">
      <i class="fas fa-bars"></i>
    </button>

    <!-- Navbar Search -->
    <form class="d-none d-md-inline-block form-inline ml-auto mr-0 mr-md-3 my-2 my-md-0">
      <div class="input-group">
        <input type="text" class="form-control search-input" placeholder="Search..." aria-label="Search" aria-describedby="basic-addon2">
        <div class="input-group-append">
          <button class="btn btn-primary search-btn" type="button">
            <i class="fas fa-search"></i>
          </button>
        </div>
      </div>
    </form>

    <!-- Navbar -->
    <ul class="navbar-nav ml-auto ml-md-0">
      <li class="nav-item dropdown no-arrow mx-1">
        <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i class="fas fa-bell fa-fw"></i>
          <span class="badge badge-danger">9+</span>
        </a>
        <div class="dropdown-menu dropdown-menu-right animated--grow-in" aria-labelledby="alertsDropdown">
          <h6 class="dropdown-header">Notifications</h6>
          <a class="dropdown-item" href="#">
            <i class="fas fa-car mr-2 text-primary"></i>New ride request
            <span class="small ml-2">11:21 AM</span>
          </a>
          <a class="dropdown-item" href="#">
            <i class="fas fa-user-plus mr-2 text-success"></i>New user registered
            <span class="small ml-2">10:30 AM</span>
          </a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>
        </div>
      </li>
      
      <li class="nav-item dropdown no-arrow">
        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i class="fas fa-user-circle fa-fw"></i>
          <span style="margin-left: 8px; font-weight: 500; color: #fff;">
            <?php echo isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Admin'; ?>
          </span>
        </a>
        <div class="dropdown-menu dropdown-menu-right animated--grow-in" aria-labelledby="userDropdown">
          <a class="dropdown-item" href="admin-profile.php">
            <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>Profile
          </a>
          <a class="dropdown-item" href="#">
            <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>Settings
          </a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>Logout
          </a>
        </div>
      </li>
    </ul>
</nav>

<style>
.admin-navbar {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.logo-transition {
    transition: transform 0.3s ease;
}

.logo-transition:hover {
    transform: scale(1.05);
}

.sidebar-toggle {
    transition: transform 0.3s ease;
}

.sidebar-toggle:hover {
    transform: rotate(90deg);
}

.search-input {
    border-radius: 20px 0 0 20px;
    border: none;
    background: rgba(255,255,255,0.1);
    color: white;
    transition: all 0.3s ease;
}

.search-input:focus {
    background: rgba(255,255,255,0.2);
    box-shadow: none;
}

.search-btn {
    border-radius: 0 20px 20px 0;
    background: rgba(255,255,255,0.1);
    border: none;
    transition: all 0.3s ease;
}

.search-btn:hover {
    background: rgba(255,255,255,0.2);
}

.animated--grow-in {
    animation: growIn 0.2s ease-in-out;
}

@keyframes growIn {
    0% {
        transform: scale(0.9);
        opacity: 0;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}
</style>