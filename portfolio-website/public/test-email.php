<?php
// Test email sending functionality
require_once '../vendor/autoload.php';
require_once '../app/core/Email.php';

use App\Core\Email;

echo "<h2>Testing Email Configuration</h2>\n";
echo "<pre>\n";

try {
    // Load email configuration
    $emailConfig = require_once '../app/config/email.php';
    echo "Email Configuration:\n";
    echo "SMTP Host: " . $emailConfig['smtp_host'] . "\n";
    echo "SMTP Port: " . $emailConfig['smtp_port'] . "\n";
    echo "SMTP Username: " . $emailConfig['smtp_username'] . "\n";
    echo "From Email: " . $emailConfig['from_email'] . "\n";
    echo "Admin Email: " . $emailConfig['admin_email'] . "\n\n";

    // Test basic email sending
    echo "Testing email send to admin@vuyanimagibisela.co.za...\n";

    $emailService = new Email();
    $result = $emailService->send(
        'admin@vuyanimagibisela.co.za',
        'Test Email from Portfolio Website',
        '<h1>Test Email</h1><p>This is a test email to verify SMTP configuration is working correctly.</p>',
        'This is a test email to verify SMTP configuration is working correctly.'
    );

    if ($result) {
        echo "\n✅ SUCCESS: Email sent successfully!\n";
    } else {
        echo "\n❌ FAILED: Email could not be sent\n";
    }

} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>\n";
