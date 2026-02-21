<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank you for contacting us</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f5f5f5;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff;">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; padding: 40px 30px; text-align: center;">
            <h1 style="margin: 0; font-size: 28px; font-weight: 600;">Thank You!</h1>
        </div>

        <!-- Body -->
        <div style="padding: 40px 30px;">
            <p style="font-size: 18px; color: #333; margin: 0 0 20px;">Hi <?php echo htmlspecialchars($name ?? 'there'); ?>,</p>

            <p style="font-size: 16px; color: #555; line-height: 1.6; margin: 0 0 20px;">
                Thank you for reaching out! I've received your message and will get back to you as soon as possible, typically within 24-48 hours.
            </p>

            <div style="background-color: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; margin: 30px 0;">
                <p style="margin: 0 0 10px; font-size: 14px; color: #6c757d; font-weight: 600;">YOUR MESSAGE:</p>
                <?php if (!empty($subject)): ?>
                    <p style="margin: 0 0 10px; font-size: 14px;"><strong>Subject:</strong> <?php echo htmlspecialchars($subject); ?></p>
                <?php endif; ?>
                <p style="margin: 0; font-size: 14px; color: #333; white-space: pre-wrap;"><?php echo htmlspecialchars($message ?? ''); ?></p>
            </div>

            <p style="font-size: 16px; color: #555; line-height: 1.6; margin: 20px 0;">
                If you have any additional information to share or if your inquiry is urgent, please feel free to reply to this email.
            </p>

            <p style="font-size: 16px; color: #555; line-height: 1.6; margin: 20px 0 0;">
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
                <a href="<?php echo $baseUrl ?? '#'; ?>/contact" style="color: #667eea; text-decoration: none;">Contact</a>
            </p>
        </div>
    </div>
</body>
</html>
