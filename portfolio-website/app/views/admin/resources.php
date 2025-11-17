<?php
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/core/Session.php';

use App\Core\Session;

// Check if user is authenticated
if (!Session::isAuthenticated()) {
    header('Location: ' . $baseUrl . '/auth/login');
    exit;
}

// Helper function for file size formatting
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Manage Resources'; ?> - Admin</title>
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
                    <h1>Manage Resources</h1>
                </div>
                <div class="admin-header-right">
                    <a href="<?php echo $baseUrl; ?>/admin/createResource" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Resource
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
                    <form method="GET" action="<?php echo $baseUrl; ?>/admin/resources" class="d-flex gap-2 align-items-center" style="flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 250px;">
                            <input
                                type="text"
                                name="search"
                                class="form-control"
                                placeholder="Search resources..."
                                value="<?php echo htmlspecialchars($search ?? ''); ?>"
                            >
                        </div>

                        <select name="status" class="form-control" style="width: 150px;">
                            <option value="">All Status</option>
                            <option value="draft" <?php echo ($status ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo ($status ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                        </select>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>

                        <?php if (!empty($search) || !empty($status)): ?>
                            <a href="<?php echo $baseUrl; ?>/admin/resources" class="btn btn-outline">
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
                                <div class="stat-card-value"><?php echo $totalResources; ?></div>
                                <div class="stat-card-label">Total Resources</div>
                            </div>
                            <div class="stat-card-icon purple">
                                <i class="fas fa-download"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resources Table -->
                <?php if (empty($resources)): ?>
                    <div class="empty-state">
                        <i class="fas fa-download"></i>
                        <h3>No resources found</h3>
                        <p>
                            <?php if (!empty($search) || !empty($status)): ?>
                                No resources match your filters. Try adjusting your search.
                            <?php else: ?>
                                Get started by uploading your first resource!
                            <?php endif; ?>
                        </p>
                        <a href="<?php echo $baseUrl; ?>/admin/createResource" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Resource
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">Icon</th>
                                    <th>Title</th>
                                    <th style="width: 150px;">File Type</th>
                                    <th style="width: 100px;">Size</th>
                                    <th style="width: 100px;">Downloads</th>
                                    <th style="width: 120px;">Status</th>
                                    <th style="width: 100px;">Login Req.</th>
                                    <th style="width: 150px;">Date</th>
                                    <th style="width: 150px;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($resources as $resource): ?>
                                    <tr>
                                        <td>
                                            <?php
                                            $iconMap = [
                                                'application/pdf' => 'fa-file-pdf',
                                                'application/msword' => 'fa-file-word',
                                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'fa-file-word',
                                                'application/zip' => 'fa-file-archive',
                                                'application/x-rar-compressed' => 'fa-file-archive',
                                                'application/x-tar' => 'fa-file-archive',
                                                'text/plain' => 'fa-file-alt',
                                                'application/json' => 'fa-file-code',
                                                'text/csv' => 'fa-file-csv',
                                            ];
                                            $icon = $iconMap[$resource['file_type'] ?? ''] ?? 'fa-file';
                                            ?>
                                            <i class="fas <?php echo $icon; ?> fa-2x" style="color: var(--primary-color);"></i>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($resource['title']); ?></strong>
                                            <?php if ($resource['description']): ?>
                                                <br>
                                                <small style="color: var(--text-muted);">
                                                    <?php echo htmlspecialchars(substr($resource['description'], 0, 60)) . (strlen($resource['description']) > 60 ? '...' : ''); ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small style="font-family: monospace; background: var(--bg-color); padding: 0.25rem 0.5rem; border-radius: 4px;">
                                                <?php
                                                $typeShort = explode('/', $resource['file_type'] ?? 'unknown')[1] ?? 'unknown';
                                                echo strtoupper($typeShort);
                                                ?>
                                            </small>
                                        </td>
                                        <td>
                                            <small><?php echo formatFileSize($resource['file_size'] ?? 0); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                <i class="fas fa-download"></i> <?php echo $resource['download_count'] ?? 0; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $resource['status'] === 'published' ? 'published' : 'draft'; ?>">
                                                <?php echo ucfirst($resource['status']); ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($resource['requires_login']): ?>
                                                <i class="fas fa-lock" style="color: #f59e0b;" title="Login required"></i>
                                            <?php else: ?>
                                                <i class="fas fa-lock-open" style="color: var(--text-muted);" title="Public access"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small style="color: var(--text-muted);">
                                                <?php echo date('M d, Y', strtotime($resource['created_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="table-actions">
                                                <?php if ($resource['file_path']): ?>
                                                    <a
                                                        href="<?php echo $baseUrl . htmlspecialchars($resource['file_path']); ?>"
                                                        class="btn btn-sm btn-secondary btn-icon"
                                                        title="Download"
                                                        download
                                                    >
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a
                                                    href="<?php echo $baseUrl; ?>/admin/editResource/<?php echo $resource['id']; ?>"
                                                    class="btn btn-sm btn-primary btn-icon"
                                                    title="Edit"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a
                                                    href="<?php echo $baseUrl; ?>/admin/deleteResource/<?php echo $resource['id']; ?>"
                                                    class="btn btn-sm btn-danger btn-icon"
                                                    onclick="return confirm('Are you sure you want to delete this resource? This action cannot be undone.');"
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
</body>
</html>
