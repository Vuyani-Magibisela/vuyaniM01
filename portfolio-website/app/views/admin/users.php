<?php
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/core/Session.php';

use App\Core\Session;

// Check if user is authenticated and is admin
if (!Session::isAuthenticated()) {
    header('Location: ' . $baseUrl . '/auth/login');
    exit;
}

// Only admins can access user management
if (Session::get('user_role') !== 'admin') {
    header('Location: ' . $baseUrl . '/admin');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Manage Users'; ?> - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/main.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/admin.css">
    <style>
        .user-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.2s ease;
        }

        .user-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .user-card.current-user {
            border-left: 4px solid var(--primary-color);
            background: rgba(59, 130, 246, 0.05);
        }

        .user-card-header {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 1rem;
        }

        .user-avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color) 0%, #2563eb 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            font-weight: 600;
            flex-shrink: 0;
        }

        .user-info {
            flex: 1;
            min-width: 0;
        }

        .user-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.25rem;
        }

        .user-email {
            color: var(--text-muted);
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .user-meta {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .user-meta-item {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .user-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .create-user-card {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.05) 100%);
            border: 2px dashed var(--primary-color);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-bottom: 2rem;
        }

        .create-user-card:hover {
            background: rgba(59, 130, 246, 0.15);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .user-card-header {
                flex-direction: column;
                text-align: center;
            }

            .user-actions {
                width: 100%;
            }

            .user-actions .btn {
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
                    <h1>Manage Users</h1>
                </div>
                <div class="admin-header-right">
                    <button class="btn btn-primary" onclick="openModal('createUserModal')">
                        <i class="fas fa-user-plus"></i> Add New User
                    </button>
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
                                <div class="stat-card-value"><?php echo count($users); ?></div>
                                <div class="stat-card-label">Total Admin Users</div>
                            </div>
                            <div class="stat-card-icon blue">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Alert -->
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Only admin users have access to this dashboard. Regular users can be managed through the main authentication system.
                </div>

                <!-- Users List -->
                <?php if (empty($users)): ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <h3>No admin users found</h3>
                        <p>There should be at least one admin user. Something may be wrong.</p>
                    </div>
                <?php else: ?>
                    <?php
                    $currentUserId = Session::getUserId();
                    foreach ($users as $user):
                    ?>
                        <div class="user-card <?php echo $user['id'] == $currentUserId ? 'current-user' : ''; ?>" id="user-<?php echo $user['id']; ?>">
                            <div class="user-card-header">
                                <div class="user-avatar">
                                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                </div>
                                <div class="user-info">
                                    <div class="user-name">
                                        <?php echo htmlspecialchars($user['username']); ?>
                                        <?php if ($user['id'] == $currentUserId): ?>
                                            <span class="badge badge-primary" style="margin-left: 0.5rem;">You</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="user-email">
                                        <i class="fas fa-envelope"></i>
                                        <?php echo htmlspecialchars($user['email']); ?>
                                    </div>
                                    <div class="user-meta">
                                        <span class="user-meta-item">
                                            <i class="fas fa-shield-alt"></i>
                                            <span class="badge badge-<?php echo $user['role'] === 'admin' ? 'primary' : 'secondary'; ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </span>
                                        <span class="user-meta-item">
                                            <i class="fas fa-calendar"></i>
                                            Joined <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                        </span>
                                        <?php if ($user['last_login']): ?>
                                            <span class="user-meta-item">
                                                <i class="fas fa-clock"></i>
                                                Last login: <?php echo date('M d, Y g:i A', strtotime($user['last_login'])); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="user-actions">
                                    <button
                                        class="btn btn-sm btn-primary"
                                        onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)"
                                        title="Edit User"
                                    >
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button
                                        class="btn btn-sm btn-secondary"
                                        onclick="resetPassword(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')"
                                        title="Reset Password"
                                    >
                                        <i class="fas fa-key"></i> Reset Password
                                    </button>
                                    <?php if ($user['id'] != $currentUserId): ?>
                                        <button
                                            class="btn btn-sm btn-danger"
                                            onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')"
                                            title="Delete User"
                                        >
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Create User Modal -->
    <div id="createUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Create New User</h2>
                <button type="button" class="modal-close" onclick="closeModal('createUserModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="createUserForm">
                    <div class="form-group">
                        <label for="create_username" class="required">Username</label>
                        <input type="text" id="create_username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="create_email" class="required">Email</label>
                        <input type="email" id="create_email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="create_password" class="required">Password</label>
                        <input type="password" id="create_password" class="form-control" required minlength="8">
                        <span class="form-help">Minimum 8 characters</span>
                    </div>
                    <div class="form-group">
                        <label for="create_role" class="required">Role</label>
                        <select id="create_role" class="form-control" required>
                            <option value="admin">Admin</option>
                            <option value="editor">Editor</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="submitCreateUser()">
                    <i class="fas fa-user-plus"></i> Create User
                </button>
                <button type="button" class="btn btn-outline" onclick="closeModal('createUserModal')">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Edit User</h2>
                <button type="button" class="modal-close" onclick="closeModal('editUserModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="edit_user_id">
                    <div class="form-group">
                        <label for="edit_username" class="required">Username</label>
                        <input type="text" id="edit_username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_email" class="required">Email</label>
                        <input type="email" id="edit_email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_role" class="required">Role</label>
                        <select id="edit_role" class="form-control" required>
                            <option value="admin">Admin</option>
                            <option value="editor">Editor</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="submitEditUser()">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <button type="button" class="btn btn-outline" onclick="closeModal('editUserModal')">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div id="resetPasswordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Reset Password</h2>
                <button type="button" class="modal-close" onclick="closeModal('resetPasswordModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p style="margin-bottom: 1.5rem;">Reset password for <strong id="reset_username"></strong></p>
                <form id="resetPasswordForm">
                    <input type="hidden" id="reset_user_id">
                    <div class="form-group">
                        <label for="new_password" class="required">New Password</label>
                        <input type="password" id="new_password" class="form-control" required minlength="8">
                        <span class="form-help">Minimum 8 characters</span>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password" class="required">Confirm Password</label>
                        <input type="password" id="confirm_password" class="form-control" required minlength="8">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="submitResetPassword()">
                    <i class="fas fa-key"></i> Reset Password
                </button>
                <button type="button" class="btn btn-outline" onclick="closeModal('resetPasswordModal')">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <script src="<?php echo $baseUrl; ?>/js/admin.js"></script>
    <script>
        const baseUrl = '<?php echo $baseUrl; ?>';

        // Create User
        async function submitCreateUser() {
            const form = document.getElementById('createUserForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const data = {
                username: document.getElementById('create_username').value,
                email: document.getElementById('create_email').value,
                password: document.getElementById('create_password').value,
                role: document.getElementById('create_role').value
            };

            try {
                const response = await fetch(`${baseUrl}/admin/createUser`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    closeModal('createUserModal');
                    location.reload();
                } else {
                    alert(result.error || 'Failed to create user');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred');
            }
        }

        // Edit User
        function editUser(user) {
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_role').value = user.role;
            openModal('editUserModal');
        }

        async function submitEditUser() {
            const form = document.getElementById('editUserForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const userId = document.getElementById('edit_user_id').value;
            const data = {
                username: document.getElementById('edit_username').value,
                email: document.getElementById('edit_email').value,
                role: document.getElementById('edit_role').value
            };

            try {
                const response = await fetch(`${baseUrl}/admin/updateUser/${userId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    closeModal('editUserModal');
                    location.reload();
                } else {
                    alert(result.error || 'Failed to update user');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred');
            }
        }

        // Reset Password
        function resetPassword(userId, username) {
            document.getElementById('reset_user_id').value = userId;
            document.getElementById('reset_username').textContent = username;
            document.getElementById('new_password').value = '';
            document.getElementById('confirm_password').value = '';
            openModal('resetPasswordModal');
        }

        async function submitResetPassword() {
            const form = document.getElementById('resetPasswordForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (newPassword !== confirmPassword) {
                alert('Passwords do not match');
                return;
            }

            const userId = document.getElementById('reset_user_id').value;

            try {
                const response = await fetch(`${baseUrl}/admin/resetUserPassword/${userId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ password: newPassword })
                });

                const result = await response.json();

                if (result.success) {
                    closeModal('resetPasswordModal');
                    alert('Password reset successfully');
                } else {
                    alert(result.error || 'Failed to reset password');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred');
            }
        }

        // Delete User
        async function deleteUser(userId, username) {
            if (!confirm(`Are you sure you want to delete user "${username}"? This action cannot be undone.`)) {
                return;
            }

            try {
                const response = await fetch(`${baseUrl}/admin/deleteUser/${userId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    const userCard = document.getElementById(`user-${userId}`);
                    userCard.style.opacity = '0';
                    userCard.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        userCard.remove();
                    }, 300);
                } else {
                    alert(result.error || 'Failed to delete user');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred');
            }
        }
    </script>
</body>
</html>
