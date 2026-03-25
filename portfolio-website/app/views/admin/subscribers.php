<?php
// Use admin layout
ob_start();
?>

<div class="admin-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-content">
            <h1><i class="fas fa-users"></i> Newsletter Subscribers</h1>
            <p>Manage your blog newsletter subscribers</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="stat-card-value"><?php echo $stats['verified']; ?></div>
            <div class="stat-card-label">Verified Subscribers</div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon orange">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="stat-card-value"><?php echo $stats['pending']; ?></div>
            <div class="stat-card-label">Pending Verification</div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon blue">
                    <i class="fas fa-envelope"></i>
                </div>
            </div>
            <div class="stat-card-value"><?php echo $stats['total']; ?></div>
            <div class="stat-card-label">Total Subscribers</div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon purple">
                    <i class="fas fa-user-slash"></i>
                </div>
            </div>
            <div class="stat-card-value"><?php echo $stats['unsubscribed']; ?></div>
            <div class="stat-card-label">Unsubscribed</div>
        </div>
    </div>

    <!-- Filters and Actions -->
    <div class="subscribers-controls">
        <div class="subscribers-filters">
            <button class="filter-btn active" data-status="all">All</button>
            <button class="filter-btn filter-verified" data-status="verified">Verified</button>
            <button class="filter-btn filter-pending" data-status="pending">Pending</button>
            <button class="filter-btn filter-unsubscribed" data-status="unsubscribed">Unsubscribed</button>
        </div>

        <button class="btn btn-primary" onclick="exportSubscribers()">
            <i class="fas fa-download"></i> Export CSV
        </button>
    </div>

    <!-- Subscribers Table -->
    <div class="subscribers-table-container">
        <?php if (empty($subscribers)): ?>
            <div class="subscribers-empty">
                <i class="fas fa-inbox"></i>
                <p class="subscribers-empty-title">No Subscribers Yet</p>
                <p>Subscribers will appear here once people start subscribing to your blog.</p>
            </div>
        <?php else: ?>
            <table class="admin-table subscribers-table">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Subscribed</th>
                        <th>Verified</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody id="subscribers-table">
                    <?php foreach ($subscribers as $subscriber): ?>
                    <tr data-status="<?php echo htmlspecialchars($subscriber['status']); ?>">
                        <td>
                            <div class="subscriber-email">
                                <i class="fas fa-envelope"></i>
                                <span><?php echo htmlspecialchars($subscriber['email']); ?></span>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo htmlspecialchars($subscriber['status']); ?>">
                                <?php echo ucfirst($subscriber['status']); ?>
                            </span>
                        </td>
                        <td class="text-muted">
                            <?php echo date('M d, Y', strtotime($subscriber['subscribed_at'])); ?>
                        </td>
                        <td class="text-muted">
                            <?php echo $subscriber['verified_at'] ? date('M d, Y', strtotime($subscriber['verified_at'])) : '-'; ?>
                        </td>
                        <td style="text-align: center;">
                            <button class="btn-delete" onclick="deleteSubscriber(<?php echo $subscriber['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<style>
/* Subscribers page styles - uses admin.css CSS variables for dark mode support */
.subscribers-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 30px 0 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.subscribers-filters {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.filter-btn {
    padding: 10px 20px;
    border: 2px solid var(--border-color);
    background: var(--card-bg);
    color: var(--text-color);
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.filter-btn.active {
    background: #667eea;
    border-color: #667eea;
    color: white;
}

.filter-btn.filter-verified.active {
    background: #10b981;
    border-color: #10b981;
}

.filter-btn.filter-pending.active {
    background: #f59e0b;
    border-color: #f59e0b;
}

.filter-btn.filter-unsubscribed.active {
    background: #6b7280;
    border-color: #6b7280;
}

.filter-btn:not(.active):hover {
    background: var(--bg-color);
}

.subscribers-table-container {
    background: var(--card-bg);
    border-radius: 12px;
    border: 1px solid var(--border-color);
    overflow: hidden;
}

.subscribers-table {
    width: 100%;
    border-collapse: collapse;
}

.subscribers-table thead {
    background: var(--bg-color);
    border-bottom: 2px solid var(--border-color);
}

.subscribers-table th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: var(--text-color);
}

.subscribers-table td {
    padding: 15px;
    color: var(--text-color);
    border-bottom: 1px solid var(--border-color);
}

.subscribers-table tr {
    transition: background 0.2s;
}

.subscribers-table tbody tr:hover {
    background: var(--bg-color);
}

.subscriber-email {
    display: flex;
    align-items: center;
    gap: 10px;
}

.subscriber-email i {
    color: #667eea;
    font-size: 1.2rem;
}

.subscriber-email span {
    font-weight: 500;
}

.text-muted {
    color: var(--text-muted);
}

.status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.status-verified {
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
}

.status-pending {
    background: rgba(245, 158, 11, 0.15);
    color: #f59e0b;
}

.status-unsubscribed {
    background: rgba(107, 114, 128, 0.15);
    color: #6b7280;
}

.btn-delete {
    padding: 8px 16px;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-delete:hover {
    background: #dc2626;
}

.subscribers-empty {
    padding: 60px 20px;
    text-align: center;
    color: var(--text-muted);
}

.subscribers-empty i {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.3;
}

.subscribers-empty-title {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 10px;
}
</style>

<script>
// Filter functionality
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const status = btn.dataset.status;

        // Update active button
        document.querySelectorAll('.filter-btn').forEach(b => {
            b.classList.remove('active');
        });
        btn.classList.add('active');

        // Filter table rows
        document.querySelectorAll('#subscribers-table tr').forEach(row => {
            if (status === 'all' || row.dataset.status === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});

// Export to CSV
function exportSubscribers() {
    window.location.href = '<?php echo $baseUrl; ?>/admin/exportSubscribers';
}

// Delete subscriber
async function deleteSubscriber(id) {
    if (!confirm('Are you sure you want to delete this subscriber? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch(`<?php echo $baseUrl; ?>/admin/deleteSubscriber/${id}`, {
            method: 'POST'
        });

        const data = await response.json();

        if (data.success) {
            // Remove row with animation
            const row = document.querySelector(`#subscribers-table tr[data-status]:has(button[onclick*="${id}"])`);
            if (row) {
                row.style.opacity = '0';
                row.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    row.remove();
                    // Reload if table is empty
                    if (document.querySelectorAll('#subscribers-table tr').length === 0) {
                        location.reload();
                    }
                }, 300);
            }
        } else {
            alert('Failed to delete subscriber. Please try again.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    }
}
</script>

<?php
$content = ob_get_clean();
require_once dirname(__DIR__) . '/layouts/admin.php';
?>
