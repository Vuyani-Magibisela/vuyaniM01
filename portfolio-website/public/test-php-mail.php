<?php
echo "<h2>Testing PHP mail() Function</h2>\n";
echo "<pre>\n";

// Check if mail() function exists
if (!function_exists('mail')) {
    echo "❌ mail() function is NOT available on this server\n";
    exit;
}

echo "✓ mail() function is available\n\n";

// Test sending email
$to = 'admin@vuyanimagibisela.co.za';
$subject = 'Test Email - PHP mail() function';
$message = '<html><body><h1>Test Email</h1><p>This email was sent using PHP\'s native mail() function.</p></body></html>';
$headers = "From: admin@vuyanimagibisela.co.za\r\n";
$headers .= "Reply-To: admin@vuyanimagibisela.co.za\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

echo "Attempting to send email to: $to\n";
echo "Subject: $subject\n\n";

$result = mail($to, $subject, $message, $headers);

if ($result) {
    echo "✅ SUCCESS: mail() function returned true!\n";
    echo "Email has been queued for delivery.\n";
    echo "Check your inbox at admin@vuyanimagibisela.co.za\n";
} else {
    echo "❌ FAILED: mail() function returned false\n";
    echo "The email could not be sent.\n";
}

echo "</pre>\n";
