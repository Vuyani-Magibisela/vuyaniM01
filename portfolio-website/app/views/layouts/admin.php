<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Admin Panel'; ?> - Portfolio Admin</title>

    <?php
    require_once dirname(__DIR__, 2) . '/config/config.php';
    ?>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo $baseUrl; ?>/images/favicon/favicon-32x32.png">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Styles -->
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/main.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/admin.css">

    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar Navigation -->
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
                        <?php if (isset($stats['draft_posts']) && $stats['draft_posts'] > 0): ?>
                            <span class="nav-badge"><?php echo $stats['draft_posts']; ?></span>
                        <?php endif; ?>
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
                        <?php if (isset($stats['unread_contacts']) && $stats['unread_contacts'] > 0): ?>
                            <span class="nav-badge"><?php echo $stats['unread_contacts']; ?></span>
                        <?php endif; ?>
                    </a>
                </div>

                <!-- Settings -->
                <div class="nav-section">
                    <div class="nav-section-title">Settings</div>
                    <a href="<?php echo $baseUrl; ?>/admin/users" class="nav-link">
                        <i class="fas fa-users"></i>
                        <span>Users</span>
                    </a>
                    <button onclick="toggleTheme()" class="nav-link theme-toggle" style="border: none; background: none; width: 100%; text-align: left;">
                        <i class="fas fa-moon"></i>
                        <span>Toggle Theme</span>
                    </button>
                </div>
            </nav>

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="sidebar-user-avatar">
                        <?php
                        $userName = $_SESSION['user_name'] ?? 'Admin';
                        echo strtoupper(substr($userName, 0, 1));
                        ?>
                    </div>
                    <div class="sidebar-user-info">
                        <div class="sidebar-user-name"><?php echo htmlspecialchars($userName); ?></div>
                        <div class="sidebar-user-role"><?php echo ucfirst($_SESSION['user_role'] ?? 'admin'); ?></div>
                    </div>
                </div>
                <a href="<?php echo $baseUrl; ?>/auth/logout" class="btn btn-danger btn-sm w-100">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="admin-main">
            <!-- Header -->
            <header class="admin-header">
                <div class="admin-header-left">
                    <button class="mobile-menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1><?php echo $pageTitle ?? $title ?? 'Admin Panel'; ?></h1>
                </div>
                <div class="admin-header-right">
                    <?php if (isset($headerActions)): ?>
                        <?php echo $headerActions; ?>
                    <?php endif; ?>
                </div>
            </header>

            <!-- Content -->
            <div class="admin-content">
                <?php if (isset($success) && $success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error) && $error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($warning) && $warning): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo htmlspecialchars($warning); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($info) && $info): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <?php echo htmlspecialchars($info); ?>
                    </div>
                <?php endif; ?>

                <!-- Main Content -->
                <?php echo $content ?? ''; ?>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="<?php echo $baseUrl; ?>/js/admin.js"></script>

    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (isset($inlineJS)): ?>
        <script>
            <?php echo $inlineJS; ?>
        </script>
    <?php endif; ?>
</body>
</html>
