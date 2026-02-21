<?php
// Test SMTP with port 587 and TLS
require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

echo "<h2>SMTP Test - Port 587 with TLS</h2>\n";
echo "<pre>\n";

$emailConfig = require '../app/config/email.php';

echo "Testing with Port 587 and STARTTLS:\n";
echo "Host: " . $emailConfig['smtp_host'] . "\n";
echo "Port: 587\n";
echo "Username: " . $emailConfig['smtp_username'] . "\n";
echo "Encryption: TLS (STARTTLS)\n\n";

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
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // Changed from SMTPS to STARTTLS
    $mail->Port = 587;  // Changed from 465 to 587

    // Recipients
    $mail->setFrom($emailConfig['from_email'], $emailConfig['from_name']);
    $mail->addAddress('admin@vuyanimagibisela.co.za');

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'SMTP Test - Port 587';
    $mail->Body = '<h1>Success!</h1><p>SMTP connection working with port 587 and TLS!</p>';
    $mail->AltBody = 'SMTP connection successful!';

    $mail->send();
    echo "\n\n✅ SUCCESS: Email sent with port 587!\n";

} catch (Exception $e) {
    echo "\n\n❌ ERROR: {$mail->ErrorInfo}\n";
    echo "Exception: " . $e->getMessage() . "\n";
}

echo "</pre>\n";
