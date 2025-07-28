<?php
// File: migrate.php
// CLI migration tool for development environments

require_once 'portfolio-website/app/core/Database.php';
require_once 'portfolio-website/app/core/App.php';

// For development, use the original structure
class MigrationManagerCLI {
    private $db;
    private $migrationsPath;
    private $seedsPath;
    
    public function __construct() {
        $this->db = Database::connect();
        $this->migrationsPath = __DIR__ . '/portfolio-website/database/migrations/';
        $this->seedsPath = __DIR__ . '/portfolio-website/database/seeds/';
        
        // Ensure directories exist
        if (!is_dir($this->migrationsPath)) {
            mkdir($this->migrationsPath, 0755, true);
        }
        if (!is_dir($this->seedsPath)) {
            mkdir($this->seedsPath, 0755, true);
        }
        
        // Create migrations table if it doesn't exist
        $this->createMigrationsTable();
    }
    
    private function createMigrationsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_migration (migration)
        )";
        
        $this->db->exec($sql);
    }
    
    public function handleCommand($argv) {
        if (count($argv) < 2) {
            $this->showHelp();
            return;
        }
        
        $command = $argv[1];
        
        switch ($command) {
            case 'migrate':
                $this->migrate();
                break;
                
            case 'rollback':
                $this->rollback();
                break;
                
            case 'status':
                $this->showStatus();
                break;
                
            case 'seed':
                $environment = $argv[2] ?? 'development';
                $this->seed($environment);
                break;
                
            case 'fresh':
                $this->fresh();
                break;
                
            case 'create:migration':
                if (!isset($argv[2])) {
                    echo "Error: Migration name required\n";
                    echo "Usage: php migrate.php create:migration migration_name\n";
                    return;
                }
                $this->createMigration($argv[2]);
                break;
                
            case 'create:seed':
                if (!isset($argv[2])) {
                    echo "Error: Seed name required\n";
                    echo "Usage: php migrate.php create:seed seed_name\n";
                    return;
                }
                $this->createSeed($argv[2]);
                break;
                
            default:
                $this->showHelp();
                break;
        }
    }
    
    private function showHelp() {
        echo "\n=== Database Migration CLI Tool ===\n\n";
        echo "Available commands:\n";
        echo "  migrate              Run all pending migrations\n";
        echo "  rollback             Rollback the last batch of migrations\n";
        echo "  status               Show migration status\n";
        echo "  seed [env]           Run seeds (env: development|production)\n";
        echo "  fresh                Reset database and run all migrations with production seeds\n";
        echo "  create:migration     Create a new migration file\n";
        echo "  create:seed          Create a new seed file\n";
        echo "\nExamples:\n";
        echo "  php migrate.php migrate\n";
        echo "  php migrate.php create:migration create_users_table\n";
        echo "  php migrate.php seed development\n\n";
    }
    
    public function migrate() {
        try {
            $this->log("Starting migration process...");
            
            $migrations = $this->getPendingMigrations();
            if (empty($migrations)) {
                $this->log("No pending migrations found.");
                return true;
            }
            
            $batch = $this->getNextBatchNumber();
            
            foreach ($migrations as $migration) {
                $this->runMigration($migration, $batch);
            }
            
            $this->log("All migrations completed successfully.");
            return true;
            
        } catch (Exception $e) {
            $this->log("Migration failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function rollback() {
        try {
            $this->log("Starting rollback process...");
            
            $lastBatch = $this->getLastBatch();
            if (!$lastBatch) {
                $this->log("No migrations to rollback.");
                return true;
            }
            
            $migrations = $this->getMigrationsInBatch($lastBatch);
            
            // Rollback in reverse order
            foreach (array_reverse($migrations) as $migration) {
                $this->rollbackMigration($migration);
            }
            
            $this->log("Rollback completed successfully.");
            return true;
            
        } catch (Exception $e) {
            $this->log("Rollback failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function seed($environment = 'development') {
        try {
            $this->log("Starting seeding process for {$environment}...");
            
            $seeds = $this->getSeeds();
            foreach ($seeds as $seed) {
                $this->runSeed($seed, $environment);
            }
            
            $this->log("Seeding completed successfully.");
            return true;
            
        } catch (Exception $e) {
            $this->log("Seeding failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function fresh() {
        try {
            $this->log("Starting fresh installation...");
            
            // Rollback all migrations
            $status = $this->getStatus();
            while ($status['total_executed'] > 0) {
                $this->rollback();
                $status = $this->getStatus();
            }
            
            // Run migrations
            $this->migrate();
            
            // Run production seeds
            $this->seed('production');
            
            $this->log("Fresh installation completed successfully.");
            return true;
            
        } catch (Exception $e) {
            $this->log("Fresh installation failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function showStatus() {
        $status = $this->getStatus();
        
        echo "\n=== Migration Status ===\n\n";
        echo "Total migrations: " . $status['total_migrations'] . "\n";
        echo "Executed: " . $status['total_executed'] . "\n";
        echo "Pending: " . $status['total_pending'] . "\n";
        echo "Last batch: " . ($status['last_batch'] ?? 'None') . "\n\n";
        
        if (!empty($status['pending_migrations'])) {
            echo "Pending migrations:\n";
            foreach ($status['pending_migrations'] as $migration) {
                echo "  - " . $migration . "\n";
            }
            echo "\n";
        }
        
        if (!empty($status['executed_migrations'])) {
            echo "Executed migrations:\n";
            foreach (array_reverse($status['executed_migrations']) as $migration) {
                echo "  ✓ " . $migration['migration'] . " (Batch " . $migration['batch'] . ", " . $migration['executed_at'] . ")\n";
            }
            echo "\n";
        }
    }
    
    public function createMigration($name) {
        $timestamp = date('Y_m_d_His');
        $className = 'Migration_' . $timestamp . '_' . ucfirst($name);
        $filename = $timestamp . '_' . strtolower($name) . '.php';
        $filepath = $this->migrationsPath . $filename;
        
        $template = "<?php

class {$className} {
    
    public function up() {
        // Migration logic here
        // Example:
        // \$sql = \"CREATE TABLE example (
        //     id INT AUTO_INCREMENT PRIMARY KEY,
        //     name VARCHAR(255) NOT NULL,
        //     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        // )\";
        // Database::connect()->exec(\$sql);
    }
    
    public function down() {
        // Rollback logic here
        // Example:
        // Database::connect()->exec(\"DROP TABLE IF EXISTS example\");
    }
}";
        
        file_put_contents($filepath, $template);
        $this->log("Migration created: {$filename}");
        echo "Migration file created at: {$filepath}\n";
        return $filepath;
    }
    
    public function createSeed($name) {
        $className = ucfirst($name) . 'Seeder';
        $filename = strtolower($name) . '.php';
        $filepath = $this->seedsPath . $filename;
        
        $template = "<?php

class {$className} {
    
    public function run(\$environment = 'production') {
        \$db = Database::connect();
        
        // Seed logic here based on environment
        if (\$environment === 'development') {
            // Development data
            // Example:
            // \$sql = \"INSERT INTO example (name) VALUES ('Test Data')\";
            // \$db->exec(\$sql);
        } else {
            // Production data
            // Example:
            // \$sql = \"INSERT INTO example (name) VALUES ('Production Data')\";
            // \$db->exec(\$sql);
        }
    }
}";
        
        file_put_contents($filepath, $template);
        $this->log("Seed created: {$filename}");
        echo "Seed file created at: {$filepath}\n";
        return $filepath;
    }
    
    // ... (Include all the private helper methods from MigrationManager.php)
    
    private function getPendingMigrations() {
        $all = $this->getAllMigrations();
        $executed = array_column($this->getExecutedMigrations(), 'migration');
        return array_diff($all, $executed);
    }
    
    private function getAllMigrations() {
        $files = glob($this->migrationsPath . '*.php');
        $migrations = [];
        
        foreach ($files as $file) {
            $migrations[] = basename($file, '.php');
        }
        
        sort($migrations);
        return $migrations;
    }
    
    private function getExecutedMigrations() {
        $stmt = $this->db->query("SELECT * FROM migrations ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getStatus() {
        $executed = $this->getExecutedMigrations();
        $all = $this->getAllMigrations();
        $pending = array_diff($all, array_column($executed, 'migration'));
        
        return [
            'total_migrations' => count($all),
            'total_executed' => count($executed),
            'total_pending' => count($pending),
            'executed_migrations' => $executed,
            'pending_migrations' => $pending,
            'last_batch' => $this->getLastBatch()
        ];
    }
    
    private function getNextBatchNumber() {
        $stmt = $this->db->query("SELECT MAX(batch) as max_batch FROM migrations");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($result['max_batch'] ?? 0) + 1;
    }
    
    private function getLastBatch() {
        $stmt = $this->db->query("SELECT MAX(batch) as max_batch FROM migrations");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['max_batch'] ?? null;
    }
    
    private function getMigrationsInBatch($batch) {
        $stmt = $this->db->prepare("SELECT migration FROM migrations WHERE batch = ? ORDER BY id");
        $stmt->execute([$batch]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'migration');
    }
    
    private function runMigration($migration, $batch) {
        $this->log("Running migration: {$migration}");
        
        try {
            $this->db->beginTransaction();
            
            // Include and execute migration
            $migrationClass = $this->loadMigrationClass($migration);
            $migrationClass->up();
            
            // Record migration
            $stmt = $this->db->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
            $stmt->execute([$migration, $batch]);
            
            $this->db->commit();
            $this->log("Migration completed: {$migration}");
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception("Migration {$migration} failed: " . $e->getMessage());
        }
    }
    
    private function rollbackMigration($migration) {
        $this->log("Rolling back migration: {$migration}");
        
        try {
            $this->db->beginTransaction();
            
            // Include and execute rollback
            $migrationClass = $this->loadMigrationClass($migration);
            $migrationClass->down();
            
            // Remove migration record
            $stmt = $this->db->prepare("DELETE FROM migrations WHERE migration = ?");
            $stmt->execute([$migration]);
            
            $this->db->commit();
            $this->log("Migration rolled back: {$migration}");
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception("Rollback {$migration} failed: " . $e->getMessage());
        }
    }
    
    private function loadMigrationClass($migration) {
        $filepath = $this->migrationsPath . $migration . '.php';
        
        if (!file_exists($filepath)) {
            throw new Exception("Migration file not found: {$filepath}");
        }
        
        require_once $filepath;
        
        // Extract class name from migration filename
        $parts = explode('_', $migration);
        $className = 'Migration_' . implode('_', $parts);
        
        if (!class_exists($className)) {
            throw new Exception("Migration class not found: {$className}");
        }
        
        return new $className();
    }
    
    private function getSeeds() {
        $files = glob($this->seedsPath . '*.php');
        $seeds = [];
        
        foreach ($files as $file) {
            $seeds[] = basename($file, '.php');
        }
        
        sort($seeds);
        return $seeds;
    }
    
    private function runSeed($seed, $environment) {
        $this->log("Running seed: {$seed} for {$environment}");
        
        $filepath = $this->seedsPath . $seed . '.php';
        
        if (!file_exists($filepath)) {
            throw new Exception("Seed file not found: {$filepath}");
        }
        
        require_once $filepath;
        
        // Extract class name from seed filename
        $className = ucfirst($seed) . 'Seeder';
        
        if (!class_exists($className)) {
            throw new Exception("Seed class not found: {$className}");
        }
        
        $seeder = new $className();
        $seeder->run($environment);
        
        $this->log("Seed completed: {$seed}");
    }
    
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}";
        
        echo $logMessage . "\n";
        
        // Log to file
        $logPath = __DIR__ . '/migration.log';
        file_put_contents($logPath, $logMessage . "\n", FILE_APPEND | LOCK_EX);
    }
}

// Run CLI if called directly
if (php_sapi_name() === 'cli') {
    try {
        $migrationManager = new MigrationManagerCLI();
        $migrationManager->handleCommand($argv);
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}