<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to the Blog!</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f5f5f5;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff;">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; padding: 40px 30px; text-align: center;">
            <h1 style="margin: 0; font-size: 28px; font-weight: 600;">Welcome!</h1>
        </div>

        <!-- Body -->
        <div style="padding: 40px 30px;">
            <p style="font-size: 18px; color: #333; margin: 0 0 20px;">Hi there!</p>

            <p style="font-size: 16px; color: #555; line-height: 1.6; margin: 0 0 20px;">
                Welcome to my blog! Your email address has been successfully verified, and you're now subscribed to receive updates about my latest articles, projects, and insights.
            </p>

            <div style="background-color: #f0f7ff; border-left: 4px solid #667eea; padding: 20px; margin: 30px 0;">
                <p style="margin: 0 0 15px; font-size: 16px; color: #333; font-weight: 600;">What to expect:</p>
                <ul style="margin: 0; padding-left: 20px; color: #555;">
                    <li style="margin-bottom: 10px;">Technical articles on web development, ICT training, and maker projects</li>
                    <li style="margin-bottom: 10px;">Updates on my latest portfolio projects and 3D art creations</li>
                    <li style="margin-bottom: 10px;">Tips, tutorials, and resources for developers and makers</li>
                    <li style="margin-bottom: 0;">Behind-the-scenes insights into my work and creative process</li>
                </ul>
            </div>

            <p style="font-size: 16px; color: #555; line-height: 1.6; margin: 20px 0;">
                I respect your inbox and only send emails when I have something valuable to share. You can unsubscribe at any time using the link at the bottom of any email.
            </p>

            <div style="text-align: center; margin: 30px 0;">
                <a href="<?php echo $baseUrl ?? '#'; ?>/blog" style="display: inline-block; padding: 14px 28px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: 600;">
                    Explore the Blog
                </a>
            </div>

            <p style="font-size: 16px; color: #555; line-height: 1.6; margin: 30px 0 0;">
                Thanks for joining the community!<br><br>
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
                <a href="<?php echo htmlspecialchars($unsubscribeUrl); ?>" style="color: #6c757d; text-decoration: none;">Unsubscribe from this list</a>
            </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
