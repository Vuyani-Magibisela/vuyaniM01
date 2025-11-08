<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Admin Dashboard'; ?></title>

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

        .btn-danger {
            background: #dc2626;
            color: white;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            color: #16a34a;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php if (isset($success) && $success): ?>
            <div class="alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <div class="admin-header">
            <div>
                <h1 style="color: var(--text-color);">Admin Dashboard</h1>
                <p style="color: var(--text-muted);">Welcome, <?php echo htmlspecialchars($username ?? 'Admin'); ?>!</p>
            </div>
            <div style="display: flex; gap: 1rem;">
                <a href="<?php echo $baseUrl; ?>/" class="btn" style="background: var(--primary-color); color: white;">
                    <i class="fas fa-home"></i> View Site
                </a>
                <a href="<?php echo $baseUrl; ?>/auth/logout" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>

        <div style="background: var(--card-bg); padding: 2rem; border-radius: 12px;">
            <h2 style="color: var(--text-color); margin-bottom: 1rem;">Authentication System Active!</h2>
            <p style="color: var(--text-color);">You are successfully logged in as: <strong><?php echo htmlspecialchars($email ?? ''); ?></strong></p>
            <p style="color: var(--text-muted); margin-top: 0.5rem;">Role: <?php echo htmlspecialchars($role ?? 'user'); ?></p>
        </div>
    </div>

    <script>
        const html = document.documentElement;
        const savedTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-theme', savedTheme);
    </script>
</body>
</html>
