<?php
// Verify email configuration
$emailConfig = require '../app/config/email.php';

echo "<h2>Email Configuration Verification</h2>\n";
echo "<pre>\n";

echo "Configuration loaded successfully!\n\n";
echo "SMTP Host: " . $emailConfig['smtp_host'] . "\n";
echo "SMTP Port: " . $emailConfig['smtp_port'] . "\n";
echo "SMTP Username: " . $emailConfig['smtp_username'] . "\n";
echo "SMTP Encryption: " . $emailConfig['smtp_encryption'] . "\n";
echo "From Email: " . $emailConfig['from_email'] . "\n";
echo "Admin Email: " . $emailConfig['admin_email'] . "\n\n";

echo "Password Details:\n";
echo "Password Length: " . strlen($emailConfig['smtp_password']) . " characters\n";
echo "First 3 characters: " . substr($emailConfig['smtp_password'], 0, 3) . "...\n";
echo "Last 3 characters: ..." . substr($emailConfig['smtp_password'], -3) . "\n";
echo "Contains special chars: " . (preg_match('/[^a-zA-Z0-9]/', $emailConfig['smtp_password']) ? 'Yes' : 'No') . "\n\n";

echo "Username Format:\n";
echo "Is full email address: " . (strpos($emailConfig['smtp_username'], '@') !== false ? 'Yes ✓' : 'No ✗') . "\n";
echo "Domain matches: " . (strpos($emailConfig['smtp_username'], 'vuyanimagibisela.co.za') !== false ? 'Yes ✓' : 'No ✗') . "\n";

echo "</pre>\n";
