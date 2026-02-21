<?php
/**
 * Web-based Email Test Page
 *
 * Quick browser form for testing the email system using the app's Email class.
 * Shows SMTP debug output and config (password masked).
 */

// Load composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load config
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
$config = require dirname(__DIR__) . '/app/config/email.php';

$result = null;
$debugOutput = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient = filter_input(INPUT_POST, 'recipient', FILTER_VALIDATE_EMAIL);
    $subject   = trim($_POST['subject'] ?? '');
    $message   = trim($_POST['message'] ?? '');

    if (!$recipient) {
        $error = 'Please enter a valid email address.';
    } elseif (!$subject) {
        $error = 'Please enter a subject.';
    } else {
        // Capture debug output
        ob_start();

        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $config['smtp_host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['smtp_username'];
            $mail->Password   = $config['smtp_password'];
            $mail->SMTPSecure = $config['smtp_encryption'];
            $mail->Port       = $config['smtp_port'];
            $mail->CharSet    = 'UTF-8';

            $mail->SMTPDebug  = 2;
            $mail->Debugoutput = function($str, $level) {
                echo htmlspecialchars(trim($str)) . "\n";
            };

            $mail->setFrom($config['from_email'], $config['from_name']);
            $mail->addAddress($recipient);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = nl2br(htmlspecialchars($message ?: 'Test email from web diagnostic page.'));
            $mail->AltBody = $message ?: 'Test email from web diagnostic page.';

            $mail->send();
            $result = true;

        } catch (PHPMailer\PHPMailer\Exception $e) {
            $result = false;
            $error  = 'PHPMailer error: ' . $e->getMessage();
        } catch (\Exception $e) {
            $result = false;
            $error  = 'Error: ' . $e->getMessage();
        }

        $debugOutput = ob_get_clean();
    }
}

$maskedPassword = substr($config['smtp_password'], 0, 3) . str_repeat('*', max(0, strlen($config['smtp_password']) - 3));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Send Test</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #0a0e27; color: #e0e0e0; padding: 2rem; }
        .container { max-width: 700px; margin: 0 auto; }
        h1 { color: #60a5fa; margin-bottom: 0.5rem; font-size: 1.5rem; }
        .subtitle { color: #9ca3af; margin-bottom: 2rem; }
        .config-box { background: #1a1f3a; border: 1px solid #2d3561; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem; font-family: monospace; font-size: 0.85rem; }
        .config-box .label { color: #9ca3af; display: inline-block; width: 100px; }
        .config-box .value { color: #60a5fa; }
        form { background: #1a1f3a; border: 1px solid #2d3561; border-radius: 8px; padding: 1.5rem; }
        label { display: block; color: #9ca3af; margin-bottom: 0.3rem; font-size: 0.9rem; }
        input, textarea { width: 100%; padding: 0.6rem; background: #0f1328; border: 1px solid #3d4570; border-radius: 4px; color: #e0e0e0; font-family: inherit; font-size: 0.95rem; margin-bottom: 1rem; }
        input:focus, textarea:focus { outline: none; border-color: #60a5fa; }
        textarea { height: 100px; resize: vertical; }
        button { background: #3b82f6; color: #fff; border: none; padding: 0.7rem 2rem; border-radius: 4px; font-size: 1rem; cursor: pointer; }
        button:hover { background: #2563eb; }
        .result { margin-top: 1.5rem; padding: 1rem; border-radius: 8px; }
        .result.success { background: #064e3b; border: 1px solid #10b981; }
        .result.error { background: #450a0a; border: 1px solid #ef4444; }
        .debug-box { margin-top: 1.5rem; background: #0f1328; border: 1px solid #2d3561; border-radius: 8px; padding: 1rem; }
        .debug-box h3 { color: #fbbf24; margin-bottom: 0.5rem; font-size: 0.95rem; }
        .debug-box pre { white-space: pre-wrap; word-break: break-all; font-size: 0.8rem; color: #9ca3af; max-height: 400px; overflow-y: auto; }
    </style>
</head>
<body>
<div class="container">
    <h1>Email Send Test</h1>
    <p class="subtitle">Test the email system using PHPMailer with SMTP</p>

    <div class="config-box">
        <div><span class="label">Host:</span> <span class="value"><?= htmlspecialchars($config['smtp_host']) ?></span></div>
        <div><span class="label">Port:</span> <span class="value"><?= htmlspecialchars($config['smtp_port']) ?></span></div>
        <div><span class="label">Username:</span> <span class="value"><?= htmlspecialchars($config['smtp_username']) ?></span></div>
        <div><span class="label">Password:</span> <span class="value"><?= htmlspecialchars($maskedPassword) ?></span></div>
        <div><span class="label">Encryption:</span> <span class="value"><?= htmlspecialchars($config['smtp_encryption']) ?></span></div>
    </div>

    <form method="POST">
        <label for="recipient">Recipient Email</label>
        <input type="email" id="recipient" name="recipient" required
               value="<?= htmlspecialchars($_POST['recipient'] ?? $config['admin_email']) ?>"
               placeholder="test@example.com">

        <label for="subject">Subject</label>
        <input type="text" id="subject" name="subject" required
               value="<?= htmlspecialchars($_POST['subject'] ?? 'Test Email - ' . date('Y-m-d H:i')) ?>"
               placeholder="Test email subject">

        <label for="message">Message (optional)</label>
        <textarea id="message" name="message" placeholder="Test message body..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>

        <button type="submit">Send Test Email</button>
    </form>

    <?php if ($result === true): ?>
        <div class="result success">
            Email sent successfully to <strong><?= htmlspecialchars($recipient) ?></strong>. Check inbox and spam folder.
        </div>
    <?php elseif ($result === false || $error): ?>
        <div class="result error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($debugOutput): ?>
        <div class="debug-box">
            <h3>SMTP Debug Output</h3>
            <pre><?= $debugOutput ?></pre>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
