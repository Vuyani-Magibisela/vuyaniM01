#!/usr/bin/env php
<?php
/**
 * SMTP Email Diagnostic Script
 *
 * Comprehensive CLI diagnostic for testing email sending capability.
 * Runs 9 sequential test phases covering DNS, connectivity, auth, and sending.
 *
 * Usage: php scripts/smtp-diagnostic.php [recipient@email.com]
 */

// Must run from CLI
if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function colorize(string $text, string $color): string {
    $colors = [
        'green'   => "\033[32m",
        'red'     => "\033[31m",
        'yellow'  => "\033[33m",
        'cyan'    => "\033[36m",
        'white'   => "\033[37m",
        'bold'    => "\033[1m",
        'reset'   => "\033[0m",
    ];
    return ($colors[$color] ?? '') . $text . $colors['reset'];
}

function printHeader(int $phase, string $title): void {
    echo "\n" . colorize("═══════════════════════════════════════════════════════════", 'cyan') . "\n";
    echo colorize("  Phase $phase: $title", 'bold') . "\n";
    echo colorize("═══════════════════════════════════════════════════════════", 'cyan') . "\n";
}

function pass(string $msg): void {
    echo "  " . colorize("[PASS]", 'green') . " $msg\n";
}

function fail(string $msg): void {
    echo "  " . colorize("[FAIL]", 'red') . " $msg\n";
}

function info(string $msg): void {
    echo "  " . colorize("[INFO]", 'yellow') . " $msg\n";
}

function skip(string $msg): void {
    echo "  " . colorize("[SKIP]", 'yellow') . " $msg\n";
}

// ---------------------------------------------------------------------------
// Load configuration
// ---------------------------------------------------------------------------

// Simulate web environment so config doesn't fail on missing $_SERVER keys
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';

$configPath = dirname(__DIR__) . '/app/config/email.php';
if (!file_exists($configPath)) {
    echo colorize("ERROR: Config file not found: $configPath\n", 'red');
    exit(1);
}

$config = require $configPath;
$recipient = $argv[1] ?? $config['admin_email'];

echo colorize("\n  SMTP Email Diagnostic Tool\n", 'bold');
echo "  Host:      {$config['smtp_host']}\n";
echo "  Port:      {$config['smtp_port']}\n";
echo "  Username:  {$config['smtp_username']}\n";
echo "  Password:  " . substr($config['smtp_password'], 0, 3) . str_repeat('*', max(0, strlen($config['smtp_password']) - 3)) . "\n";
echo "  Encrypt:   {$config['smtp_encryption']}\n";
echo "  Recipient: $recipient\n";

$results = [];

// ---------------------------------------------------------------------------
// Phase 1: DNS Resolution
// ---------------------------------------------------------------------------

printHeader(1, 'DNS Resolution');

$host = $config['smtp_host'];
$ip = gethostbyname($host);

if ($ip !== $host) {
    pass("$host resolves to $ip");
    $results[1] = true;
} else {
    fail("Could not resolve $host");
    $results[1] = false;
}

// MX records
$mxHosts = [];
$mxWeights = [];
if (getmxrr(explode('.', $config['smtp_username'])[1] ?? $host, $mxHosts, $mxWeights)) {
    $domain = explode('@', $config['smtp_username'])[1] ?? $host;
    if (getmxrr($domain, $mxHosts, $mxWeights)) {
        foreach ($mxHosts as $i => $mx) {
            info("MX record: $mx (priority {$mxWeights[$i]})");
        }
    }
} else {
    // Try with the domain from username
    $domain = explode('@', $config['smtp_username'])[1] ?? '';
    if ($domain && getmxrr($domain, $mxHosts, $mxWeights)) {
        foreach ($mxHosts as $i => $mx) {
            info("MX record: $mx (priority {$mxWeights[$i]})");
        }
    } else {
        info("No MX records found (may use A record fallback)");
    }
}

// ---------------------------------------------------------------------------
// Phase 2: Port Connectivity
// ---------------------------------------------------------------------------

printHeader(2, 'Port Connectivity');

