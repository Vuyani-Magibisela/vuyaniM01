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
            <div class="stat-icon verified" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $stats['verified']; ?></h3>
                <p>Verified Subscribers</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon pending" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $stats['pending']; ?></h3>
                <p>Pending Verification</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon total" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $stats['total']; ?></h3>
                <p>Total Subscribers</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon unsubscribed" style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);">
                <i class="fas fa-user-slash"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $stats['unsubscribed']; ?></h3>
                <p>Unsubscribed</p>
            </div>
        </div>
    </div>

    <!-- Filters and Actions -->
    <div class="table-controls" style="display: flex; justify-content: space-between; align-items: center; margin: 30px 0 20px; flex-wrap: wrap; gap: 15px;">
        <div class="filters" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button class="filter-btn active" data-status="all" style="padding: 10px 20px; border: 2px solid #667eea; background: #667eea; color: white; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.3s;">All</button>
            <button class="filter-btn" data-status="verified" style="padding: 10px 20px; border: 2px solid #10b981; background: white; color: #10b981; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.3s;">Verified</button>
            <button class="filter-btn" data-status="pending" style="padding: 10px 20px; border: 2px solid #f59e0b; background: white; color: #f59e0b; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.3s;">Pending</button>
            <button class="filter-btn" data-status="unsubscribed" style="padding: 10px 20px; border: 2px solid #6b7280; background: white; color: #6b7280; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.3s;">Unsubscribed</button>
        </div>

        <button class="btn btn-primary" onclick="exportSubscribers()" style="padding: 10px 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">
            <i class="fas fa-download"></i> Export CSV
        </button>
    </div>

    <!-- Subscribers Table -->
    <div class="table-container" style="background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden;">
        <?php if (empty($subscribers)): ?>
            <div style="padding: 60px 20px; text-align: center; color: #6b7280;">
                <i class="fas fa-inbox" style="font-size: 4rem; margin-bottom: 20px; opacity: 0.3;"></i>
                <p style="font-size: 1.2rem; font-weight: 600; margin-bottom: 10px;">No Subscribers Yet</p>
                <p>Subscribers will appear here once people start subscribing to your blog.</p>
            </div>
        <?php else: ?>
            <table class="admin-table" style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f9fafb; border-bottom: 2px solid #e5e7eb;">
                    <tr>
                        <th style="padding: 15px; text-align: left; font-weight: 600; color: #374151;">Email</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600; color: #374151;">Status</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600; color: #374151;">Subscribed</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600; color: #374151;">Verified</th>
                        <th style="padding: 15px; text-align: center; font-weight: 600; color: #374151;">Actions</th>
                    </tr>
                </thead>
                <tbody id="subscribers-table">
                    <?php foreach ($subscribers as $subscriber): ?>
                    <tr data-status="<?php echo htmlspecialchars($subscriber['status']); ?>" style="border-bottom: 1px solid #e5e7eb; transition: background 0.2s;">
                        <td style="padding: 15px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <i class="fas fa-envelope" style="color: #667eea; font-size: 1.2rem;"></i>
                                <span style="font-weight: 500; color: #111827;"><?php echo htmlspecialchars($subscriber['email']); ?></span>
                            </div>
                        </td>
                        <td style="padding: 15px;">
                            <?php
                            $statusColors = [
                                'verified' => '#10b981',
                                'pending' => '#f59e0b',
                                'unsubscribed' => '#6b7280'
                            ];
                            $statusColor = $statusColors[$subscriber['status']] ?? '#6b7280';
                            ?>
                            <span class="status-badge" style="display: inline-block; padding: 6px 12px; background: <?php echo $statusColor; ?>20; color: <?php echo $statusColor; ?>; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                                <?php echo ucfirst($subscriber['status']); ?>
                            </span>
                        </td>
                        <td style="padding: 15px; color: #6b7280;">
                            <?php echo date('M d, Y', strtotime($subscriber['subscribed_at'])); ?>
                        </td>
                        <td style="padding: 15px; color: #6b7280;">
                            <?php echo $subscriber['verified_at'] ? date('M d, Y', strtotime($subscriber['verified_at'])) : '-'; ?>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <button class="btn-delete" onclick="deleteSubscriber(<?php echo $subscriber['id']; ?>)" style="padding: 8px 16px; background: #ef4444; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
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

<script>
// Filter functionality
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const status = btn.dataset.status;

        // Update active button
        document.querySelectorAll('.filter-btn').forEach(b => {
            b.classList.remove('active');
            b.style.background = 'white';
            b.style.color = b.style.borderColor.split(' ')[2]; // Get color from border
        });
        btn.classList.add('active');
        btn.style.background = btn.style.borderColor.split(' ')[2]; // Use border color as background
        btn.style.color = 'white';

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
