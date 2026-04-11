<?php
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/core/Session.php';

use App\Core\Session;

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
    <title><?php echo $title ?? 'Manage Blog'; ?></title>
    <link rel="icon" type="image/png" href="<?php echo $baseUrl; ?>/images/favicon/favicon-32x32.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/main.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/admin.css">
    <script src="<?php echo $baseUrl; ?>/js/theme-init.js"></script>
</head>
<body>
<div class="admin-wrapper">
    <?php include dirname(__DIR__) . '/partials/admin_sidebar.php'; ?>

    <main class="admin-main">
        <!-- Header -->
        <header class="admin-header">
            <div class="admin-header-left">
                <button class="mobile-menu-toggle"><i class="fas fa-bars"></i></button>
                <h1>Blog Posts</h1>
            </div>
            <div class="header-actions">
                <a href="<?php echo $baseUrl; ?>/admin/categories" class="btn btn-sm" style="background:#8b5cf6;color:#fff;">
                    <i class="fas fa-folder"></i> <span class="btn-label">Categories</span>
                </a>
                <a href="<?php echo $baseUrl; ?>/admin/createBlogPost" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> <span class="btn-label">New Post</span>
                </a>
                <a href="<?php echo $baseUrl; ?>/admin" class="btn btn-sm btn-outline">
                    <i class="fas fa-arrow-left"></i> <span class="btn-label">Dashboard</span>
                </a>
            </div>
        </header>

        <div class="admin-content">
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Filters -->
            <form method="GET" action="<?php echo $baseUrl; ?>/admin/blog" class="blog-filter-bar">
                <input type="text" name="search" placeholder="Search by title…" value="<?php echo htmlspecialchars($search ?? ''); ?>" class="blog-filter-input">
                <select name="status" class="blog-filter-select">
                    <option value="">All statuses</option>
                    <option value="draft"     <?php echo ($status === 'draft')     ? 'selected' : ''; ?>>Draft</option>
                    <option value="published" <?php echo ($status === 'published') ? 'selected' : ''; ?>>Published</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
            </form>

            <p class="blog-list-count"><?php echo $totalPosts; ?> post<?php echo $totalPosts !== 1 ? 's' : ''; ?></p>

            <!-- Post list -->
            <?php if (empty($posts)): ?>
                <div class="empty-state-card">
                    <i class="fas fa-inbox"></i>
                    <h3>No posts found</h3>
                    <p>Create your first blog post to get started.</p>
                    <a href="<?php echo $baseUrl; ?>/admin/createBlogPost" class="btn btn-primary" style="margin-top:1rem;">
                        <i class="fas fa-plus"></i> Create Post
                    </a>
                </div>
            <?php else: ?>
                <div class="blog-post-list">
                    <?php foreach ($posts as $post): ?>
                    <div class="blog-list-item" role="button" tabindex="0"
                         onclick="openPostModal(<?php echo (int)$post['id']; ?>)"
                         onkeydown="if(event.key==='Enter')openPostModal(<?php echo (int)$post['id']; ?>)">
                        <div class="blog-list-thumb">
                            <?php if (!empty($post['featured_image'])): ?>
                                <img src="<?php echo $post['featured_image']; ?>" alt="" loading="lazy">
                            <?php else: ?>
                                <div class="blog-list-thumb-placeholder"><i class="fas fa-image"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="blog-list-info">
                            <span class="blog-list-title"><?php echo htmlspecialchars($post['title']); ?></span>
                            <div class="blog-list-meta">
                                <span class="badge badge-<?php echo $post['status']; ?>"><?php echo ucfirst($post['status']); ?></span>
                                <?php if (!empty($post['category_name'])): ?>
                                    <span class="blog-meta-item"><i class="fas fa-folder"></i> <?php echo htmlspecialchars($post['category_name']); ?></span>
                                <?php endif; ?>
                                <span class="blog-meta-item"><i class="fas fa-eye"></i> <?php echo number_format((int)$post['views']); ?></span>
                                <span class="blog-meta-item"><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                                <?php if ($post['is_featured']): ?>
                                    <span class="blog-meta-item" style="color:#f59e0b;"><i class="fas fa-star"></i> Featured</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right blog-list-arrow"></i>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php
                    $qs = ($status ? '&status='.$status : '') . ($search ? '&search='.urlencode($search) : '');
                    if ($currentPage > 1): ?>
                        <a href="?page=<?php echo $currentPage - 1; ?><?php echo $qs; ?>"><i class="fas fa-chevron-left"></i> Prev</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $currentPage): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?><?php echo $qs; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?php echo $currentPage + 1; ?><?php echo $qs; ?>">Next <i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Post Detail Modal -->
