<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Blog Post</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f5f5f5;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff;">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; padding: 40px 30px; text-align: center;">
            <h1 style="margin: 0; font-size: 28px; font-weight: 600;">New Blog Post</h1>
        </div>

        <!-- Body -->
        <div style="padding: 40px 30px;">
            <p style="font-size: 18px; color: #333; margin: 0 0 20px;">Hi there!</p>

            <p style="font-size: 16px; color: #555; line-height: 1.6; margin: 0 0 25px;">
                A new article has just been published on my blog that I think you'll enjoy:
            </p>

            <h2 style="font-size: 22px; color: #333; margin: 0 0 15px; line-height: 1.4;">
                <?php echo htmlspecialchars($title ?? ''); ?>
            </h2>

            <?php if (!empty($featured_image)): ?>
            <div style="margin: 20px 0;">
                <img src="<?php echo htmlspecialchars($featured_image); ?>" alt="<?php echo htmlspecialchars($title ?? ''); ?>" style="width: 100%; max-width: 540px; height: auto; border-radius: 8px; display: block;">
            </div>
            <?php endif; ?>

            <?php if (!empty($excerpt)): ?>
            <p style="font-size: 16px; color: #555; line-height: 1.6; margin: 0 0 25px;">
                <?php echo htmlspecialchars($excerpt); ?>
            </p>
            <?php endif; ?>

            <div style="text-align: center; margin: 30px 0;">
                <a href="<?php echo htmlspecialchars($articleUrl ?? '#'); ?>" style="display: inline-block; padding: 14px 28px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: 600;">
                    Read Article
                </a>
            </div>

            <p style="font-size: 16px; color: #555; line-height: 1.6; margin: 30px 0 0;">
                Happy reading!<br><br>
                <strong>Vuyani Magibisela</strong><br>
                <span style="font-size: 14px; color: #6c757d;">ICT Trainer | Web & App Developer | Maker | 3D Artist</span>
            </p>
        </div>

        <!-- Footer -->
        <div style="background-color: #f8f9fa; padding: 30px; text-align: center; font-size: 14px; color: #6c757d; border-top: 1px solid #e9ecef;">
            <p style="margin: 0 0 10px;">&copy; <?php echo date('Y'); ?> Vuyani Magibisela. All rights reserved.</p>
            <p style="margin: 10px 0;">
                <a href="<?php echo $baseUrl ?? '#'; ?>" style="color: #667eea; text-decoration: none;">Visit Website</a> |
                <a href="<?php echo $baseUrl ?? '#'; ?>/blog" style="color: #667eea; text-decoration: none;">Blog</a>
            </p>
            <?php if (!empty($unsubscribeUrl)): ?>
            <p style="margin: 15px 0 0; font-size: 12px;">
                You received this email because you subscribed to blog updates.<br>
                <a href="<?php echo htmlspecialchars($unsubscribeUrl); ?>" style="color: #6c757d; text-decoration: none;">Unsubscribe from this list</a>
            </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
