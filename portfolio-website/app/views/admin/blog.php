<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Manage Blog'; ?></title>

    <?php
    require_once dirname(__DIR__, 2) . '/config/config.php';
    ?>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo $baseUrl; ?>/images/favicon/favicon-32x32.png">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Styles -->
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/main.css">

    <style>
        .admin-container {
            min-height: 100vh;
            background: var(--bg-color);
            padding: 2rem;
        }

        .admin-header {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            color: #16a34a;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .filters {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-color);
            font-weight: 500;
        }

        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background: var(--bg-color);
            color: var(--text-color);
        }

        .table-container {
            background: var(--card-bg);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-color);
        }

        .table th {
            background: var(--bg-color);
            font-weight: 600;
        }

        .table tr:hover {
            background: rgba(59, 130, 246, 0.05);
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-draft {
            background: #f59e0b;
            color: white;
        }

        .badge-published {
            background: #10b981;
            color: white;
        }

        .featured-toggle {
            cursor: pointer;
            font-size: 1.25rem;
            transition: transform 0.2s;
        }

        .featured-toggle:hover {
            transform: scale(1.2);
        }

        .featured-toggle.active {
            color: #f59e0b;
        }

        .actions {
            display: flex;
            gap: 0.5rem;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            padding: 2rem;
        }

        .pagination a,
        .pagination span {
            padding: 0.5rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-color);
            text-decoration: none;
            transition: all 0.2s;
        }

        .pagination a:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .pagination .active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <div class="admin-container">
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

        <div class="admin-header">
            <div>
                <h1 style="color: var(--text-color); margin-bottom: 0.5rem;">Manage Blog Posts</h1>
                <p style="color: var(--text-muted);">Total: <?php echo $totalPosts; ?> post(s)</p>
            </div>
            <div style="display: flex; gap: 1rem;">
                <a href="<?php echo $baseUrl; ?>/admin/categories" class="btn btn-sm" style="background: #8b5cf6; color: white;">
                    <i class="fas fa-folder"></i> Categories
                </a>
                <a href="<?php echo $baseUrl; ?>/admin/createBlogPost" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Post
                </a>
                <a href="<?php echo $baseUrl; ?>/admin" class="btn btn-sm" style="background: var(--card-bg); color: var(--text-color); border: 1px solid var(--border-color);">
                    <i class="fas fa-arrow-left"></i> Dashboard
                </a>
            </div>
        </div>

        <!-- Filters -->
        <form method="GET" action="<?php echo $baseUrl; ?>/admin/blog" class="filters">
            <div class="filter-group">
                <label for="search">Search</label>
                <input type="text" id="search" name="search" placeholder="Search by title..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
            </div>
            <div class="filter-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All</option>
                    <option value="draft" <?php echo ($status === 'draft') ? 'selected' : ''; ?>>Draft</option>
                    <option value="published" <?php echo ($status === 'published') ? 'selected' : ''; ?>>Published</option>
                </select>
            </div>
            <div class="filter-group" style="display: flex; align-items: flex-end;">
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-search"></i> Filter
                </button>
            </div>
        </form>

        <!-- Posts Table -->
        <div class="table-container">
            <?php if (empty($posts)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3 style="color: var(--text-color); margin-bottom: 0.5rem;">No posts found</h3>
                    <p>Create your first blog post to get started!</p>
                    <a href="<?php echo $baseUrl; ?>/admin/createBlogPost" class="btn btn-primary" style="margin-top: 1rem;">
                        <i class="fas fa-plus"></i> Create Post
                    </a>
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th style="text-align: center;">Featured</th>
                            <th style="text-align: center;">Views</th>
                            <th>Date</th>
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($post['title']); ?></strong><br>
                                    <small style="color: var(--text-muted);">
                                        by <?php echo htmlspecialchars($post['author_name'] ?? 'Unknown'); ?>
                                    </small>
                                </td>
                                <td><?php echo htmlspecialchars($post['category_name'] ?? 'Uncategorized'); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $post['status']; ?>">
                                        <?php echo ucfirst($post['status']); ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <i class="fas fa-star featured-toggle <?php echo $post['is_featured'] ? 'active' : ''; ?>"
                                       data-post-id="<?php echo $post['id']; ?>"
                                       title="<?php echo $post['is_featured'] ? 'Remove from featured' : 'Mark as featured'; ?>"></i>
                                </td>
                                <td style="text-align: center;"><?php echo number_format($post['views']); ?></td>
                                <td>
                                    <small><?php echo date('M d, Y', strtotime($post['created_at'])); ?></small>
                                </td>
                                <td>
                                    <div class="actions" style="justify-content: center;">
                                        <a href="<?php echo $baseUrl; ?>/admin/editBlogPost/<?php echo $post['id']; ?>"
                                           class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo $baseUrl; ?>/admin/deleteBlogPost/<?php echo $post['id']; ?>"
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this post?')"
                                           title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($currentPage > 1): ?>
                            <a href="?page=<?php echo $currentPage - 1; ?><?php echo $status ? '&status=' . $status : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i == $currentPage): ?>
                                <span class="active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?><?php echo $status ? '&status=' . $status : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <a href="?page=<?php echo $currentPage + 1; ?><?php echo $status ? '&status=' . $status : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Theme management
        const html = document.documentElement;
        const savedTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-theme', savedTheme);

        // Featured toggle
        document.querySelectorAll('.featured-toggle').forEach(toggle => {
            toggle.addEventListener('click', async function() {
                const postId = this.dataset.postId;
                const isActive = this.classList.contains('active');

                try {
                    const response = await fetch(`<?php echo $baseUrl; ?>/admin/toggleFeatured/${postId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.classList.toggle('active');
                        this.title = isActive ? 'Mark as featured' : 'Remove from featured';
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