<div id="postDetailModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="modalPostTitle">
    <div class="modal-content modal-content--wide">
        <div class="modal-header">
            <h2 class="modal-title" id="modalPostTitle">Loading…</h2>
            <button class="modal-close" onclick="closeModal('postDetailModal')" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="modalPostBody">
            <div class="modal-loading"><i class="fas fa-spinner fa-spin"></i> Loading…</div>
        </div>
    </div>
</div>

<script>
var BASE = '<?php echo $baseUrl; ?>';

function openPostModal(postId) {
    var modal = document.getElementById('postDetailModal');
    var title = document.getElementById('modalPostTitle');
    var body  = document.getElementById('modalPostBody');

    title.textContent = 'Loading…';
    body.innerHTML    = '<div class="modal-loading"><i class="fas fa-spinner fa-spin"></i> Loading…</div>';
    openModal('postDetailModal');

    fetch(BASE + '/admin/postStats/' + postId)
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (!d.success) { body.innerHTML = '<p style="color:#dc2626;">Failed to load post data.</p>'; return; }
            renderModal(d);
        })
        .catch(function(){ body.innerHTML = '<p style="color:#dc2626;">Network error. Please try again.</p>'; });
}

function renderModal(d) {
    var p = d.post;
    var reactions = d.reactions;
    var comments  = d.comments;
    var pending   = d.pendingComments;
    var base      = d.baseUrl;

    document.getElementById('modalPostTitle').textContent = p.title;

    var emojiMap = {like:'👍', love:'❤️', fire:'🔥', clap:'👏', wow:'😮'};

    // Reactions row
    var reactHtml = Object.keys(reactions).map(function(k){
        return '<span class="modal-reaction"><span>' + (emojiMap[k]||k) + '</span> <strong>' + reactions[k] + '</strong></span>';
    }).join('');

    // Comments
    var statusLabels = { pending: 'Pending', approved: 'Approved', spam: 'Spam' };
    var commentRows = '';
    if (comments && comments.length) {
        commentRows = '<div class="modal-comments">';
        comments.forEach(function(c){
            var badgeClass = c.status === 'approved' ? 'badge-published' : (c.status === 'spam' ? 'badge-danger' : 'badge-draft');
            commentRows += '<div class="modal-comment-item" id="mc-' + c.id + '">';
            commentRows += '<div class="modal-comment-header">';
            commentRows += '<strong>' + escHtml(c.author_name) + '</strong>';
            commentRows += '<span class="badge ' + badgeClass + '">' + (statusLabels[c.status]||c.status) + '</span>';
            commentRows += '<span style="color:#999;font-size:.8rem;">' + c.created_at.substring(0,10) + '</span>';
            commentRows += '</div>';
            commentRows += '<p class="modal-comment-text">' + escHtml(c.content) + '</p>';
            commentRows += '<div class="modal-comment-actions">';
            if (c.status !== 'approved') {
                commentRows += '<button class="btn btn-sm btn-success" onclick="commentAction(\'' + base + '/admin/approveComment/' + c.id + '\', ' + c.id + ', \'approved\')"><i class="fas fa-check"></i> Approve</button>';
            }
            if (c.status !== 'spam') {
                commentRows += '<button class="btn btn-sm" style="background:#f59e0b;color:#fff;" onclick="commentAction(\'' + base + '/admin/spamComment/' + c.id + '\', ' + c.id + ', \'spam\')"><i class="fas fa-ban"></i> Spam</button>';
            }
            commentRows += '<button class="btn btn-sm btn-danger" onclick="deleteComment(\'' + base + '/admin/deleteComment/' + c.id + '\', ' + c.id + ')"><i class="fas fa-trash"></i> Delete</button>';
            commentRows += '</div></div>';
        });
        commentRows += '</div>';
    } else {
        commentRows = '<p style="color:#999;font-size:.9rem;">No comments yet.</p>';
    }

    var thumb = p.featured_image
        ? '<img src="' + p.featured_image + '" alt="" class="modal-thumb">'
        : '';

    document.getElementById('modalPostBody').innerHTML = [
        thumb,
        '<div class="modal-stats-grid">',
            '<div class="modal-stat"><span class="modal-stat-label">Status</span><span class="badge badge-' + p.status + '">' + capitalise(p.status) + '</span></div>',
            '<div class="modal-stat"><span class="modal-stat-label">Category</span><span>' + escHtml(p.category_name||'—') + '</span></div>',
            '<div class="modal-stat"><span class="modal-stat-label">Views</span><span>' + p.views.toLocaleString() + '</span></div>',
            '<div class="modal-stat"><span class="modal-stat-label">Featured</span><span>' + (p.is_featured ? '⭐ Yes' : 'No') + '</span></div>',
            '<div class="modal-stat"><span class="modal-stat-label">Published</span><span>' + (p.published_at ? p.published_at.substring(0,10) : '—') + '</span></div>',
            '<div class="modal-stat"><span class="modal-stat-label">Created</span><span>' + (p.created_at ? p.created_at.substring(0,10) : '—') + '</span></div>',
        '</div>',

        '<div class="modal-reactions-row"><span class="modal-section-label">Reactions</span>' + (reactHtml || '<span style="color:#999;">None yet</span>') + '</div>',

        '<div class="modal-section-label" style="margin-bottom:.5rem;">Comments',
            pending > 0 ? ' <span class="badge badge-draft">' + pending + ' pending</span>' : '',
        '</div>',
        commentRows,

        '<div class="modal-footer">',
            '<a href="' + base + '/blog/preview/' + p.id + '" class="btn btn-sm" style="background:#8b5cf6;color:#fff;" target="_blank"><i class="fas fa-eye"></i> Preview</a>',
            '<a href="' + base + '/admin/editBlogPost/' + p.id + '" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i> Edit</a>',
            '<button class="btn btn-sm" style="background:#f59e0b;color:#fff;" onclick="toggleFeaturedModal(' + p.id + ', ' + (p.is_featured?'true':'false') + ', this)"><i class="fas fa-star"></i> ' + (p.is_featured ? 'Unfeature' : 'Feature') + '</button>',
            '<button class="btn btn-sm btn-danger" onclick="deletePost(' + p.id + ')"><i class="fas fa-trash"></i> Delete</button>',
        '</div>',
    ].join('');
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function capitalise(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

function commentAction(url, id, newStatus) {
    fetch(url, { method: 'POST' })
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (d.success) {
                var item = document.getElementById('mc-' + id);
                if (item) {
                    // Update badge and re-render action buttons based on new status
                    var badge = item.querySelector('.badge');
                    if (badge) {
                        badge.className = 'badge ' + (newStatus === 'approved' ? 'badge-published' : 'badge-draft');
                        badge.textContent = newStatus === 'approved' ? 'Approved' : 'Spam';
                    }
                    var actions = item.querySelector('.modal-comment-actions');
                    if (actions) {
                        var approveBtn = actions.querySelector('[onclick*="approveComment"]');
                        if (approveBtn && newStatus === 'approved') approveBtn.remove();
                        var spamBtn = actions.querySelector('[onclick*="spamComment"]');
                        if (spamBtn && newStatus === 'spam') spamBtn.remove();
                    }
                }
            }
        });
}

function deleteComment(url, id) {
    if (!confirm('Delete this comment permanently?')) return;
    fetch(url, { method: 'POST' })
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (d.success) {
                var item = document.getElementById('mc-' + id);
                if (item) item.remove();
            }
        });
}

function toggleFeaturedModal(postId, isFeatured, btn) {
    fetch(BASE + '/admin/toggleFeatured/' + postId, { method: 'POST' })
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (d.success) {
                btn.innerHTML = '<i class="fas fa-star"></i> ' + (isFeatured ? 'Feature' : 'Unfeature');
                btn.setAttribute('onclick', 'toggleFeaturedModal(' + postId + ', ' + (!isFeatured) + ', this)');
            }
        });
}

function deletePost(postId) {
    if (!confirm('Delete this post permanently? This cannot be undone.')) return;
    window.location.href = BASE + '/admin/deleteBlogPost/' + postId;
}
</script>

<script src="<?php echo $baseUrl; ?>/js/admin.js"></script>
</body>
</html>
