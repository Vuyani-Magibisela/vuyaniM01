<?php
require_once '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

$emailConfig = require '../app/config/email.php';

echo "<h2>SMTP Test with localhost</h2>\n";
echo "<pre>\n";

// Test 1: localhost with port 587 and TLS
echo "==========================================\n";
echo "Test 1: localhost, Port 587, STARTTLS\n";
echo "==========================================\n";

$mail1 = new PHPMailer(true);
try {
    $mail1->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail1->Debugoutput = function($str, $level) {
        echo "DEBUG ($level): $str\n";
    };
    $mail1->isSMTP();
    $mail1->Host = 'localhost';
    $mail1->SMTPAuth = true;
    $mail1->Username = $emailConfig['smtp_username'];
    $mail1->Password = $emailConfig['smtp_password'];
    $mail1->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail1->Port = 587;
    $mail1->setFrom($emailConfig['from_email'], $emailConfig['from_name']);
    $mail1->addAddress('admin@vuyanimagibisela.co.za');
    $mail1->Subject = 'Test - localhost:587 TLS';
    $mail1->Body = '<h1>Success!</h1><p>localhost:587 with TLS works!</p>';
    
    $mail1->send();
    echo "\n✅ SUCCESS: localhost:587 with TLS works!\n\n";
} catch (Exception $e) {
    echo "\n❌ FAILED: " . $e->getMessage() . "\n\n";
}

// Test 2: localhost with port 465 and SSL
echo "\n==========================================\n";
echo "Test 2: localhost, Port 465, SSL\n";
echo "==========================================\n";

$mail2 = new PHPMailer(true);
try {
    $mail2->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail2->Debugoutput = function($str, $level) {
        echo "DEBUG ($level): $str\n";
    };
    $mail2->isSMTP();
    $mail2->Host = 'localhost';
    $mail2->SMTPAuth = true;
    $mail2->Username = $emailConfig['smtp_username'];
    $mail2->Password = $emailConfig['smtp_password'];
    $mail2->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail2->Port = 465;
    $mail2->setFrom($emailConfig['from_email'], $emailConfig['from_name']);
    $mail2->addAddress('admin@vuyanimagibisela.co.za');
    $mail2->Subject = 'Test - localhost:465 SSL';
    $mail2->Body = '<h1>Success!</h1><p>localhost:465 with SSL works!</p>';
    
    $mail2->send();
    echo "\n✅ SUCCESS: localhost:465 with SSL works!\n\n";
} catch (Exception $e) {
    echo "\n❌ FAILED: " . $e->getMessage() . "\n\n";
}

// Test 3: localhost with port 25 (no encryption)
echo "\n==========================================\n";
echo "Test 3: localhost, Port 25, No Encryption\n";
echo "==========================================\n";

$mail3 = new PHPMailer(true);
try {
    $mail3->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail3->Debugoutput = function($str, $level) {
        echo "DEBUG ($level): $str\n";
    };
    $mail3->isSMTP();
    $mail3->Host = 'localhost';
    $mail3->SMTPAuth = false;  // Often no auth required for localhost
    $mail3->Port = 25;
    $mail3->setFrom($emailConfig['from_email'], $emailConfig['from_name']);
    $mail3->addAddress('admin@vuyanimagibisela.co.za');
    $mail3->Subject = 'Test - localhost:25 No Auth';
    $mail3->Body = '<h1>Success!</h1><p>localhost:25 without auth works!</p>';
    
    $mail3->send();
    echo "\n✅ SUCCESS: localhost:25 without auth works!\n\n";
} catch (Exception $e) {
    echo "\n❌ FAILED: " . $e->getMessage() . "\n\n";
}

echo "</pre>\n";
