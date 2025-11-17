<?php
/**
 * Database Migration Runner
 * Run all SQL migrations in order
 *
 * Usage: php database/run_migrations.php
 */

// Set HTTP_HOST for CLI environment
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}

require_once __DIR__ . '/../vendor/autoload.php';

// Load database configuration
$dbConfig = require __DIR__ . '/../app/config/database.php';

echo "=== Database Migration Runner ===\n\n";

try {
    // Get database connection directly using PDO
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}",
        $dbConfig['user'],
        $dbConfig['password'],
        $dbConfig['options']
    );

    if (!$pdo) {
        throw new Exception("Failed to connect to database");
    }

    echo "✓ Connected to database\n\n";

    // Get all migration files
    $migrationPath = __DIR__ . '/migrations';
    $seedPath = __DIR__ . '/seeds';

    $migrationFiles = glob($migrationPath . '/*.sql');
    $seedFiles = glob($seedPath . '/*.sql');

    sort($migrationFiles); // Ensure migrations run in order

    echo "Found " . count($migrationFiles) . " migration(s)\n";
    echo "Found " . count($seedFiles) . " seed file(s)\n\n";

    // Run migrations
    echo "--- Running Migrations ---\n";
    foreach ($migrationFiles as $file) {
        $filename = basename($file);
        echo "Running: $filename... ";

        $sql = file_get_contents($file);

        try {
            $pdo->exec($sql);
            echo "✓ SUCCESS\n";
        } catch (PDOException $e) {
            echo "✗ FAILED: " . $e->getMessage() . "\n";
        }
    }

    echo "\n--- Running Seeds ---\n";
    foreach ($seedFiles as $file) {
        $filename = basename($file);
        echo "Running: $filename... ";

        $sql = file_get_contents($file);

        try {
            $pdo->exec($sql);
            echo "✓ SUCCESS\n";
        } catch (PDOException $e) {
            echo "✗ FAILED: " . $e->getMessage() . "\n";
        }
    }

    echo "\n=== Migration Complete ===\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
