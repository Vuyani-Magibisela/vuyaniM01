<?php
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/core/Session.php';

use App\Core\Session;
?>
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <a href="<?php echo $baseUrl; ?>/admin" class="sidebar-logo">
            <i class="fas fa-code"></i>
            <span>Admin Panel</span>
        </a>
        <button class="sidebar-toggle" title="Toggle sidebar">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        <!-- Main Navigation -->
        <div class="nav-section">
            <div class="nav-section-title">Main</div>
            <a href="<?php echo $baseUrl; ?>/admin" class="nav-link">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="<?php echo $baseUrl; ?>/" class="nav-link" target="_blank">
                <i class="fas fa-external-link-alt"></i>
                <span>View Site</span>
            </a>
        </div>

        <!-- Content Management -->
        <div class="nav-section">
            <div class="nav-section-title">Content</div>
            <a href="<?php echo $baseUrl; ?>/admin/blog" class="nav-link">
                <i class="fas fa-blog"></i>
                <span>Blog Posts</span>
            </a>
            <a href="<?php echo $baseUrl; ?>/admin/categories" class="nav-link">
                <i class="fas fa-folder"></i>
                <span>Categories</span>
            </a>
            <a href="<?php echo $baseUrl; ?>/admin/projects" class="nav-link">
                <i class="fas fa-project-diagram"></i>
                <span>Projects</span>
            </a>
            <a href="<?php echo $baseUrl; ?>/admin/resources" class="nav-link">
                <i class="fas fa-download"></i>
                <span>Resources</span>
            </a>
        </div>

        <!-- Communication -->
        <div class="nav-section">
            <div class="nav-section-title">Communication</div>
            <a href="<?php echo $baseUrl; ?>/admin/contacts" class="nav-link">
                <i class="fas fa-envelope"></i>
                <span>Contact Messages</span>
            </a>
        </div>

        <!-- Settings -->
        <div class="nav-section">
            <div class="nav-section-title">Settings</div>
            <a href="<?php echo $baseUrl; ?>/admin/users" class="nav-link">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
            <button onclick="toggleTheme()" class="nav-link theme-toggle" style="border: none; background: none; width: 100%; text-align: left; cursor: pointer;">
                <i class="fas fa-moon"></i>
                <span>Toggle Theme</span>
            </button>
        </div>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar">
                <?php
                $userName = Session::get('username') ?? 'Admin';
                echo strtoupper(substr($userName, 0, 1));
                ?>
            </div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?php echo htmlspecialchars($userName); ?></div>
                <div class="sidebar-user-role"><?php echo ucfirst(Session::get('user_role') ?? 'admin'); ?></div>
            </div>
        </div>
        <a href="<?php echo $baseUrl; ?>/auth/logout" class="btn btn-danger btn-sm w-100">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</aside>