$portsToTest = [25, 465, 587];
$openPorts = [];

foreach ($portsToTest as $port) {
    $errno = 0;
    $errstr = '';
    $fp = @fsockopen($host, $port, $errno, $errstr, 5);
    if ($fp) {
        pass("Port $port is OPEN");
        $openPorts[] = $port;
        fclose($fp);
    } else {
        fail("Port $port is CLOSED ($errstr)");
    }
}

$results[2] = count($openPorts) > 0;

if (empty($openPorts)) {
    fail("No SMTP ports are open. Check firewall/hosting.");
}

// ---------------------------------------------------------------------------
// Phase 3: SMTP Banner
// ---------------------------------------------------------------------------

printHeader(3, 'SMTP Banner');
$results[3] = false;

$bannerPort = in_array(25, $openPorts) ? 25 : (in_array(587, $openPorts) ? 587 : null);

if ($bannerPort) {
    $fp = @fsockopen($host, $bannerPort, $errno, $errstr, 5);
    if ($fp) {
        stream_set_timeout($fp, 5);
        $banner = fgets($fp, 1024);
        if ($banner && strpos($banner, '220') === 0) {
            pass("Banner on port $bannerPort: " . trim($banner));
            $results[3] = true;
        } else {
            fail("Unexpected banner: " . trim($banner ?: '(empty)'));
        }
        fclose($fp);
    }
} else {
    // Try SSL on 465
    if (in_array(465, $openPorts)) {
        $ctx = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        $fp = @stream_socket_client("ssl://$host:465", $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $ctx);
        if ($fp) {
            stream_set_timeout($fp, 5);
            $banner = fgets($fp, 1024);
            if ($banner && strpos($banner, '220') === 0) {
                pass("Banner on port 465 (SSL): " . trim($banner));
                $results[3] = true;
            } else {
                fail("Unexpected SSL banner: " . trim($banner ?: '(empty)'));
            }
            fclose($fp);
        } else {
            fail("Could not connect via SSL to port 465");
        }
    } else {
        skip("No plain-text SMTP ports open for banner check");
    }
}

// ---------------------------------------------------------------------------
// Phase 4: EHLO Capabilities
// ---------------------------------------------------------------------------

printHeader(4, 'EHLO Capabilities');
$results[4] = false;
$authMethods = [];

function smtpConversation(string $host, int $port, bool $ssl = false): ?array {
    $capabilities = [];

    if ($ssl) {
        $ctx = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        $fp = @stream_socket_client("ssl://$host:$port", $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $ctx);
    } else {
        $fp = @fsockopen($host, $port, $errno, $errstr, 5);
    }

    if (!$fp) return null;

    stream_set_timeout($fp, 5);
    fgets($fp, 1024); // read banner

    fwrite($fp, "EHLO localhost\r\n");

    while ($line = fgets($fp, 1024)) {
        $line = trim($line);
        $capabilities[] = $line;
        // Lines starting with "250 " (space, not dash) indicate last line
        if (preg_match('/^250 /', $line)) break;
    }

    fwrite($fp, "QUIT\r\n");
    fclose($fp);

    return $capabilities;
}

// Try port 465 with SSL first (most common config), then 587, then 25
$ehloAttempts = [];
if (in_array(465, $openPorts)) $ehloAttempts[] = [465, true];
if (in_array(587, $openPorts)) $ehloAttempts[] = [587, false];
if (in_array(25, $openPorts))  $ehloAttempts[] = [25, false];

foreach ($ehloAttempts as [$port, $ssl]) {
    $label = $ssl ? "$port (SSL)" : "$port";
    $caps = smtpConversation($host, $port, $ssl);
    if ($caps) {
        info("Capabilities on port $label:");
        foreach ($caps as $cap) {
            echo "    $cap\n";
            if (stripos($cap, 'AUTH') !== false) {
                preg_match('/AUTH\s+(.+)/i', $cap, $m);
                if (isset($m[1])) {
                    $authMethods = array_merge($authMethods, preg_split('/\s+/', trim($m[1])));
                }
            }
        }
        $results[4] = true;
    } else {
        fail("Could not get EHLO response from port $label");
    }
}

