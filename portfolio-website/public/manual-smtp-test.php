<?php
require_once '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

echo "<h2>Manual SMTP Authentication Test</h2>\n";
echo "<pre>\n";

// MANUAL INPUT - Replace this password with your actual email password
$manualPassword = '0q;pC7*.T*5*gYWS';

echo "Testing SMTP authentication with manually entered password...\n";
echo "Password length: " . strlen($manualPassword) . " characters\n\n";

$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = SMTP::DEBUG_CONNECTION;
    $mail->isSMTP();
    $mail->Host = 'mail.vuyanimagibisela.co.za';
    $mail->SMTPAuth = true;
    $mail->Username = 'admin@vuyanimagibisela.co.za';
    $mail->Password = $manualPassword;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->setFrom('admin@vuyanimagibisela.co.za', 'Vuyani Magibisela');
    $mail->addAddress('admin@vuyanimagibisela.co.za');
    $mail->Subject = 'Test Email';
    $mail->Body = 'Test';
    
    $result = $mail->send();
    echo "\n\n✅ SUCCESS! Email sent successfully!\n";
    echo "The credentials are correct!\n";
    
} catch (Exception $e) {
    echo "\n\n❌ FAILED: " . $e->getMessage() . "\n";
}

echo "</pre>\n";
