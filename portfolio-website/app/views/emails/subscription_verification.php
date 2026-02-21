<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Blog Subscription</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f5f5f5;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff;">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; padding: 40px 30px; text-align: center;">
            <h1 style="margin: 0; font-size: 28px; font-weight: 600;">Verify Your Subscription</h1>
        </div>

        <!-- Body -->
        <div style="padding: 40px 30px;">
            <p style="font-size: 18px; color: #333; margin: 0 0 20px;">Hi there!</p>

            <p style="font-size: 16px; color: #555; line-height: 1.6; margin: 0 0 20px;">
                Thank you for subscribing to my blog! To complete your subscription and start receiving updates, please verify your email address by clicking the button below.
            </p>

            <div style="text-align: center; margin: 40px 0;">
                <a href="<?php echo htmlspecialchars($verificationUrl ?? '#'); ?>" style="display: inline-block; padding: 16px 32px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                    Verify My Email Address
                </a>
            </div>

            <p style="font-size: 14px; color: #6c757d; line-height: 1.6; margin: 20px 0; padding: 15px; background-color: #f8f9fa; border-left: 4px solid #ffc107;">
                <strong>Note:</strong> If you didn't subscribe to this blog, you can safely ignore this email. Your email address will not be added to our subscriber list.
            </p>

            <p style="font-size: 14px; color: #6c757d; margin: 20px 0;">
                Or copy and paste this link into your browser:<br>
                <a href="<?php echo htmlspecialchars($verificationUrl ?? '#'); ?>" style="color: #667eea; word-break: break-all;"><?php echo htmlspecialchars($verificationUrl ?? ''); ?></a>
            </p>

            <p style="font-size: 16px; color: #555; line-height: 1.6; margin: 30px 0 0;">
                Best regards,<br>
                <strong>Vuyani Magibisela</strong><br>
                <span style="font-size: 14px; color: #6c757d;">ICT Trainer | Web & App Developer</span>
            </p>
        </div>

        <!-- Footer -->
        <div style="background-color: #f8f9fa; padding: 30px; text-align: center; font-size: 14px; color: #6c757d; border-top: 1px solid #e9ecef;">
            <p style="margin: 0 0 10px;">&copy; <?php echo date('Y'); ?> Vuyani Magibisela. All rights reserved.</p>
            <p style="margin: 10px 0;">
                <a href="<?php echo $baseUrl ?? '#'; ?>" style="color: #667eea; text-decoration: none;">Visit Website</a> |
                <a href="<?php echo $baseUrl ?? '#'; ?>/blog" style="color: #667eea; text-decoration: none;">Blog</a>
            </p>
        </div>
    </div>
</body>
</html>