$authMethods = array_unique($authMethods);
if (!empty($authMethods)) {
    pass("Auth methods found: " . implode(', ', $authMethods));
} else {
    info("No AUTH methods advertised (may still work with implicit auth)");
}

// ---------------------------------------------------------------------------
// Phase 5: SSL/TLS
// ---------------------------------------------------------------------------

printHeader(5, 'SSL/TLS');
$results[5] = false;

// Test SSL on 465
if (in_array(465, $openPorts)) {
    $ctx = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'capture_peer_cert' => true]]);
    $fp = @stream_socket_client("ssl://$host:465", $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $ctx);
    if ($fp) {
        pass("SSL connection on port 465 successful");
        $params = stream_context_get_params($fp);
        if (isset($params['options']['ssl']['peer_certificate'])) {
            $cert = openssl_x509_parse($params['options']['ssl']['peer_certificate']);
            if ($cert) {
                info("Certificate CN: " . ($cert['subject']['CN'] ?? 'N/A'));
                info("Issuer: " . ($cert['issuer']['O'] ?? $cert['issuer']['CN'] ?? 'N/A'));
                info("Valid until: " . date('Y-m-d', $cert['validTo_time_t'] ?? 0));
            }
        }
        fclose($fp);
        $results[5] = true;
    } else {
        fail("SSL connection on port 465 failed: $errstr");
    }
}

// Test STARTTLS on 587
if (in_array(587, $openPorts)) {
    $fp = @fsockopen($host, 587, $errno, $errstr, 5);
    if ($fp) {
        stream_set_timeout($fp, 5);
        fgets($fp, 1024); // banner
        fwrite($fp, "EHLO localhost\r\n");
        while ($line = fgets($fp, 1024)) {
            if (preg_match('/^250 /', $line)) break;
        }
        fwrite($fp, "STARTTLS\r\n");
        $response = fgets($fp, 1024);
        if ($response && strpos($response, '220') === 0) {
            pass("STARTTLS on port 587 accepted");
            $results[5] = true;
        } else {
            fail("STARTTLS on port 587 rejected: " . trim($response ?: '(no response)'));
        }
        fclose($fp);
    }
}

if (!$results[5] && empty($openPorts)) {
    skip("No ports available for SSL/TLS testing");
}

// ---------------------------------------------------------------------------
// Phase 6: Authentication
// ---------------------------------------------------------------------------

printHeader(6, 'Authentication');
$results[6] = false;

$username = $config['smtp_username'];
$password = $config['smtp_password'];

function testSmtpAuth(string $host, int $port, string $username, string $password, bool $ssl = false): array {
    if ($ssl) {
        $ctx = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        $fp = @stream_socket_client("ssl://$host:$port", $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $ctx);
    } else {
        $fp = @fsockopen($host, $port, $errno, $errstr, 10);
    }

    if (!$fp) return ['success' => false, 'error' => "Connection failed: $errstr"];

    stream_set_timeout($fp, 10);

    $banner = fgets($fp, 1024);
    if (!$banner || strpos($banner, '220') !== 0) {
        fclose($fp);
        return ['success' => false, 'error' => "Bad banner: " . trim($banner ?: '(empty)')];
    }

    fwrite($fp, "EHLO localhost\r\n");
    $ehloResponse = '';
    while ($line = fgets($fp, 1024)) {
        $ehloResponse .= $line;
        if (preg_match('/^250 /', $line)) break;
    }

    // Try AUTH LOGIN
    fwrite($fp, "AUTH LOGIN\r\n");
    $response = fgets($fp, 1024);

    if ($response && strpos($response, '334') === 0) {
        fwrite($fp, base64_encode($username) . "\r\n");
        $response = fgets($fp, 1024);

        if ($response && strpos($response, '334') === 0) {
            fwrite($fp, base64_encode($password) . "\r\n");
            $response = fgets($fp, 1024);

            fwrite($fp, "QUIT\r\n");
            fclose($fp);

            if ($response && strpos($response, '235') === 0) {
                return ['success' => true, 'error' => '', 'response' => trim($response)];
            } else {
                return ['success' => false, 'error' => 'Auth rejected: ' . trim($response ?: '(no response)')];
            }
        }
    }

    fwrite($fp, "QUIT\r\n");
    fclose($fp);

    return ['success' => false, 'error' => 'AUTH LOGIN not supported or failed: ' . trim($response ?: '(no response)')];
}

