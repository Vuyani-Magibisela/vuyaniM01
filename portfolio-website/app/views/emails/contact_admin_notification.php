<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Form Submission</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f5f5f5;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff;">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; padding: 40px 30px; text-align: center;">
            <h1 style="margin: 0; font-size: 28px; font-weight: 600;">New Contact Form Submission</h1>
        </div>

        <!-- Body -->
        <div style="padding: 40px 30px;">
            <p style="font-size: 16px; color: #333; margin: 0 0 20px;">
                You have received a new message from your portfolio website contact form.
            </p>

            <div style="background-color: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; margin: 20px 0;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; font-weight: 600; color: #6c757d; font-size: 14px;">Name:</td>
                        <td style="padding: 8px 0; color: #333; font-size: 14px;"><?php echo htmlspecialchars($name ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; font-weight: 600; color: #6c757d; font-size: 14px;">Email:</td>
                        <td style="padding: 8px 0; font-size: 14px;"><a href="mailto:<?php echo htmlspecialchars($email ?? ''); ?>" style="color: #667eea; text-decoration: none;"><?php echo htmlspecialchars($email ?? 'N/A'); ?></a></td>
                    </tr>
                    <?php if (!empty($subject)): ?>
                    <tr>
                        <td style="padding: 8px 0; font-weight: 600; color: #6c757d; font-size: 14px;">Subject:</td>
                        <td style="padding: 8px 0; color: #333; font-size: 14px;"><?php echo htmlspecialchars($subject); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td style="padding: 8px 0; font-weight: 600; color: #6c757d; font-size: 14px;">Submitted:</td>
                        <td style="padding: 8px 0; color: #333; font-size: 14px;"><?php echo htmlspecialchars($timestamp ?? date('Y-m-d H:i:s')); ?></td>
                    </tr>
                    <?php if (!empty($ip_address)): ?>
                    <tr>
                        <td style="padding: 8px 0; font-weight: 600; color: #6c757d; font-size: 14px;">IP Address:</td>
                        <td style="padding: 8px 0; color: #333; font-size: 14px;"><?php echo htmlspecialchars($ip_address); ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>

            <div style="background-color: #fff; border: 1px solid #e9ecef; border-radius: 6px; padding: 20px; margin: 20px 0;">
                <p style="margin: 0 0 10px; font-size: 14px; color: #6c757d; font-weight: 600;">MESSAGE:</p>
                <p style="margin: 0; font-size: 14px; color: #333; line-height: 1.6; white-space: pre-wrap;"><?php echo htmlspecialchars($message ?? ''); ?></p>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="<?php echo $baseUrl ?? '#'; ?>/admin/contacts" style="display: inline-block; padding: 14px 28px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: 600;">
                    View in Admin Panel
                </a>
            </div>

            <p style="font-size: 14px; color: #6c757d; margin: 20px 0 0; text-align: center;">
                Reply directly to this email to respond to <?php echo htmlspecialchars($name ?? 'the sender'); ?>
            </p>
        </div>

        <!-- Footer -->
        <div style="background-color: #f8f9fa; padding: 30px; text-align: center; font-size: 14px; color: #6c757d; border-top: 1px solid #e9ecef;">
            <p style="margin: 0 0 10px;">&copy; <?php echo date('Y'); ?> Vuyani Magibisela. All rights reserved.</p>
            <p style="margin: 10px 0;">
                <a href="<?php echo $baseUrl ?? '#'; ?>/admin" style="color: #667eea; text-decoration: none;">Admin Dashboard</a>
            </p>
        </div>
    </div>
</body>
</html>
