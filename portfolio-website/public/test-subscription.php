<?php
// Test subscription endpoint
$basePath = dirname(__DIR__);
require_once $basePath . '/vendor/autoload.php';
require_once $basePath . '/app/core/Database.php';
require_once $basePath . '/app/models/BaseModel.php';
require_once $basePath . '/app/models/Subscriber.php';
require_once $basePath . '/app/controllers/BaseController.php';
require_once $basePath . '/app/controllers/SubscriptionController.php';

use App\Controllers\SubscriptionController;

echo "<h2>Testing Subscription Endpoint</h2>\n";
echo "<pre>\n";

// Simulate AJAX POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'Test Script';

// Simulate JSON input
$testEmail = 'test' . time() . '@example.com';
$_POST = [];
file_put_contents('php://input', json_encode(['email' => $testEmail]));

echo "Testing subscription for: $testEmail\n\n";

try {
    $controller = new SubscriptionController();
    
    // Capture output
    ob_start();
    $controller->subscribe();
    $output = ob_get_clean();
    
    echo "Response:\n";
    echo $output . "\n";
    
    $response = json_decode($output, true);
    if ($response && isset($response['success'])) {
        if ($response['success']) {
            echo "\n✅ Subscription endpoint working!\n";
            echo "Message: " . $response['message'] . "\n";
        } else {
            echo "\n⚠️ Subscription failed\n";
            echo "Message: " . $response['message'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "</pre>\n";
