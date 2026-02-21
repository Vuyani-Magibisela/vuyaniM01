<?php
// Detailed SMTP connection test
require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

echo "<h2>SMTP Connection Test</h2>\n";
echo "<pre>\n";

$emailConfig = require '../app/config/email.php';

echo "Configuration:\n";
echo "Host: " . $emailConfig['smtp_host'] . "\n";
echo "Port: " . $emailConfig['smtp_port'] . "\n";
echo "Username: " . $emailConfig['smtp_username'] . "\n";
echo "Encryption: " . $emailConfig['smtp_encryption'] . "\n\n";

$mail = new PHPMailer(true);

try {
    // Enable verbose debug output
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->Debugoutput = function($str, $level) {
        echo "DEBUG ($level): $str\n";
    };

    // Server settings
    $mail->isSMTP();
    $mail->Host = $emailConfig['smtp_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $emailConfig['smtp_username'];
    $mail->Password = $emailConfig['smtp_password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = $emailConfig['smtp_port'];

    // Recipients
    $mail->setFrom($emailConfig['from_email'], $emailConfig['from_name']);
    $mail->addAddress('admin@vuyanimagibisela.co.za');

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'SMTP Test Email';
    $mail->Body = '<h1>Test Email</h1><p>SMTP connection successful!</p>';
    $mail->AltBody = 'SMTP connection successful!';

    $mail->send();
    echo "\n\n✅ SUCCESS: Email sent!\n";

} catch (Exception $e) {
    echo "\n\n❌ ERROR: {$mail->ErrorInfo}\n";
    echo "Exception: " . $e->getMessage() . "\n";
}

echo "</pre>\n";
