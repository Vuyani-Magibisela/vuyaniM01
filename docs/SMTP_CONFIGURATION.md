# SMTP Configuration Guide

This document contains the complete SMTP configuration details for the vuyaniM01 project email system.

## SMTP Server Settings

| Setting | Value |
|---------|-------|
| **SMTP Host** | `mail.vuyanimagibisela.co.za` |
| **SMTP Port** | `465` |
| **Encryption** | `SSL` |
| **Authentication** | Required |

## Authentication Credentials

| Credential | Value |
|------------|-------|
| **Username** | `admin@vuyanimagibisela.co.za` |
| **Password** | `@*7.dQ.I=NkO7X]v` |

## Email Addresses

| Purpose | Email | Display Name |
|---------|-------|--------------|
| **From Address** | `admin@vuyanimagibisela.co.za` | Vuyani Magibisela |
| **Admin Email** | `admin@vuyanimagibisela.co.za` | Vuyani Magibisela |

**Note:** Admin email receives contact form notifications and other administrative emails.

## PHPMailer Configuration

### Basic Setup

```php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

// Server settings
$mail->isSMTP();
$mail->Host = 'mail.vuyanimagibisela.co.za';
$mail->SMTPAuth = true;
$mail->Username = 'admin@vuyanimagibisela.co.za';
$mail->Password = '@*7.dQ.I=NkO7X]v';
$mail->SMTPSecure = 'ssl';
$mail->Port = 465;

// From address
$mail->setFrom('admin@vuyanimagibisela.co.za', 'Vuyani Magibisela');

// Content settings
$mail->isHTML(true);
$mail->CharSet = 'UTF-8';
```

### Complete Configuration Array

```php
return [
    'smtp_host' => 'mail.vuyanimagibisela.co.za',
    'smtp_port' => 465,
    'smtp_username' => 'admin@vuyanimagibisela.co.za',
    'smtp_password' => '@*7.dQ.I=NkO7X]v',
    'smtp_encryption' => 'ssl',
    'from_email' => 'admin@vuyanimagibisela.co.za',
    'from_name' => 'Vuyani Magibisela',
    'admin_email' => 'admin@vuyanimagibisela.co.za',
    'admin_name' => 'Vuyani Magibisela'
];
```

## Implementation Details

### File Locations (Production)

- **PHPMailer Library:** `private_files/vendor/phpmailer/phpmailer/src/`
- **Email Configuration:** `app/config/email.php`
- **Email Core Class:** `app/core/Email.php`
- **Email Templates:** `app/views/emails/`

### Available Email Templates

The project includes the following email templates:

1. **contact_confirmation.php** - Confirmation sent to contact form submitters
2. **contact_admin_notification.php** - Admin notification for new contact submissions
3. **subscription_verification.php** - Email verification for blog subscribers
4. **subscription_welcome.php** - Welcome email after subscription verification
5. **new_post_notification.php** - New blog post notifications for subscribers

### Email Core Class Methods

```php
// Send basic email
$email->send($to, $subject, $body, $altBody);

// Send email using template
$email->sendTemplate($to, $subject, $template, $data);

// Send contact form confirmation
$email->sendContactConfirmation($contactData);

// Send contact notification to admin
$email->sendContactNotification($contactData);

// Send subscription verification
$email->sendSubscriptionVerification($email, $token);

// Send subscription welcome
$email->sendSubscriptionWelcome($email, $unsubscribeToken);

// Send new post notification
$email->sendNewPostNotification($email, $unsubscribeToken, $postData);

// Send test email
$email->sendTestEmail($testEmail);

// Get last error
$email->getLastError();
```

## Important Notes

### Security Considerations

1. **Never commit credentials** - Keep SMTP credentials in config files excluded from git
2. **Use environment detection** - The config file auto-detects local vs production
3. **Validate recipient emails** - Always validate email addresses before sending

### Port Configuration

- **Port 465 with SSL** (used in this configuration)
- NOT Port 587 with TLS
- Ensure firewall allows outbound connections on port 465

### Known Issues

- **SMTP Authentication Failure** (as of February 2026)
  - If authentication fails, reset password in hosting control panel
  - Verify credentials in `app/config/email.php`
  - Check email account is active in cPanel/hosting panel

### Production Environment

- **Domain:** vuyanimagibisela.co.za
- **Hosting Type:** Shared hosting
- **Document Root:** `public_html/`
- **No Composer Autoload:** PHP classes require manual `require_once` statements

## Testing Email Configuration

### Test Email Endpoint

Use the diagnostic endpoint (should be removed after SMTP is confirmed working):

```
/subscription/testEmail
```

### Manual Testing

```php
require_once 'app/core/Email.php';

$email = new \App\Core\Email();
$result = $email->sendTestEmail('your-test@email.com');

if ($result) {
    echo "Email sent successfully!";
} else {
    echo "Error: " . $email->getLastError();
}
```

## Troubleshooting

### Common Issues

1. **Authentication Failed**
   - Verify credentials in hosting panel
   - Check if email account is active
   - Ensure password hasn't expired

2. **Connection Timeout**
   - Verify SMTP host is correct
   - Check if port 465 is blocked by firewall
   - Confirm SSL encryption is supported

3. **Email Not Received**
   - Check spam/junk folder
   - Verify recipient email is valid
   - Check email logs on server

4. **Template Not Found**
   - Ensure template file exists in `app/views/emails/`
   - Verify file permissions (644)
   - Check template filename matches (without .php extension)

## Environment Detection

The configuration automatically detects environment:

```php
$httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';

if ($httpHost === 'localhost' ||
    strpos($httpHost, '127.0.0.1') !== false ||
    strpos($httpHost, 'localhost') !== false) {
    // Local development - uses production SMTP for testing
} else {
    // Production server
}
```

## Migration to Other Projects

To use this SMTP configuration in other projects:

1. Copy `/app/config/email.php` to your project
2. Copy `/app/core/Email.php` to your project
3. Install PHPMailer: `composer require phpmailer/phpmailer`
4. Copy email templates from `/app/views/emails/` (optional)
5. Update configuration values as needed

## Related Documentation

- [DEPLOYMENT_INSTRUCTIONS.md](DEPLOYMENT_INSTRUCTIONS.md) - Production deployment guide
- [DATABASE_MIGRATION_GUIDE.md](DATABASE_MIGRATION_GUIDE.md) - Database setup
- [AUTHENTICATION_SYSTEM.md](AUTHENTICATION_SYSTEM.md) - Authentication documentation

## Last Updated

2026-04-08
