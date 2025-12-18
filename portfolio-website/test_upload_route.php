<?php
// Simple test to check if routing to admin/uploadImage works
echo "Testing upload route...\n";
echo "URL should be: /vuyaniM01/portfolio-website/public/admin/uploadImage\n\n";

// Simulate the router
$_GET['url'] = 'admin/uploadImage';

$url = $_GET['url'] ?? 'home/index';
$url = explode('/', filter_var(rtrim($url, '/'), FILTER_SANITIZE_URL));

echo "Parsed URL parts:\n";
echo "url[0] (controller): " . $url[0] . "\n";
echo "url[1] (method): " . ($url[1] ?? 'index') . "\n";

$controller = ucfirst($url[0]) . 'Controller';
$method = $url[1] ?? 'index';

echo "\nExpected:\n";
echo "Controller: " . $controller . "\n";
echo "Method: " . $method . "\n";

echo "\nController file should exist at: ";
$controllerPath = __DIR__ . '/portfolio-website/app/controllers/' . $controller . '.php';
echo $controllerPath . "\n";
echo "Exists: " . (file_exists($controllerPath) ? 'YES' : 'NO') . "\n";

if (file_exists($controllerPath)) {
    echo "\nChecking if method exists in controller...\n";
    require_once __DIR__ . '/portfolio-website/app/core/BaseController.php';
    require_once __DIR__ . '/portfolio-website/app/core/Session.php';
    require_once __DIR__ . '/portfolio-website/app/controllers/AuthController.php';
    require_once $controllerPath;

    $fullClass = "App\\Controllers\\" . $controller;
    echo "Full class name: " . $fullClass . "\n";
    echo "Class exists: " . (class_exists($fullClass) ? 'YES' : 'NO') . "\n";

    if (class_exists($fullClass)) {
        echo "Method exists: " . (method_exists($fullClass, $method) ? 'YES' : 'NO') . "\n";
    }
}
