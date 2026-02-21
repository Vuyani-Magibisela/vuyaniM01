<?php
// Get base URL from config
require_once dirname(__DIR__, 3) . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($message) ? 'Unsubscribed Successfully' : 'Unsubscribe Error'; ?> - Vuyani Magibisela</title>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .unsubscribe-container {
            max-width: 600px;
            margin: 100px auto;
            padding: 40px;
            text-align: center;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }

        .unsubscribe-icon {
            font-size: 5rem;
            margin-bottom: 30px;
        }

        .unsubscribe-icon.success {
            color: #10b981;
        }

        .unsubscribe-icon.error {
            color: #ef4444;
        }

        .unsubscribe-container h1 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: #333;
        }

        .unsubscribe-container p {
            font-size: 1.1rem;
            line-height: 1.6;
            color: #666;
            margin-bottom: 20px;
        }

        .btn-primary {
            display: inline-block;
            padding: 14px 28px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 20px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="unsubscribe-container">
        <?php if (isset($message)): ?>
            <div class="unsubscribe-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Successfully Unsubscribed</h1>
            <p><?php echo htmlspecialchars($message); ?></p>
            <p>We're sorry to see you go! If you change your mind, you can always resubscribe from the blog page.</p>
        <?php else: ?>
            <div class="unsubscribe-icon error">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <h1>Unsubscribe Error</h1>
            <p><?php echo htmlspecialchars($error ?? 'An error occurred while processing your request.'); ?></p>
            <p>Please contact us if you continue to experience issues.</p>
        <?php endif; ?>

        <a href="<?php echo $baseUrl; ?>/" class="btn-primary">
            <i class="fas fa-home"></i> Return to Homepage
        </a>
    </div>
</body>
</html>