// Test auth on each viable port/encryption combo
$authAttempts = [];
if (in_array(465, $openPorts)) $authAttempts[] = ['port' => 465, 'ssl' => true, 'label' => '465/SSL'];
if (in_array(587, $openPorts)) $authAttempts[] = ['port' => 587, 'ssl' => false, 'label' => '587/STARTTLS'];
if (in_array(25, $openPorts))  $authAttempts[] = ['port' => 25,  'ssl' => false, 'label' => '25/Plain'];

foreach ($authAttempts as $attempt) {
    $result = testSmtpAuth($host, $attempt['port'], $username, $password, $attempt['ssl']);
    if ($result['success']) {
        pass("Authentication on port {$attempt['label']} SUCCEEDED");
        if (!empty($result['response'])) info("Server response: {$result['response']}");
        $results[6] = true;
    } else {
        fail("Authentication on port {$attempt['label']} FAILED: {$result['error']}");
    }
}

if (empty($authAttempts)) {
    skip("No ports available for authentication testing");
}

// ---------------------------------------------------------------------------
// Phase 7: PHPMailer Send
// ---------------------------------------------------------------------------

printHeader(7, 'PHPMailer Send');
$results[7] = false;

if ($results[6]) {
    // Load composer autoloader
    $autoloader = dirname(__DIR__) . '/vendor/autoload.php';
    $vendorDir  = dirname(__DIR__) . '/vendor';
    if (file_exists($autoloader)) {
        require_once $autoloader;
    }

    // Fallback: manually require PHPMailer if autoloader didn't register the class
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $phpmailerSrc = $vendorDir . '/phpmailer/phpmailer/src';
        if (is_dir($phpmailerSrc)) {
            require_once $phpmailerSrc . '/Exception.php';
            require_once $phpmailerSrc . '/PHPMailer.php';
            require_once $phpmailerSrc . '/SMTP.php';
            info("Loaded PHPMailer via direct require (autoloader didn't register namespace)");
        }
    }

    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
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

            // Enable verbose debug output
            $mail->SMTPDebug  = 2;
            $mail->Debugoutput = function($str, $level) {
                $str = trim($str);
                if ($str) echo "    " . colorize("DEBUG", 'yellow') . " $str\n";
            };

            $mail->setFrom($config['from_email'], $config['from_name']);
            $mail->addAddress($recipient);
            $mail->isHTML(true);
            $mail->Subject = 'SMTP Diagnostic Test - ' . date('Y-m-d H:i:s');
            $mail->Body    = '<h2>SMTP Diagnostic Test</h2>'
                           . '<p>This email was sent by the SMTP diagnostic script.</p>'
                           . '<p>Sent at: ' . date('Y-m-d H:i:s T') . '</p>'
                           . '<p>Server: ' . $config['smtp_host'] . ':' . $config['smtp_port'] . '</p>';
            $mail->AltBody = "SMTP Diagnostic Test\nSent at: " . date('Y-m-d H:i:s T');

            $mail->send();
            pass("Email sent successfully to $recipient");
            $results[7] = true;

        } catch (PHPMailer\PHPMailer\Exception $e) {
            fail("PHPMailer error: " . $e->getMessage());
        } catch (\Exception $e) {
            fail("Error: " . $e->getMessage());
        }
    } else {
        fail("PHPMailer class not found. Check composer install or vendor/phpmailer/ directory.");
        info("Run: composer require phpmailer/phpmailer");
    }
} else {
    skip("Skipping PHPMailer send — authentication failed in Phase 6");
}

