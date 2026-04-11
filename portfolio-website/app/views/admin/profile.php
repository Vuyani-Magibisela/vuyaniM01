<?php
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/core/Session.php';

use App\Core\Session;

if (!Session::isAuthenticated()) {
    header('Location: ' . $baseUrl . '/auth/login');
    exit;
}

// Normalize $user into an array
$u = is_object($user) ? (array)$user : (array)$user;
$currentAvatar = !empty($u['profile_image']) ? $u['profile_image'] : $baseUrl . '/images/author-avatar.jpg';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'My Profile'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/main.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/admin.css">
    <style>
        .profile-wrapper {
            max-width: 760px;
        }
        .profile-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 1.5rem;
        }
        .profile-card h2 {
            margin: 0 0 1.25rem 0;
            font-size: 1.125rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-color);
        }
        .avatar-row {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .avatar-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            background: var(--bg-color);
            border: 3px solid var(--border-color);
            flex-shrink: 0;
        }
        .avatar-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .avatar-actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .avatar-actions .btn {
            width: fit-content;
        }
        .upload-status {
            font-size: 0.875rem;
            color: var(--text-muted);
            min-height: 1.2em;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.4rem;
            color: var(--text-color);
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.65rem 0.85rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background: var(--bg-color);
            color: var(--text-color);
            font-family: inherit;
            font-size: 0.95rem;
        }
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        .form-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        .alert {
            padding: 0.85rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .alert-success {
            background: #dcfce7;
            color: #166534;
        }
        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
        }
        @media (max-width: 640px) {
            .form-row { grid-template-columns: 1fr; }
            .avatar-row { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include dirname(__DIR__) . '/partials/admin_sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <div class="admin-header-left">
                    <button class="mobile-menu-toggle"><i class="fas fa-bars"></i></button>
                    <h1>My Profile</h1>
                </div>
            </header>

            <div class="admin-content">
                <div class="profile-wrapper">
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div>
                                <?php foreach ($errors as $err): ?>
                                    <div><?php echo htmlspecialchars($err); ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo $baseUrl; ?>/admin/profile" id="profileForm">
                        <div class="profile-card">
                            <h2><i class="fas fa-user-circle"></i> Profile Image</h2>
                            <div class="avatar-row">
                                <div class="avatar-preview">
                                    <img id="avatarPreview" src="<?php echo htmlspecialchars($currentAvatar); ?>" alt="Profile avatar">
                                </div>
                                <div class="avatar-actions">
                                    <input type="file" id="avatarFile" accept="image/*" style="display:none;">
                                    <button type="button" class="btn btn-primary" onclick="document.getElementById('avatarFile').click();">
                                        <i class="fas fa-upload"></i> Choose Image
                                    </button>
                                    <div class="upload-status" id="uploadStatus">JPG, PNG, WEBP or GIF &mdash; max 5MB.</div>
                                    <input type="hidden" name="profile_image" id="profileImageInput" value="<?php echo htmlspecialchars($u['profile_image'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="profile-card">
                            <h2><i class="fas fa-id-card"></i> Personal Info</h2>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="first_name">First Name</label>
                                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($u['first_name'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="last_name">Last Name</label>
                                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($u['last_name'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($u['email'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="bio">Author Bio</label>
                                <textarea id="bio" name="bio" placeholder="Short description shown on blog articles..."><?php echo htmlspecialchars($u['bio'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="profile-card">
                            <h2><i class="fas fa-lock"></i> Change Password <small style="font-weight:400;color:var(--text-muted);">(optional)</small></h2>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" id="new_password" name="new_password" autocomplete="new-password">
                                </div>
                                <div class="form-group">
                                    <label for="confirm_password">Confirm Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password">
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <a href="<?php echo $baseUrl; ?>/admin" class="btn btn-outline">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        (function () {
            const fileInput = document.getElementById('avatarFile');
            const preview = document.getElementById('avatarPreview');
            const hiddenInput = document.getElementById('profileImageInput');
            const status = document.getElementById('uploadStatus');
            const baseUrl = '<?php echo $baseUrl; ?>';

            fileInput.addEventListener('change', async function () {
                if (!this.files || !this.files[0]) return;
                const file = this.files[0];

                if (file.size > 5 * 1024 * 1024) {
                    status.textContent = 'File too large. Maximum 5MB.';
                    status.style.color = '#dc2626';
                    return;
                }

                status.textContent = 'Uploading...';
                status.style.color = '';

                const fd = new FormData();
                fd.append('image', file);

                try {
                    const res = await fetch(baseUrl + '/admin/uploadProfileImage', {
                        method: 'POST',
                        body: fd
                    });
                    const data = await res.json();

                    if (data.success) {
                        preview.src = data.url;
                        hiddenInput.value = data.url;
                        status.textContent = 'Uploaded. Click "Save Changes" to apply.';
                        status.style.color = '#16a34a';
                    } else {
                        status.textContent = data.error || 'Upload failed.';
                        status.style.color = '#dc2626';
                    }
                } catch (err) {
                    status.textContent = 'Upload failed: ' + err.message;
                    status.style.color = '#dc2626';
                }
            });
        })();
    </script>
    <script src="<?php echo $baseUrl; ?>/js/admin.js"></script>
</body>
</html>
