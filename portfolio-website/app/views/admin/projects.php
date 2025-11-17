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
    <title><?php echo $title ?? 'Manage Projects'; ?> - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/main.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/admin.css">
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
                    <h1>Manage Projects</h1>
                </div>
                <div class="admin-header-right">
                    <a href="<?php echo $baseUrl; ?>/admin/createProject" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Project
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

                <?php if (isset($error) && $error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="card">
                    <form method="GET" action="<?php echo $baseUrl; ?>/admin/projects" class="d-flex gap-2 align-items-center" style="flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 250px;">
                            <input
                                type="text"
                                name="search"
                                class="form-control"
                                placeholder="Search projects..."
                                value="<?php echo htmlspecialchars($search ?? ''); ?>"
                            >
                        </div>

                        <select name="status" class="form-control" style="width: 150px;">
                            <option value="">All Status</option>
                            <option value="draft" <?php echo ($status ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo ($status ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                        </select>

                        <select name="category" class="form-control" style="width: 200px;">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($categoryId ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>

                        <?php if (!empty($search) || !empty($status) || !empty($categoryId)): ?>
                            <a href="<?php echo $baseUrl; ?>/admin/projects" class="btn btn-outline">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Stats Summary -->
                <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 2rem;">
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div>
                                <div class="stat-card-value"><?php echo $totalProjects; ?></div>
                                <div class="stat-card-label">Total Projects</div>
                            </div>
                            <div class="stat-card-icon blue">
                                <i class="fas fa-project-diagram"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Projects Table -->
                <?php if (empty($projects)): ?>
                    <div class="empty-state">
                        <i class="fas fa-project-diagram"></i>
                        <h3>No projects found</h3>
                        <p>
                            <?php if (!empty($search) || !empty($status) || !empty($categoryId)): ?>
                                No projects match your filters. Try adjusting your search.
                            <?php else: ?>
                                Get started by creating your first project!
                            <?php endif; ?>
                        </p>
                        <a href="<?php echo $baseUrl; ?>/admin/createProject" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Project
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">Image</th>
                                    <th>Title</th>
                                    <th style="width: 150px;">Category</th>
                                    <th style="width: 120px;">Status</th>
                                    <th style="width: 100px;">Featured</th>
                                    <th style="width: 80px;">Images</th>
                                    <th style="width: 150px;">Date</th>
                                    <th style="width: 150px;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($projects as $project): ?>
                                    <tr>
                                        <td>
                                            <?php if ($project['featured_image']): ?>
                                                <img
                                                    src="<?php echo $baseUrl . htmlspecialchars($project['featured_image']); ?>"
                                                    alt="<?php echo htmlspecialchars($project['title']); ?>"
                                                    style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;"
                                                >
                                            <?php else: ?>
                                                <div style="width: 50px; height: 50px; background: var(--border-color); border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-image" style="color: var(--text-muted);"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($project['title']); ?></strong>
                                            <?php if ($project['client']): ?>
                                                <br>
                                                <small style="color: var(--text-muted);">
                                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($project['client']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($project['category_name']): ?>
                                                <span class="badge badge-secondary">
                                                    <?php echo htmlspecialchars($project['category_name']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $project['status'] === 'published' ? 'published' : 'draft'; ?>">
                                                <?php echo ucfirst($project['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button
                                                class="btn btn-sm btn-icon featured-toggle <?php echo $project['is_featured'] ? 'active' : ''; ?>"
                                                data-project-id="<?php echo $project['id']; ?>"
                                                title="<?php echo $project['is_featured'] ? 'Remove from featured' : 'Mark as featured'; ?>"
                                                style="background: <?php echo $project['is_featured'] ? '#f59e0b' : 'var(--border-color)'; ?>; color: white;"
                                            >
                                                <i class="fas fa-star"></i>
                                            </button>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                <i class="fas fa-images"></i> <?php echo $project['image_count'] ?? 0; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small style="color: var(--text-muted);">
                                                <?php echo date('M d, Y', strtotime($project['created_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="table-actions">
                                                <a
                                                    href="<?php echo $baseUrl; ?>/admin/editProject/<?php echo $project['id']; ?>"
                                                    class="btn btn-sm btn-primary btn-icon"
                                                    title="Edit"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a
                                                    href="<?php echo $baseUrl; ?>/admin/deleteProject/<?php echo $project['id']; ?>"
                                                    class="btn btn-sm btn-danger btn-icon"
                                                    onclick="return confirm('Are you sure you want to delete this project? This will also delete all associated images.');"
                                                    title="Delete"
                                                >
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php
                            $queryParams = [];
                            if (!empty($search)) $queryParams['search'] = $search;
                            if (!empty($status)) $queryParams['status'] = $status;
                            if (!empty($categoryId)) $queryParams['category'] = $categoryId;
                            $queryString = !empty($queryParams) ? '&' . http_build_query($queryParams) : '';
                            ?>

                            <?php if ($currentPage > 1): ?>
                                <a href="?page=1<?php echo $queryString; ?>">First</a>
                                <a href="?page=<?php echo $currentPage - 1; ?><?php echo $queryString; ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php else: ?>
                                <span class="disabled">First</span>
                                <span class="disabled"><i class="fas fa-chevron-left"></i></span>
                            <?php endif; ?>

                            <?php
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($totalPages, $currentPage + 2);

                            for ($i = $startPage; $i <= $endPage; $i++):
                            ?>
                                <a
                                    href="?page=<?php echo $i; ?><?php echo $queryString; ?>"
                                    class="<?php echo $i === $currentPage ? 'active' : ''; ?>"
                                >
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($currentPage < $totalPages): ?>
                                <a href="?page=<?php echo $currentPage + 1; ?><?php echo $queryString; ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                                <a href="?page=<?php echo $totalPages; ?><?php echo $queryString; ?>">Last</a>
                            <?php else: ?>
                                <span class="disabled"><i class="fas fa-chevron-right"></i></span>
                                <span class="disabled">Last</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="<?php echo $baseUrl; ?>/js/admin.js"></script>
    <script>
        // Featured toggle functionality
        document.querySelectorAll('.featured-toggle').forEach(toggle => {
            toggle.addEventListener('click', async function() {
                const projectId = this.dataset.projectId;
                const button = this;

                try {
                    const response = await fetch('<?php echo $baseUrl; ?>/admin/toggleFeatured/' + projectId + '?type=project', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        button.classList.toggle('active');
                        const isActive = button.classList.contains('active');
                        button.style.background = isActive ? '#f59e0b' : 'var(--border-color)';
                        button.title = isActive ? 'Remove from featured' : 'Mark as featured';
                    } else {
                        alert('Failed to toggle featured status');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred');
                }
            });
        });
    </script>
</body>
</html>
