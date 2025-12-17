<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Manage Categories'; ?></title>

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

    <!-- Theme Initializer - Load immediately to prevent flash -->
    <script src="<?php echo $baseUrl; ?>/js/theme-init.js"></script>

    <style>
        /* Ensure CSS variables are defined */
        :root {
            --bg-color: #f5f5f5;
            --card-bg: #ffffff;
            --text-color: #1f2937;
            --text-muted: #6b7280;
            --border-color: #d1d5db;
            --input-border: #9ca3af;
            --input-bg: #ffffff;
            --primary-color: #3b82f6;
        }

        [data-theme="dark"] {
            --bg-color: #1a1a1a;
            --card-bg: #2d2d2d;
            --text-color: #f9f9f9;
            --text-muted: #9ca3af;
            --border-color: #404040;
            --input-border: #555555;
            --input-bg: #1f1f1f;
            --primary-color: #3b82f6;
        }

        .admin-container {
            min-height: 100vh;
            background: var(--bg-color);
            padding: 2rem;
            max-width: 1000px;
            margin: 0 auto;
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
            border: none !important;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1) !important;
        }

        .btn-primary {
            background: var(--primary-color) !important;
            color: white !important;
        }

        .btn-primary:hover {
            background: #2563eb !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3) !important;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        .btn-danger {
            background: #ef4444 !important;
            color: white !important;
        }

        .btn-danger:hover {
            background: #dc2626 !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3) !important;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
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

        .card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 2rem;
            border: 1px solid var(--border-color);
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-color);
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--input-border) !important;
            border-radius: 8px;
            font-size: 1rem;
            background: var(--input-bg) !important;
            color: var(--text-color) !important;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1) !important;
            transition: all 0.3s;
        }

        .form-control:hover {
            border-color: var(--text-muted) !important;
        }

        .form-control:focus {
            outline: none !important;
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1), 0 1px 3px 0 rgba(0, 0, 0, 0.1) !important;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
            font-family: inherit;
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

        .actions {
            display: flex;
            gap: 0.5rem;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            background: var(--primary-color);
            color: white;
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
                <h1 style="color: var(--text-color); margin-bottom: 0.5rem;">Manage Categories</h1>
                <p style="color: var(--text-muted);">Organize your blog posts into categories</p>
            </div>
            <div>
                <a href="<?php echo $baseUrl; ?>/admin/blog" class="btn" style="background: var(--card-bg); color: var(--text-color); border: 1px solid var(--border-color);">
                    <i class="fas fa-arrow-left"></i> Back to Posts
                </a>
            </div>
        </div>

        <!-- Create New Category Form -->
        <div class="card">
            <h2 style="color: var(--text-color); margin-bottom: 1.5rem;">
                <i class="fas fa-plus-circle"></i> Create New Category
            </h2>

            <form method="POST" action="<?php echo $baseUrl; ?>/admin/storeCategory">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="name">Category Name *</label>
                        <input type="text"
                               id="name"
                               name="name"
                               class="form-control"
                               placeholder="e.g., Tutorials"
                               required>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="slug">Slug (optional)</label>
                        <input type="text"
                               id="slug"
                               name="slug"
                               class="form-control"
                               placeholder="Auto-generated from name"
                               pattern="[a-z0-9-]+">
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description"
                              name="description"
                              class="form-control"
                              rows="2"
                              placeholder="Brief description of this category"></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Category
                </button>
            </form>
        </div>

        <!-- Categories List -->
        <div class="table-container">
            <?php if (empty($categories)): ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <h3 style="color: var(--text-color); margin-bottom: 0.5rem;">No categories yet</h3>
                    <p>Create your first category to organize your blog posts!</p>
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th style="text-align: center;">Posts</th>
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                </td>
                                <td>
                                    <code style="background: var(--bg-color); padding: 0.25rem 0.5rem; border-radius: 4px;">
                                        <?php echo htmlspecialchars($category['slug']); ?>
                                    </code>
                                </td>
                                <td>
                                    <small style="color: var(--text-muted);">
                                        <?php echo htmlspecialchars($category['description'] ?? 'No description'); ?>
                                    </small>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge">
                                        <?php echo $category['post_count'] ?? 0; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="actions" style="justify-content: center;">
                                        <button class="btn btn-sm btn-primary"
                                                onclick="editCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($category['slug'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($category['description'] ?? '', ENT_QUOTES); ?>')"
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="<?php echo $baseUrl; ?>/admin/deleteCategory/<?php echo $category['id']; ?>"
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this category? This action cannot be undone if the category has posts.')"
                                           title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Category Modal (Simple overlay) -->
    <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
        <div style="background: var(--card-bg); border-radius: 12px; padding: 2rem; max-width: 500px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
            <h3 style="color: var(--text-color); margin-bottom: 1.5rem;">
                <i class="fas fa-edit"></i> Edit Category
            </h3>

            <form method="POST" id="editForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="form-group">
                    <label for="edit_name">Category Name *</label>
                    <input type="text"
                           id="edit_name"
                           name="name"
                           class="form-control"
                           required>
                </div>

                <div class="form-group">
                    <label for="edit_slug">Slug *</label>
                    <input type="text"
                           id="edit_slug"
                           name="slug"
                           class="form-control"
                           pattern="[a-z0-9-]+"
                           required>
                </div>

                <div class="form-group">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description"
                              name="description"
                              class="form-control"
                              rows="2"></textarea>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" class="btn" style="background: #6b7280; color: white;" onclick="closeEditModal()">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Theme management
        const html = document.documentElement;
        const savedTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-theme', savedTheme);

        // Auto-generate slug from name
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');

        nameInput.addEventListener('input', () => {
            if (!slugInput.value) {
                slugInput.value = generateSlug(nameInput.value);
            }
        });

        function generateSlug(text) {
            return text
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
        }

        // Edit category modal
        const editModal = document.getElementById('editModal');
        const editForm = document.getElementById('editForm');

        function editCategory(id, name, slug, description) {
            editForm.action = '<?php echo $baseUrl; ?>/admin/updateCategory/' + id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_slug').value = slug;
            document.getElementById('edit_description').value = description;
            editModal.style.display = 'flex';
        }

        function closeEditModal() {
            editModal.style.display = 'none';
        }

        // Close modal on outside click
        editModal.addEventListener('click', (e) => {
            if (e.target === editModal) {
                closeEditModal();
            }
        });

        // Close modal on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && editModal.style.display === 'flex') {
                closeEditModal();
            }
        });
    </script>
</body>
</html>
