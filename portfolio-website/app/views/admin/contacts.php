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
    <title><?php echo $title ?? 'Contact Submissions'; ?> - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/main.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/admin.css">
    <style>
        .contact-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.2s ease;
        }

        .contact-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .contact-card.unread {
            border-left: 4px solid var(--primary-color);
            background: rgba(59, 130, 246, 0.05);
        }

        .contact-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .contact-info {
            flex: 1;
            min-width: 200px;
        }

        .contact-name {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.25rem;
        }

        .contact-email {
            color: var(--text-muted);
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.25rem;
        }

        .contact-date {
            color: var(--text-muted);
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .contact-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .contact-message {
            color: var(--text-color);
            line-height: 1.6;
            padding: 1rem;
            background: var(--bg-color);
            border-radius: 8px;
            margin-top: 1rem;
        }

        .contact-subject {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .message-preview {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .message-full {
            white-space: pre-wrap;
        }

        .expand-toggle {
            margin-top: 0.5rem;
            color: var(--primary-color);
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .expand-toggle:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .contact-card-header {
                flex-direction: column;
            }

            .contact-actions {
                width: 100%;
                justify-content: stretch;
            }

            .contact-actions .btn {
                flex: 1;
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
                    <h1>Contact Submissions</h1>
                </div>
                <div class="admin-header-right">
                    <?php
                    $unreadCount = 0;
                    foreach ($submissions as $sub) {
                        if (!$sub['is_read']) $unreadCount++;
                    }
                    ?>
                    <?php if ($unreadCount > 0): ?>
                        <span class="badge badge-danger" style="padding: 0.5rem 1rem;">
                            <?php echo $unreadCount; ?> Unread
                        </span>
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

                <!-- Stats Summary -->
                <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 2rem;">
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div>
                                <div class="stat-card-value"><?php echo count($submissions); ?></div>
                                <div class="stat-card-label">Total Messages</div>
                            </div>
                            <div class="stat-card-icon blue">
                                <i class="fas fa-envelope"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div>
                                <div class="stat-card-value"><?php echo $unreadCount; ?></div>
                                <div class="stat-card-label">Unread</div>
                            </div>
                            <div class="stat-card-icon orange">
                                <i class="fas fa-bell"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Submissions -->
                <?php if (empty($submissions)): ?>
                    <div class="empty-state">
                        <i class="fas fa-envelope"></i>
                        <h3>No contact submissions yet</h3>
                        <p>When visitors submit the contact form, their messages will appear here.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($submissions as $submission): ?>
                        <div class="contact-card <?php echo !$submission['is_read'] ? 'unread' : ''; ?>" id="contact-<?php echo $submission['id']; ?>">
                            <div class="contact-card-header">
                                <div class="contact-info">
                                    <div class="contact-name">
                                        <?php echo htmlspecialchars($submission['name']); ?>
                                        <?php if (!$submission['is_read']): ?>
                                            <span class="badge badge-primary" style="margin-left: 0.5rem;">New</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="contact-email">
                                        <i class="fas fa-envelope"></i>
                                        <a href="mailto:<?php echo htmlspecialchars($submission['email']); ?>" style="color: var(--primary-color);">
                                            <?php echo htmlspecialchars($submission['email']); ?>
                                        </a>
                                    </div>
                                    <?php if (!empty($submission['phone'])): ?>
                                        <div class="contact-email">
                                            <i class="fas fa-phone"></i>
                                            <a href="tel:<?php echo htmlspecialchars($submission['phone']); ?>" style="color: var(--primary-color);">
                                                <?php echo htmlspecialchars($submission['phone']); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <div class="contact-date">
                                        <i class="fas fa-clock"></i>
                                        <?php echo date('F j, Y \a\t g:i A', strtotime($submission['created_at'])); ?>
                                    </div>
                                </div>
                                <div class="contact-actions">
                                    <a
                                        href="mailto:<?php echo htmlspecialchars($submission['email']); ?>?subject=Re: Contact Form Submission"
                                        class="btn btn-sm btn-primary"
                                        title="Reply via Email"
                                    >
                                        <i class="fas fa-reply"></i> Reply
                                    </a>
                                    <?php if (!$submission['is_read']): ?>
                                        <button
                                            class="btn btn-sm btn-secondary mark-read-btn"
                                            data-id="<?php echo $submission['id']; ?>"
                                            title="Mark as Read"
                                        >
                                            <i class="fas fa-check"></i> Mark Read
                                        </button>
                                    <?php endif; ?>
                                    <button
                                        class="btn btn-sm btn-danger delete-btn"
                                        data-id="<?php echo $submission['id']; ?>"
                                        title="Delete"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>

                            <?php if (!empty($submission['subject'])): ?>
                                <div class="contact-subject">
                                    <i class="fas fa-tag"></i> <?php echo htmlspecialchars($submission['subject']); ?>
                                </div>
                            <?php endif; ?>

                            <div class="contact-message">
                                <div class="message-preview message-content-<?php echo $submission['id']; ?>">
                                    <?php echo nl2br(htmlspecialchars($submission['message'])); ?>
                                </div>
                                <?php if (strlen($submission['message']) > 200): ?>
                                    <div class="expand-toggle" onclick="toggleMessage(<?php echo $submission['id']; ?>)">
                                        <span class="toggle-text-<?php echo $submission['id']; ?>">Show more</span>
                                        <i class="fas fa-chevron-down toggle-icon-<?php echo $submission['id']; ?>"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="<?php echo $baseUrl; ?>/js/admin.js"></script>
    <script>
        const baseUrl = '<?php echo $baseUrl; ?>';

        // Toggle message expand/collapse
        const expandedMessages = new Set();

        function toggleMessage(id) {
            const messageEl = document.querySelector(`.message-content-${id}`);
            const toggleText = document.querySelector(`.toggle-text-${id}`);
            const toggleIcon = document.querySelector(`.toggle-icon-${id}`);

            if (expandedMessages.has(id)) {
                messageEl.classList.remove('message-full');
                messageEl.classList.add('message-preview');
                toggleText.textContent = 'Show more';
                toggleIcon.classList.remove('fa-chevron-up');
                toggleIcon.classList.add('fa-chevron-down');
                expandedMessages.delete(id);
            } else {
                messageEl.classList.remove('message-preview');
                messageEl.classList.add('message-full');
                toggleText.textContent = 'Show less';
                toggleIcon.classList.remove('fa-chevron-down');
                toggleIcon.classList.add('fa-chevron-up');
                expandedMessages.add(id);
            }
        }

        // Mark as read functionality
        document.querySelectorAll('.mark-read-btn').forEach(btn => {
            btn.addEventListener('click', async function() {
                const id = this.dataset.id;
                const card = document.getElementById(`contact-${id}`);

                try {
                    const response = await fetch(`${baseUrl}/admin/markContactRead/${id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        card.classList.remove('unread');
                        this.remove();

                        // Update unread count
                        const unreadBadge = document.querySelector('.badge-danger');
                        if (unreadBadge) {
                            const currentCount = parseInt(unreadBadge.textContent);
                            const newCount = currentCount - 1;
                            if (newCount > 0) {
                                unreadBadge.textContent = newCount + ' Unread';
                            } else {
                                unreadBadge.remove();
                            }
                        }

                        // Update stats
                        const unreadStatValue = document.querySelectorAll('.stat-card-value')[1];
                        if (unreadStatValue) {
                            const currentValue = parseInt(unreadStatValue.textContent);
                            unreadStatValue.textContent = Math.max(0, currentValue - 1);
                        }
                    } else {
                        alert('Failed to mark as read');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred');
                }
            });
        });

        // Delete functionality
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', async function() {
                if (!confirm('Are you sure you want to delete this contact submission? This action cannot be undone.')) {
                    return;
                }

                const id = this.dataset.id;
                const card = document.getElementById(`contact-${id}`);

                try {
                    const response = await fetch(`${baseUrl}/admin/deleteContact/${id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Fade out and remove
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            card.remove();

                            // Check if any contacts remain
                            const remainingCards = document.querySelectorAll('.contact-card');
                            if (remainingCards.length === 0) {
                                location.reload();
                            }
                        }, 300);

                        // Update total count
                        const totalStatValue = document.querySelectorAll('.stat-card-value')[0];
                        if (totalStatValue) {
                            const currentValue = parseInt(totalStatValue.textContent);
                            totalStatValue.textContent = Math.max(0, currentValue - 1);
                        }
                    } else {
                        alert('Failed to delete submission');
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
