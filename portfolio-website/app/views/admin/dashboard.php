<?php
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/core/Session.php';

use App\Core\Session;

// Check if user is authenticated
if (!Session::isAuthenticated()) {
    header('Location: ' . $baseUrl . '/auth/login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Admin Dashboard'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/main.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/admin.css">
    <style>
        .welcome-banner {
            background: linear-gradient(135deg, var(--primary-color) 0%, #2563eb 100%);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            color: white;
            box-shadow: 0 10px 40px rgba(59, 130, 246, 0.3);
        }

        .welcome-banner h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
        }

        .welcome-banner p {
            margin: 0;
            opacity: 0.9;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .quick-action-btn {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            text-decoration: none;
            color: var(--text-color);
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
        }

        .quick-action-btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            border-color: var(--primary-color);
        }

        .quick-action-btn i {
            width: 48px;
            height: 48px;
            background: var(--primary-color);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .quick-action-btn span {
            font-weight: 600;
        }

        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .content-section {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
        }

        .content-section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .content-section-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .view-all-link {
            font-size: 0.875rem;
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .view-all-link:hover {
            text-decoration: underline;
        }

        .recent-item {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 0.75rem;
            background: var(--bg-color);
            transition: all 0.2s ease;
        }

        .recent-item:hover {
            background: rgba(59, 130, 246, 0.05);
            transform: translateX(4px);
        }

        .recent-item-title {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .recent-item-meta {
            font-size: 0.75rem;
            color: var(--text-muted);
            display: flex;
            gap: 1rem;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .content-grid {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <?php include dirname(__DIR__) . '/partials/admin_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Header -->
            <header class="admin-header">
                <div class="admin-header-left">
                    <button class="mobile-menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1>Dashboard</h1>
                </div>
                <div class="admin-header-right">
                    <a href="<?php echo $baseUrl; ?>/" class="btn btn-outline" target="_blank">
                        <i class="fas fa-external-link-alt"></i> View Site
                    </a>
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

                <!-- Welcome Banner -->
                <div class="welcome-banner">
                    <h1>Welcome back, <?php echo htmlspecialchars($username ?? 'Admin'); ?>! 👋</h1>
                    <p>Here's what's happening with your portfolio today.</p>
                </div>

                <!-- Statistics Grid -->
                <div class="stats-grid">
                    <!-- Blog Posts -->
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div>
                                <div class="stat-card-value"><?php echo $stats['total_posts']; ?></div>
                                <div class="stat-card-label">Blog Posts</div>
                            </div>
                            <div class="stat-card-icon blue">
                                <i class="fas fa-blog"></i>
                            </div>
                        </div>
                        <div class="stat-card-footer">
                            <span class="stat-card-trend up">
                                <i class="fas fa-check-circle"></i>
                                <?php echo $stats['published_posts']; ?> published
                            </span>
                            <span style="color: var(--text-muted);">
                                <?php echo $stats['draft_posts']; ?> drafts
                            </span>
                        </div>
                    </div>

                    <!-- Projects -->
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div>
                                <div class="stat-card-value"><?php echo $stats['total_projects']; ?></div>
                                <div class="stat-card-label">Projects</div>
                            </div>
                            <div class="stat-card-icon green">
                                <i class="fas fa-project-diagram"></i>
                            </div>
                        </div>
                        <div class="stat-card-footer">
                            <span class="stat-card-trend up">
                                <i class="fas fa-check-circle"></i>
                                <?php echo $stats['published_projects']; ?> published
                            </span>
                            <span style="color: var(--text-muted);">
                                <?php echo $stats['draft_projects']; ?> drafts
                            </span>
                        </div>
                    </div>

                    <!-- Resources -->
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div>
                                <div class="stat-card-value"><?php echo $stats['total_resources']; ?></div>
                                <div class="stat-card-label">Resources</div>
                            </div>
                            <div class="stat-card-icon purple">
                                <i class="fas fa-download"></i>
                            </div>
                        </div>
                        <div class="stat-card-footer">
                            <span class="stat-card-trend up">
                                <i class="fas fa-check-circle"></i>
                                <?php echo $stats['published_resources']; ?> published
                            </span>
                            <span style="color: var(--text-muted);">
                                <?php echo $stats['draft_resources']; ?> drafts
                            </span>
                        </div>
                    </div>

                    <!-- Contacts -->
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div>
                                <div class="stat-card-value"><?php echo $stats['total_contacts']; ?></div>
                                <div class="stat-card-label">Messages</div>
                            </div>
                            <div class="stat-card-icon orange">
                                <i class="fas fa-envelope"></i>
                            </div>
                        </div>
                        <div class="stat-card-footer">
                            <?php if ($stats['unread_contacts'] > 0): ?>
                                <span class="stat-card-trend" style="color: #ef4444;">
                                    <i class="fas fa-bell"></i>
                                    <?php echo $stats['unread_contacts']; ?> unread
                                </span>
                            <?php else: ?>
                                <span class="stat-card-trend up">
                                    <i class="fas fa-check-circle"></i>
                                    All read
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <h2 style="color: var(--text-color); margin-bottom: 1rem; font-size: 1.5rem;">
                    <i class="fas fa-bolt"></i> Quick Actions
                </h2>
                <div class="quick-actions">
                    <a href="<?php echo $baseUrl; ?>/admin/createBlogPost" class="quick-action-btn">
                        <i class="fas fa-plus"></i>
                        <span>New Blog Post</span>
                    </a>
                    <a href="<?php echo $baseUrl; ?>/admin/createProject" class="quick-action-btn">
                        <i class="fas fa-plus"></i>
                        <span>New Project</span>
                    </a>
                    <a href="<?php echo $baseUrl; ?>/admin/createResource" class="quick-action-btn">
                        <i class="fas fa-upload"></i>
                        <span>Upload Resource</span>
                    </a>
                    <a href="<?php echo $baseUrl; ?>/admin/contacts" class="quick-action-btn">
                        <i class="fas fa-envelope"></i>
                        <span>View Messages</span>
                        <?php if ($stats['unread_contacts'] > 0): ?>
                            <span class="badge badge-danger"><?php echo $stats['unread_contacts']; ?></span>
                        <?php endif; ?>
                    </a>
                </div>

                <!-- Recent Activity -->
                <h2 style="color: var(--text-color); margin-bottom: 1rem; font-size: 1.5rem;">
                    <i class="fas fa-clock"></i> Recent Activity
                </h2>
                <div class="content-grid">
                    <!-- Recent Blog Posts -->
                    <div class="content-section">
                        <div class="content-section-header">
                            <div class="content-section-title">
                                <i class="fas fa-blog"></i>
                                Recent Posts
                            </div>
                            <a href="<?php echo $baseUrl; ?>/admin/blog" class="view-all-link">
                                View all <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                        <?php if (empty($recentPosts)): ?>
                            <p style="color: var(--text-muted); text-align: center; padding: 2rem;">
                                No blog posts yet. <a href="<?php echo $baseUrl; ?>/admin/createBlogPost" style="color: var(--primary-color);">Create your first post</a>
                            </p>
                        <?php else: ?>
                            <?php foreach ($recentPosts as $post): ?>
                                <div class="recent-item">
                                    <div class="recent-item-title">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                        <span class="badge badge-<?php echo $post['status'] === 'published' ? 'published' : 'draft'; ?>" style="margin-left: auto;">
                                            <?php echo ucfirst($post['status']); ?>
                                        </span>
                                    </div>
                                    <div class="recent-item-meta">
                                        <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                                        <span><i class="fas fa-folder"></i> <?php echo htmlspecialchars($post['category_name'] ?? 'Uncategorized'); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Recent Projects -->
                    <div class="content-section">
                        <div class="content-section-header">
                            <div class="content-section-title">
                                <i class="fas fa-project-diagram"></i>
                                Recent Projects
                            </div>
                            <a href="<?php echo $baseUrl; ?>/admin/projects" class="view-all-link">
                                View all <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                        <?php if (empty($recentProjects)): ?>
                            <p style="color: var(--text-muted); text-align: center; padding: 2rem;">
                                No projects yet. <a href="<?php echo $baseUrl; ?>/admin/createProject" style="color: var(--primary-color);">Create your first project</a>
                            </p>
                        <?php else: ?>
                            <?php foreach ($recentProjects as $project): ?>
                                <div class="recent-item">
                                    <div class="recent-item-title">
                                        <?php echo htmlspecialchars($project['title']); ?>
                                        <span class="badge badge-<?php echo $project['status'] === 'published' ? 'published' : 'draft'; ?>" style="margin-left: auto;">
                                            <?php echo ucfirst($project['status']); ?>
                                        </span>
                                    </div>
                                    <div class="recent-item-meta">
                                        <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($project['created_at'])); ?></span>
                                        <?php if ($project['client']): ?>
                                            <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($project['client']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Recent Contact Messages -->
                    <div class="content-section">
                        <div class="content-section-header">
                            <div class="content-section-title">
                                <i class="fas fa-envelope"></i>
                                Recent Messages
                            </div>
                            <a href="<?php echo $baseUrl; ?>/admin/contacts" class="view-all-link">
                                View all <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                        <?php if (empty($recentContacts)): ?>
                            <p style="color: var(--text-muted); text-align: center; padding: 2rem;">
                                No messages yet.
                            </p>
                        <?php else: ?>
                            <?php foreach ($recentContacts as $contact): ?>
                                <div class="recent-item">
                                    <div class="recent-item-title">
                                        <?php echo htmlspecialchars($contact['name']); ?>
                                        <?php if (!$contact['is_read']): ?>
                                            <span class="badge badge-primary" style="margin-left: auto;">New</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="recent-item-meta">
                                        <span><i class="fas fa-clock"></i> <?php echo date('M d, Y g:i A', strtotime($contact['created_at'])); ?></span>
                                        <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($contact['email']); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- System Info -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle"></i>
                            System Information
                        </h3>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                            <div>
                                <div style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 0.25rem;">Logged in as</div>
                                <div style="font-weight: 600; color: var(--text-color);"><?php echo htmlspecialchars($username); ?></div>
                                <div style="font-size: 0.875rem; color: var(--text-muted);"><?php echo htmlspecialchars($email); ?></div>
                            </div>
                            <div>
                                <div style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 0.25rem;">Role</div>
                                <div style="font-weight: 600; color: var(--text-color);">
                                    <span class="badge badge-primary"><?php echo ucfirst($role); ?></span>
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 0.25rem;">Admin Users</div>
                                <div style="font-weight: 600; color: var(--text-color);"><?php echo $stats['total_users']; ?> users</div>
                            </div>
                            <div>
                                <div style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 0.25rem;">Portfolio Status</div>
                                <div style="font-weight: 600; color: #10b981;">
                                    <i class="fas fa-check-circle"></i> Active
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="<?php echo $baseUrl; ?>/js/admin.js"></script>
</body>
</html>