// ---------------------------------------------------------------------------
// Phase 8: PHP mail() Fallback
// ---------------------------------------------------------------------------

printHeader(8, 'PHP mail() Fallback');
$results[8] = false;

$sendmailPath = ini_get('sendmail_path');
info("sendmail_path: " . ($sendmailPath ?: '(not set)'));

$mailResult = @mail(
    $recipient,
    'PHP mail() Test - ' . date('Y-m-d H:i:s'),
    "This is a test from PHP mail() function.\nSent at: " . date('Y-m-d H:i:s T'),
    "From: {$config['from_email']}\r\nX-Mailer: PHP/" . phpversion()
);

if ($mailResult) {
    pass("PHP mail() returned true (check inbox — delivery not guaranteed)");
    $results[8] = true;
} else {
    fail("PHP mail() returned false — local MTA likely not configured");
    info("This is expected on most hosting without a local mail server");
}

// ---------------------------------------------------------------------------
// Phase 9: Summary
// ---------------------------------------------------------------------------

printHeader(9, 'Summary');

$phaseNames = [
    1 => 'DNS Resolution',
    2 => 'Port Connectivity',
    3 => 'SMTP Banner',
    4 => 'EHLO Capabilities',
    5 => 'SSL/TLS',
    6 => 'Authentication',
    7 => 'PHPMailer Send',
    8 => 'PHP mail() Fallback',
];

$passed = 0;
$failed = 0;

foreach ($phaseNames as $num => $name) {
    $status = $results[$num] ?? false;
    if ($status) {
        echo "  " . colorize("✓", 'green') . " Phase $num: $name\n";
        $passed++;
    } else {
        echo "  " . colorize("✗", 'red') . " Phase $num: $name\n";
        $failed++;
    }
}

echo "\n  " . colorize("Results: $passed passed, $failed failed", $failed === 0 ? 'green' : ($passed > $failed ? 'yellow' : 'red')) . "\n";

// Actionable diagnosis
echo "\n" . colorize("  Diagnosis:", 'bold') . "\n";

if ($results[7] ?? false) {
    echo "  " . colorize("Email system is fully working!", 'green') . "\n";
    echo "  PHPMailer successfully sent a test email to $recipient.\n";
    echo "  Check inbox (and spam folder) for delivery confirmation.\n";
} elseif ($results[6] ?? false) {
    echo "  " . colorize("Authentication works but sending failed.", 'yellow') . "\n";
    echo "  Next steps:\n";
    echo "    - Check PHPMailer error messages above\n";
    echo "    - Verify sender address is allowed by the server\n";
    echo "    - Check if the hosting provider blocks outgoing mail\n";
} elseif ($results[5] ?? false) {
    echo "  " . colorize("SSL/TLS works but authentication failed.", 'red') . "\n";
    echo "  Next steps:\n";
    echo "    - Verify username: {$config['smtp_username']}\n";
    echo "    - Verify password is correct (check for special characters)\n";
    echo "    - Check if the email account is active and not locked\n";
    echo "    - Try logging into webmail with these credentials\n";
} elseif ($results[2] ?? false) {
    echo "  " . colorize("Ports are open but connection/TLS issues.", 'red') . "\n";
    echo "  Next steps:\n";
    echo "    - Check if the mail server requires a specific TLS version\n";
    echo "    - Try different port/encryption combinations in config\n";
} elseif ($results[1] ?? false) {
    echo "  " . colorize("DNS resolves but no ports are reachable.", 'red') . "\n";
    echo "  Next steps:\n";
    echo "    - Check if a firewall is blocking outgoing SMTP ports\n";
    echo "    - Contact hosting provider about SMTP port access\n";
    echo "    - Try using port 587 with STARTTLS instead of 465/SSL\n";
} else {
    echo "  " . colorize("DNS resolution failed.", 'red') . "\n";
    echo "  Next steps:\n";
    echo "    - Verify smtp_host in config: {$config['smtp_host']}\n";
    echo "    - Check DNS settings for the domain\n";
    echo "    - Try using the mail server's IP address directly\n";
}

echo "\n";
