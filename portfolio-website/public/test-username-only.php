<?php
require_once '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

$emailConfig = require '../app/config/email.php';

echo "<h2>SMTP Test - Username Without @domain</h2>\n";
echo "<pre>\n";

echo "Testing with username 'admin' instead of 'admin@vuyanimagibisela.co.za'\n\n";

$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->Debugoutput = function($str, $level) {
        echo "DEBUG ($level): $str\n";
    };
    
    $mail->isSMTP();
    $mail->Host = 'mail.vuyanimagibisela.co.za';
    $mail->SMTPAuth = true;
    $mail->Username = 'admin';  // Just username without @domain
    $mail->Password = $emailConfig['smtp_password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->setFrom($emailConfig['from_email'], $emailConfig['from_name']);
    $mail->addAddress('admin@vuyanimagibisela.co.za');
    $mail->Subject = 'Test - Username Only';
    $mail->Body = '<h1>Success!</h1><p>Username-only authentication works!</p>';
    
    $mail->send();
    echo "\n✅ SUCCESS: Authentication with username 'admin' works!\n";
    
} catch (Exception $e) {
    echo "\n❌ FAILED: " . $e->getMessage() . "\n";
}

echo "</pre>\n";
