<?php
// File: public/test-db.php
// Remove this file after testing!

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test</h2>";

// Test 1: Check if config file exists
$configPath = dirname(__DIR__) . '/app/config/database.php';
echo "<h3>Test 1: Config File</h3>";
if (file_exists($configPath)) {
    echo "✅ Config file exists: " . $configPath . "<br>";
    $config = require $configPath;
    echo "Database: " . $config['dbname'] . "<br>";
    echo "Host: " . $config['host'] . "<br>";
    echo "User: " . $config['user'] . "<br>";
} else {
    echo "❌ Config file not found: " . $configPath . "<br>";
    exit;
}

// Test 2: Try direct PDO connection
echo "<h3>Test 2: Direct Connection</h3>";
try {
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['user'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Direct PDO connection successful<br>";
    
    // Test query
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "✅ Test query successful: " . $result->test . "<br>";
    
} catch (PDOException $e) {
    echo "❌ Direct connection failed: " . $e->getMessage() . "<br>";
}

// Test 3: Using your Database class
echo "<h3>Test 3: Using Database Class</h3>";
require_once dirname(__DIR__) . '/app/core/Database.php';

try {
    $db = App\core\Database::connect();
    if ($db) {
        echo "✅ Database class connection successful<br>";
    } else {
        echo "❌ Database class returned null<br>";
    }
} catch (Exception $e) {
    echo "❌ Database class failed: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p><strong>Note:</strong> Delete this file after testing!</p>";
?>