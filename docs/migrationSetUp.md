#Migration system that works seamlessly in both development (with CLI) and production (web-based) environments.
##Web-Based Migration Interface:

<?php
// File: public/admin/migrate.php
// Web-based migration interface for shared hosting environments

session_start();

// Security: Only allow access with proper authentication
if (!isset($_SESSION['admin_authenticated']) || $_SESSION['admin_authenticated'] !== true) {
    // Simple authentication for migration access
    if (isset($_POST['admin_password'])) {
        $adminPassword = 'MigrateAdmin2025!'; // Change this to a secure password
        if ($_POST['admin_password'] === $adminPassword) {
            $_SESSION['admin_authenticated'] = true;
        } else {
            $error = 'Invalid password';
        }
    }
    
    if (!isset($_SESSION['admin_authenticated'])) {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Migration Access</title>
            <style>
                body { font-family: Arial, sans-serif; max-width: 400px; margin: 100px auto; padding: 20px; }
                .form-group { margin-bottom: 15px; }
                input[type="password"] { width: 100%; padding: 10px; }
                button { background: #007cba; color: white; padding: 10px 20px; border: none; cursor: pointer; }
                .error { color: red; margin-bottom: 15px; }
            </style>
        </head>
        <body>
            <h2>Migration System Access</h2>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Admin Password:</label>
                    <input type="password" name="admin_password" required>
                </div>
                <button type="submit">Access Migration System</button>
            </form>
        </body>
        </html>
        <?php
        exit;
    }
}

// Include required files
require_once '../../app/core/Database.php';
require_once '../../app/core/MigrationManager.php';

use App\Core\MigrationManager;

$migrationManager = new MigrationManager();
$action = $_GET['action'] ?? 'status';
$message = '';
$error = '';

// Handle POST actions
if ($_POST) {
    try {
        switch ($_POST['action']) {
            case 'migrate':
                ob_start();
                $migrationManager->migrate();
                $output = ob_get_clean();
                $message = "Migrations completed successfully.\n" . $output;
                break;
                
            case 'rollback':
                ob_start();
                $migrationManager->rollback();
                $output = ob_get_clean();
                $message = "Rollback completed successfully.\n" . $output;
                break;
                
            case 'seed_production':
                ob_start();
                $migrationManager->seed('production');
                $output = ob_get_clean();
                $message = "Production seeding completed successfully.\n" . $output;
                break;
                
            case 'seed_development':
                ob_start();
                $migrationManager->seed('development');
                $output = ob_get_clean();
                $message = "Development seeding completed successfully.\n" . $output;
                break;
                
            case 'fresh':
                ob_start();
                // Rollback all migrations
                $status = $migrationManager->status();
                while ($status['total_executed'] > 0) {
                    $migrationManager->rollback();
                    $status = $migrationManager->status();
                }
                // Run migrations
                $migrationManager->migrate();
                // Run seeds
                $migrationManager->seed('production');
                $output = ob_get_clean();
                $message = "Fresh installation completed successfully.\n" . $output;
                break;
                
            case 'logout':
                session_destroy();
                header('Location: migrate.php');
                exit;
                break;
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get current status
$status = $migrationManager->status();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migration System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .btn {
            background: #007cba;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
            margin-bottom: 10px;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background: #005a87;
        }
        
        .btn-danger {
            background: #dc3545;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #333;
        }
        
        .btn-warning:hover {
            background: #e0a800;
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .status-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .migration-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
        }
        
        .migration-item {
            padding: 8px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        
        .migration-item:last-child {
            border-bottom: none;
        }
        
        .status-icon {
            margin-right: 10px;
            font-weight: bold;
        }
        
        .executed {
            color: #28a745;
        }
        
        .pending {
            color: #ffc107;
        }
        
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .log-output {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            white-space: pre-wrap;
            max-height: 200px;
            overflow-y: auto;
        }
        
        .warning-box {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .action-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        
        .action-card h4 {
            margin-bottom: 10px;
            color: #333;
        }
        
        .action-card p {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 15px;
        }
        
        @media (max-width: 768px) {
            .status-grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                text-align: center;
            }
            
            .header h1 {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🗂️ Database Migration System</h1>
            <form method="POST" style="margin: 0;">
                <input type="hidden" name="action" value="logout">
                <button type="submit" class="btn btn-danger">Logout</button>
            </form>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success">
                <strong>Success!</strong><br>
                <div class="log-output"><?php echo htmlspecialchars($message); ?></div>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <strong>Error!</strong><br>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="warning-box">
            <strong>⚠️ Production Environment Notice:</strong><br>
            This is a web-based migration interface designed for shared hosting environments. 
            Always backup your database before running migrations in production!
        </div>
        
        <div class="card">
            <h3>📊 Migration Status</h3>
            <div class="status-grid">
                <div>
                    <h4>Summary</h4>
                    <p><strong>Executed:</strong> <?php echo $status['total_executed']; ?> migrations</p>
                    <p><strong>Pending:</strong> <?php echo $status['total_pending']; ?> migrations</p>
                </div>
                <div>
                    <h4>Quick Stats</h4>
                    <p><strong>Database:</strong> Connected ✅</p>
                    <p><strong>Environment:</strong> <?php echo $_SERVER['HTTP_HOST']; ?></p>
                    <p><strong>Last Check:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
                </div>
            </div>
        </div>
        
        <div class="status-grid">
            <div class="card">
                <h4>✅ Executed Migrations</h4>
                <div class="migration-list">
                    <?php if (empty($status['executed'])): ?>
                        <p>No migrations have been executed yet.</p>
                    <?php else: ?>
                        <?php foreach ($status['executed'] as $migration): ?>
                            <div class="migration-item">
                                <span class="status-icon executed">✓</span>
                                <?php echo htmlspecialchars($migration); ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <h4>⏳ Pending Migrations</h4>
                <div class="migration-list">
                    <?php if (empty($status['pending'])): ?>
                        <p>No pending migrations. System is up to date!</p>
                    <?php else: ?>
                        <?php foreach ($status['pending'] as $migration): ?>
                            <div class="migration-item">
                                <span class="status-icon pending">•</span>
                                <?php echo htmlspecialchars($migration); ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h3>🚀 Actions</h3>
            <div class="actions-grid">
                <div class="action-card">
                    <h4>Run Migrations</h4>
                    <p>Execute all pending database migrations</p>
                    <form method="POST">
                        <input type="hidden" name="action" value="migrate">
                        <button type="submit" class="btn btn-success" 
                                <?php echo empty($status['pending']) ? 'disabled' : ''; ?>>
                            Run Migrations
                        </button>
                    </form>
                </div>
                
                <div class="action-card">
                    <h4>Rollback</h4>
                    <p>Rollback the last batch of migrations</p>
                    <form method="POST">
                        <input type="hidden" name="action" value="rollback">
                        <button type="submit" class="btn btn-warning"
                                onclick="return confirm('Are you sure you want to rollback migrations? This cannot be undone!')"
                                <?php echo empty($status['executed']) ? 'disabled' : ''; ?>>
                            Rollback
                        </button>
                    </form>
                </div>
                
                <div class="action-card">
                    <h4>Seed Production</h4>
                    <p>Run production-safe data seeders</p>
                    <form method="POST">
                        <input type="hidden" name="action" value="seed_production">
                        <button type="submit" class="btn">Seed Production</button>
                    </form>
                </div>
                
                <div class="action-card">
                    <h4>Seed Development</h4>
                    <p>Run all seeders including sample data</p>
                    <form method="POST">
                        <input type="hidden" name="action" value="seed_development">
                        <button type="submit" class="btn">Seed Development</button>
                    </form>
                </div>
                
                <div class="action-card">
                    <h4>Fresh Install</h4>
                    <p>Rollback all, migrate, and seed (DESTRUCTIVE)</p>
                    <form method="POST">
                        <input type="hidden" name="action" value="fresh">
                        <button type="submit" class="btn btn-danger"
                                onclick="return confirm('⚠️ WARNING: This will DELETE ALL DATA and reinstall everything! Are you absolutely sure?')">
                            Fresh Install
                        </button>
                    </form>
                </div>
                
                <div class="action-card">
                    <h4>Refresh Status</h4>
                    <p>Check current migration status</p>
                    <a href="migrate.php" class="btn">Refresh</a>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h3>📖 Documentation</h3>
            <ul>
                <li><strong>Migrations:</strong> Database schema changes that can be run and rolled back</li>
                <li><strong>Seeds:</strong> Data population scripts for initial setup</li>
                <li><strong>Production Seeds:</strong> Safe data for production environments</li>
                <li><strong>Development Seeds:</strong> Sample data for testing and development</li>
                <li><strong>Fresh Install:</strong> Complete reset - use with extreme caution!</li>
            </ul>
        </div>
        
        <div class="card">
            <h3>🔧 File Upload Interface</h3>
            <p>To add new migrations or seeders in a shared hosting environment:</p>
            <ol>
                <li>Create your migration/seeder files locally</li>
                <li>Upload them to <code>database/migrations/</code> or <code>database/seeds/</code></li>
                <li>Return to this interface and run the migrations</li>
            </ol>
        </div>
    </div>
</body>
</html>

#MigrationManager for shared hosting:
<?php

namespace App\Core;

use PDO;
use PDOException;
use Exception;

class MigrationManager {
    private $db;
    private $migrationsPath;
    private $seedsPath;
    
    public function __construct() {
        $this->db = Database::connect();
        $this->migrationsPath = dirname(__DIR__, 2) . '/database/migrations/';
        $this->seedsPath = dirname(__DIR__, 2) . '/database/seeds/';
        
        // Ensure directories exist
        if (!is_dir($this->migrationsPath)) {
            mkdir($this->migrationsPath, 0755, true);
        }
        if (!is_dir($this->seedsPath)) {
            mkdir($this->seedsPath, 0755, true);
        }
        
        $this->createMigrationsTable();
    }
    
    /**
     * Create migrations tracking table
     */
    private function createMigrationsTable() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                batch INT NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $this->db->exec($sql);
        } catch (PDOException $e) {
            $this->log("Error creating migrations table: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Run all pending migrations
     */
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
            
            $this->log("Migration process completed successfully.");
            return true;
            
        } catch (Exception $e) {
            $this->log("Migration failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Rollback the last batch of migrations
     */
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
    
    /**
     * Get pending migrations
     */
    private function getPendingMigrations() {
        $allMigrations = $this->getAllMigrationFiles();
        $executedMigrations = $this->getExecutedMigrations();
        
        return array_diff($allMigrations, $executedMigrations);
    }
    
    /**
     * Get all migration files
     */
    private function getAllMigrationFiles() {
        $files = glob($this->migrationsPath . '*.php');
        return array_map('basename', $files, array_fill(0, count($files), '.php'));
    }
    
    /**
     * Get executed migrations
     */
    private function getExecutedMigrations() {
        $stmt = $this->db->query("SELECT migration FROM migrations ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Run a single migration
     */
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
            $this->db->rollBack();
            $this->log("Migration failed: {$migration} - " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Rollback a single migration
     */
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
            $this->log("Rollback completed: {$migration}");
            
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->log("Rollback failed: {$migration} - " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Load migration class
     */
    private function loadMigrationClass($migration) {
        $filePath = $this->migrationsPath . $migration . '.php';
        
        if (!file_exists($filePath)) {
            throw new Exception("Migration file not found: {$filePath}");
        }
        
        require_once $filePath;
        
        // Extract class name from filename
        $className = $this->getClassNameFromFile($migration);
        
        if (!class_exists($className)) {
            throw new Exception("Migration class not found: {$className}");
        }
        
        return new $className($this->db);
    }
    
    /**
     * Extract class name from migration filename
     */
    private function getClassNameFromFile($filename) {
        // Remove timestamp prefix and convert to PascalCase
        $parts = explode('_', preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $filename));
        return implode('', array_map('ucfirst', $parts));
    }
    
    /**
     * Get next batch number
     */
    private function getNextBatchNumber() {
        $stmt = $this->db->query("SELECT MAX(batch) FROM migrations");
        $lastBatch = $stmt->fetchColumn();
        return $lastBatch ? $lastBatch + 1 : 1;
    }
    
    /**
     * Get last batch number
     */
    private function getLastBatch() {
        $stmt = $this->db->query("SELECT MAX(batch) FROM migrations");
        return $stmt->fetchColumn();
    }
    
    /**
     * Get migrations in specific batch
     */
    private function getMigrationsInBatch($batch) {
        $stmt = $this->db->prepare("SELECT migration FROM migrations WHERE batch = ? ORDER BY id");
        $stmt->execute([$batch]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Run seeders
     */
    public function seed($environment = 'development') {
        try {
            $this->log("Starting seeding process for environment: {$environment}");
            
            $seeders = $this->getSeederFiles($environment);
            
            foreach ($seeders as $seeder) {
                $this->runSeeder($seeder);
            }
            
            $this->log("Seeding completed successfully.");
            return true;
            
        } catch (Exception $e) {
            $this->log("Seeding failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get seeder files for environment
     */
    private function getSeederFiles($environment) {
        $pattern = $this->seedsPath . "*.php";
        $files = glob($pattern);
        
        $seeders = [];
        foreach ($files as $file) {
            $filename = basename($file, '.php');
            
            // Include all seeders for development, only production-safe for production
            if ($environment === 'development' || strpos($filename, 'production') !== false) {
                $seeders[] = $filename;
            }
        }
        
        // Sort to ensure consistent order
        sort($seeders);
        return $seeders;
    }
    
    /**
     * Run a single seeder
     */
    private function runSeeder($seeder) {
        $this->log("Running seeder: {$seeder}");
        
        try {
            $this->db->beginTransaction();
            
            $seederClass = $this->loadSeederClass($seeder);
            $seederClass->run();
            
            $this->db->commit();
            $this->log("Seeder completed: {$seeder}");
            
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->log("Seeder failed: {$seeder} - " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Load seeder class
     */
    private function loadSeederClass($seeder) {
        $filePath = $this->seedsPath . $seeder . '.php';
        
        if (!file_exists($filePath)) {
            throw new Exception("Seeder file not found: {$filePath}");
        }
        
        require_once $filePath;
        
        $className = $this->getClassNameFromFile($seeder);
        
        if (!class_exists($className)) {
            throw new Exception("Seeder class not found: {$className}");
        }
        
        return new $className($this->db);
    }
    
    /**
     * Create a new migration file
     */
    public function createMigration($name) {
        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_{$name}.php";
        $className = $this->getClassNameFromFile($name);
        
        $template = $this->getMigrationTemplate($className);
        
        $filePath = $this->migrationsPath . $filename;
        file_put_contents($filePath, $template);
        
        $this->log("Migration created: {$filename}");
        return $filePath;
    }
    
    /**
     * Create a new seeder file
     */
    public function createSeeder($name) {
        $filename = "{$name}.php";
        $className = $this->getClassNameFromFile($name);
        
        $template = $this->getSeederTemplate($className);
        
        $filePath = $this->seedsPath . $filename;
        file_put_contents($filePath, $template);
        
        $this->log("Seeder created: {$filename}");
        return $filePath;
    }
    
    /**
     * Get migration template
     */
    private function getMigrationTemplate($className) {
        return "<?php

class {$className} {
    private \$db;
    
    public function __construct(\$db) {
        \$this->db = \$db;
    }
    
    /**
     * Run the migration
     */
    public function up() {
        // Add your migration code here
        \$sql = \"
            -- Your SQL here
        \";
        
        \$this->db->exec(\$sql);
    }
    
    /**
     * Reverse the migration
     */
    public function down() {
        // Add your rollback code here
        \$sql = \"
            -- Your rollback SQL here
        \";
        
        \$this->db->exec(\$sql);
    }
}
";
    }
    
    /**
     * Get seeder template
     */
    private function getSeederTemplate($className) {
        return "<?php

class {$className} {
    private \$db;
    
    public function __construct(\$db) {
        \$this->db = \$db;
    }
    
    /**
     * Run the seeder
     */
    public function run() {
        // Add your seeding code here
        \$data = [
            // Your data here
        ];
        
        foreach (\$data as \$row) {
            \$stmt = \$this->db->prepare(\"INSERT INTO table_name (column1, column2) VALUES (?, ?)\");
            \$stmt->execute([\$row['column1'], \$row['column2']]);
        }
    }
}
";
    }
    
    /**
     * Log message
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
        
        // Log to file (ensure directory exists and is writable)
        $logFile = dirname(__DIR__, 2) . '/database/migration.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Try to write to log file, but don't fail if we can't
        try {
            file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            // Fallback: output directly if we can't write to file
            if (php_sapi_name() === 'cli') {
                echo "Warning: Could not write to log file. " . $e->getMessage() . "\n";
            }
        }
        
        // Also output to console if running from CLI, or to output buffer for web
        if (php_sapi_name() === 'cli') {
            echo $logMessage;
        } else {
            // For web interface, output directly so it can be captured
            echo $logMessage;
            @ob_flush();
            @flush();
        }
    }
    
    /**
     * Check if running in shared hosting environment
     */
    public function isSharedHosting() {
        // Check for common shared hosting indicators
        $indicators = [
            !function_exists('exec'),
            !function_exists('shell_exec'),
            !function_exists('system'),
            php_sapi_name() !== 'cli',
            isset($_SERVER['HTTP_HOST'])
        ];
        
        return count(array_filter($indicators)) >= 3;
    }
    
    /**
     * Get environment info for shared hosting
     */
    public function getEnvironmentInfo() {
        return [
            'php_version' => PHP_VERSION,
            'sapi' => php_sapi_name(),
            'is_cli' => php_sapi_name() === 'cli',
            'is_shared_hosting' => $this->isSharedHosting(),
            'max_execution_time' => ini_get('max_execution_time'),
            'memory_limit' => ini_get('memory_limit'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'
        ];
    }
    
    /**
     * Validate shared hosting environment
     */
    public function validateEnvironment() {
        $issues = [];
        
        // Check PHP version
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $issues[] = 'PHP 7.4 or higher required. Current: ' . PHP_VERSION;
        }
        
        // Check PDO extension
        if (!extension_loaded('pdo')) {
            $issues[] = 'PDO extension is required but not loaded';
        }
        
        if (!extension_loaded('pdo_mysql')) {
            $issues[] = 'PDO MySQL extension is required but not loaded';
        }
        
        // Check database connection
        try {
            $this->db->query('SELECT 1');
        } catch (Exception $e) {
            $issues[] = 'Database connection failed: ' . $e->getMessage();
        }
        
        // Check directory permissions
        $directories = [
            $this->migrationsPath,
            $this->seedsPath,
            dirname(__DIR__, 2) . '/database'
        ];
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                $issues[] = "Directory does not exist: {$dir}";
            } elseif (!is_readable($dir)) {
                $issues[] = "Directory is not readable: {$dir}";
            }
        }
        
        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'environment' => $this->getEnvironmentInfo()
        ];
    }
    
    /**
     * Get migration status
     */
    public function status() {
        $executed = $this->getExecutedMigrations();
        $pending = $this->getPendingMigrations();
        
        return [
            'executed' => $executed,
            'pending' => $pending,
            'total_executed' => count($executed),
            'total_pending' => count($pending)
        ];
    }
}

#file upload interface for shared hosting:
<?php
// File: public/admin/upload.php
// File upload interface for migrations and seeders in shared hosting

session_start();

// Security check
if (!isset($_SESSION['admin_authenticated']) || $_SESSION['admin_authenticated'] !== true) {
    header('Location: migrate.php');
    exit;
}

$message = '';
$error = '';

// Handle file uploads
if ($_POST && isset($_FILES['migration_file'])) {
    try {
        $uploadType = $_POST['upload_type'];
        $file = $_FILES['migration_file'];
        
        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error: ' . $file['error']);
        }
        
        if ($file['size'] > 1024 * 1024) { // 1MB limit
            throw new Exception('File too large. Maximum size is 1MB.');
        }
        
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        if ($fileExtension !== 'php') {
            throw new Exception('Only PHP files are allowed.');
        }
        
        // Validate PHP syntax
        $content = file_get_contents($file['tmp_name']);
        if (strpos($content, '<?php') !== 0) {
            throw new Exception('File must start with <?php tag.');
        }
        
        // Check for basic security issues
        $dangerousFunctions = ['exec', 'shell_exec', 'system', 'eval', 'file_get_contents'];
        foreach ($dangerousFunctions as $func) {
            if (strpos($content, $func) !== false) {
                throw new Exception("Dangerous function '{$func}' detected in file.");
            }
        }
        
        // Determine target directory
        $targetDir = $uploadType === 'migration' ? 
            '../../database/migrations/' : 
            '../../database/seeds/';
            
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        // Generate filename if needed
        if ($uploadType === 'migration' && !preg_match('/^\d{4}_\d{2}_\d{2}_\d{6}_/', $file['name'])) {
            $timestamp = date('Y_m_d_His');
            $baseName = pathinfo($file['name'], PATHINFO_FILENAME);
            $newFileName = $timestamp . '_' . $baseName . '.php';
        } else {
            $newFileName = $file['name'];
        }
        
        $targetPath = $targetDir . $newFileName;
        
        // Check if file already exists
        if (file_exists($targetPath)) {
            throw new Exception('File already exists: ' . $newFileName);
        }
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $message = "File uploaded successfully: {$newFileName}";
        } else {
            throw new Exception('Failed to move uploaded file.');
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get existing files
function getExistingFiles($directory) {
    $files = [];
    if (is_dir($directory)) {
        $fileList = glob($directory . '*.php');
        foreach ($fileList as $file) {
            $files[] = [
                'name' => basename($file),
                'size' => filesize($file),
                'modified' => filemtime($file)
            ];
        }
    }
    return $files;
}

$migrations = getExistingFiles('../../database/migrations/');
$seeders = getExistingFiles('../../database/seeds/');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Migration Files</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .btn {
            background: #007cba;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background: #005a87;
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .radio-group {
            display: flex;
            gap: 20px;
            margin-bottom: 10px;
        }
        
        .radio-group label {
            font-weight: normal;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .file-upload-area {
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 40px 20px;
            text-align: center;
            transition: border-color 0.3s;
        }
        
        .file-upload-area:hover {
            border-color: #007cba;
        }
        
        .file-upload-area.dragover {
            border-color: #007cba;
            background-color: #f0f8ff;
        }
        
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .file-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .file-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
        }
        
        .file-item h5 {
            margin-bottom: 10px;
            color: #007cba;
        }
        
        .file-meta {
            font-size: 0.9em;
            color: #666;
        }
        
        .code-preview {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            margin-top: 20px;
        }
        
        .warning-box {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .tab-container {
            margin-bottom: 20px;
        }
        
        .tab-buttons {
            display: flex;
            border-bottom: 1px solid #ddd;
        }
        
        .tab-button {
            padding: 10px 20px;
            background: none;
            border: none;
            cursor: pointer;
            border-bottom: 2px solid transparent;
        }
        
        .tab-button.active {
            border-bottom-color: #007cba;
            color: #007cba;
        }
        
        .tab-content {
            display: none;
            padding-top: 20px;
        }
        
        .tab-content.active {
            display: block;
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
            }
            
            .header h1 {
                margin-bottom: 10px;
            }
            
            .file-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📁 Upload Migration Files</h1>
            <div>
                <a href="migrate.php" class="btn btn-secondary">← Back to Migration System</a>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success">
                <strong>Success!</strong> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <strong>Error!</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="warning-box">
            <strong>⚠️ Shared Hosting Upload Interface</strong><br>
            This interface allows you to upload migration and seeder files to your shared hosting environment. 
            Only upload files you trust, as they will have access to your database.
        </div>
        
        <div class="card">
            <h3>📤 Upload New File</h3>
            <form method="POST" enctype="multipart/form-data" id="uploadForm">
                <div class="form-group">
                    <label>File Type:</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="upload_type" value="migration" checked>
                            Migration
                        </label>
                        <label>
                            <input type="radio" name="upload_type" value="seeder">
                            Seeder
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Select File (PHP only, max 1MB):</label>
                    <div class="file-upload-area" id="fileUploadArea">
                        <input type="file" name="migration_file" id="migrationFile" 
                               accept=".php" required class="form-control">
                        <p>Click to select a file or drag and drop here</p>
                        <small>Supported: .php files up to 1MB</small>
                    </div>
                </div>
                
                <button type="submit" class="btn">Upload File</button>
            </form>
        </div>
        
        <div class="tab-container">
            <div class="tab-buttons">
                <button class="tab-button active" onclick="showTab('migrations')">
                    📋 Migrations (<?php echo count($migrations); ?>)
                </button>
                <button class="tab-button" onclick="showTab('seeders')">
                    🌱 Seeders (<?php echo count($seeders); ?>)
                </button>
            </div>
            
            <div id="migrations" class="tab-content active">
                <div class="card">
                    <h3>Existing Migration Files</h3>
                    <?php if (empty($migrations)): ?>
                        <p>No migration files found.</p>
                    <?php else: ?>
                        <div class="file-list">
                            <?php foreach ($migrations as $file): ?>
                                <div class="file-item">
                                    <h5><?php echo htmlspecialchars($file['name']); ?></h5>
                                    <div class="file-meta">
                                        Size: <?php echo number_format($file['size']); ?> bytes<br>
                                        Modified: <?php echo date('Y-m-d H:i:s', $file['modified']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div id="seeders" class="tab-content">
                <div class="card">
                    <h3>Existing Seeder Files</h3>
                    <?php if (empty($seeders)): ?>
                        <p>No seeder files found.</p>
                    <?php else: ?>
                        <div class="file-list">
                            <?php foreach ($seeders as $file): ?>
                                <div class="file-item">
                                    <h5><?php echo htmlspecialchars($file['name']); ?></h5>
                                    <div class="file-meta">
                                        Size: <?php echo number_format($file['size']); ?> bytes<br>
                                        Modified: <?php echo date('Y-m-d H:i:s', $file['modified']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h3>📝 Migration Template</h3>
            <p>Copy this template to create new migration files:</p>
            <div class="code-preview">
&lt;?php

class YourMigrationName {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Run the migration
     */
    public function up() {
        $sql = "
            CREATE TABLE your_table_name (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ";
        
        $this->db->exec($sql);
    }
    
    /**
     * Reverse the migration
     */
    public function down() {
        $sql = "DROP TABLE IF EXISTS your_table_name";
        $this->db->exec($sql);
    }
}
            </div>
        </div>
        
        <div class="card">
            <h3>🌱 Seeder Template</h3>
            <p>Copy this template to create new seeder files:</p>
            <div class="code-preview">
&lt;?php

class YourSeederName {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Run the seeder
     */
    public function run() {
        $data = [
            ['name' => 'Sample 1', 'value' => 'Value 1'],
            ['name' => 'Sample 2', 'value' => 'Value 2'],
        ];
        
        foreach ($data as $row) {
            // Check if data already exists
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM your_table WHERE name = ?");
            $stmt->execute([$row['name']]);
            
            if ($stmt->fetchColumn() == 0) {
                $stmt = $this->db->prepare("INSERT INTO your_table (name, value) VALUES (?, ?)");
                $stmt->execute([$row['name'], $row['value']]);
                echo "Created: {$row['name']}\n";
            } else {
                echo "Already exists: {$row['name']}\n";
            }
        }
    }
}
            </div>
        </div>
        
        <div class="card">
            <h3>💡 Best Practices for Shared Hosting</h3>
            <ul>
                <li><strong>Test Locally First:</strong> Always test your migrations in a local development environment</li>
                <li><strong>Backup Database:</strong> Create a database backup before running migrations in production</li>
                <li><strong>Small Batches:</strong> Upload and run migrations in small batches to avoid timeouts</li>
                <li><strong>Check File Permissions:</strong> Ensure uploaded files have correct permissions</li>
                <li><strong>Monitor Execution Time:</strong> Shared hosting has execution time limits</li>
                <li><strong>Use Transactions:</strong> The system automatically wraps operations in transactions</li>
            </ul>
        </div>
    </div>
    
    <script>
        // Tab switching
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
            });
            
            // Show selected tab and mark button as active
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
        
        // File upload drag and drop
        const fileUploadArea = document.getElementById('fileUploadArea');
        const fileInput = document.getElementById('migrationFile');
        
        fileUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUploadArea.classList.add('dragover');
        });
        
        fileUploadArea.addEventListener('dragleave', () => {
            fileUploadArea.classList.remove('dragover');
        });
        
        fileUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                updateFileInfo(files[0]);
            }
        });
        
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                updateFileInfo(e.target.files[0]);
            }
        });
        
        function updateFileInfo(file) {
            const info = document.querySelector('.file-upload-area p');
            info.textContent = `Selected: ${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
        }
        
        // Form validation
        document.getElementById('uploadForm').addEventListener('submit', (e) => {
            const fileInput = document.getElementById('migrationFile');
            
            if (!fileInput.files.length) {
                alert('Please select a file to upload.');
                e.preventDefault();
                return;
            }
            
            const file = fileInput.files[0];
            
            // Check file extension
            if (!file.name.toLowerCase().endsWith('.php')) {
                alert('Please select a PHP file.');
                e.preventDefault();
                return;
            }
            
            // Check file size (1MB = 1048576 bytes)
            if (file.size > 1048576) {
                alert('File is too large. Maximum size is 1MB.');
                e.preventDefault();
                return;
            }
            
            // Show loading state
            const submitButton = e.target.querySelector('button[type="submit"]');
            submitButton.textContent = 'Uploading...';
            submitButton.disabled = true;
        });
    </script>
</body>
</html>

#setup script for shared hosting:
<?php
// File: public/admin/setup.php
// Web-based setup for shared hosting environments

session_start();

// Security check
$setupPassword = 'SetupVuyani2025!'; // Change this to a secure password
$isAuthenticated = false;

if (isset($_POST['setup_password'])) {
    if ($_POST['setup_password'] === $setupPassword) {
        $_SESSION['setup_authenticated'] = true;
        $isAuthenticated = true;
    } else {
        $setupError = 'Invalid setup password';
    }
} elseif (isset($_SESSION['setup_authenticated']) && $_SESSION['setup_authenticated'] === true) {
    $isAuthenticated = true;
}

if (!$isAuthenticated) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Portfolio Setup</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                max-width: 500px; 
                margin: 100px auto; 
                padding: 20px;
                background: #f5f5f5;
            }
            .setup-card {
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            .form-group { margin-bottom: 20px; }
            input[type="password"] { 
                width: 100%; 
                padding: 12px; 
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 16px;
            }
            button { 
                background: #007cba; 
                color: white; 
                padding: 12px 25px; 
                border: none; 
                border-radius: 4px;
                cursor: pointer; 
                font-size: 16px;
                width: 100%;
            }
            button:hover { background: #005a87; }
            .error { 
                color: #dc3545; 
                margin-bottom: 15px; 
                padding: 10px;
                background: #f8d7da;
                border-radius: 4px;
            }
            h2 { color: #333; margin-bottom: 20px; text-align: center; }
            .info { 
                background: #d1ecf1; 
                color: #0c5460; 
                padding: 15px; 
                border-radius: 4px; 
                margin-bottom: 20px; 
            }
        </style>
    </head>
    <body>
        <div class="setup-card">
            <h2>🚀 Portfolio Website Setup</h2>
            
            <div class="info">
                <strong>Initial Setup Required</strong><br>
                This will create your database structure and initial configuration for your portfolio website.
            </div>
            
            <?php if (isset($setupError)): ?>
                <div class="error"><?php echo $setupError; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Setup Password:</label>
                    <input type="password" name="setup_password" placeholder="Enter setup password" required>
                </div>
                <button type="submit">Begin Setup</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Include required files
require_once '../../app/core/Database.php';
require_once '../../app/core/MigrationManager.php';

use App\Core\MigrationManager;

$step = $_GET['step'] ?? 1;
$message = '';
$error = '';
$setupComplete = false;

// Handle setup steps
if ($_POST && isset($_POST['action'])) {
    try {
        switch ($_POST['action']) {
            case 'create_structure':
                $result = createDirectoryStructure();
                $message = $result['message'];
                break;
                
            case 'create_files':
                $result = createSystemFiles();
                $message = $result['message'];
                break;
                
            case 'test_database':
                $result = testDatabaseConnection();
                $message = $result['message'];
                break;
                
            case 'run_migrations':
                $migrationManager = new MigrationManager();
                ob_start();
                $migrationManager->migrate();
                $output = ob_get_clean();
                $message = "Database migrations completed successfully!\n" . $output;
                break;
                
            case 'run_seeds':
                $migrationManager = new MigrationManager();
                ob_start();
                $migrationManager->seed('production');
                $output = ob_get_clean();
                $message = "Production seeding completed successfully!\n" . $output;
                break;
                
            case 'complete_setup':
                // Mark setup as complete
                $_SESSION['setup_complete'] = true;
                $setupComplete = true;
                $message = "Setup completed successfully! Your portfolio website is ready to use.";
                break;
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

function createDirectoryStructure() {
    $directories = [
        '../../database',
        '../../database/migrations',
        '../../database/seeds',
        '../../public/images/blog',
        '../../public/images/projects', 
        '../../public/resources',
        '../../app/core'
    ];
    
    $created = [];
    $existing = [];
    
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            if (mkdir($dir, 0755, true)) {
                $created[] = $dir;
            }
        } else {
            $existing[] = $dir;
        }
    }
    
    $message = "Directory structure created!\n";
    if (!empty($created)) {
        $message .= "Created: " . implode(', ', $created) . "\n";
    }
    if (!empty($existing)) {
        $message .= "Already existed: " . implode(', ', $existing) . "\n";
    }
    
    return ['success' => true, 'message' => $message];
}

function createSystemFiles() {
    $files = [
        '../../database/migrations/2025_01_24_120000_create_users_table.php' => getUsersMigration(),
        '../../database/migrations/2025_01_24_120001_create_project_categories_table.php' => getProjectCategoriesMigration(),
        '../../database/migrations/2025_01_24_120002_create_projects_table.php' => getProjectsMigration(),
        '../../database/migrations/2025_01_24_120003_create_blog_categories_table.php' => getBlogCategoriesMigration(),
        '../../database/migrations/2025_01_24_120004_create_blog_posts_table.php' => getBlogPostsMigration(),
        '../../database/migrations/2025_01_24_120005_create_resources_table.php' => getResourcesMigration(),
        '../../database/migrations/2025_01_24_120006_create_contact_submissions_table.php' => getContactSubmissionsMigration(),
        '../../database/seeds/01_production_users_seeder.php' => getUsersSeeder(),
        '../../database/seeds/02_production_project_categories_seeder.php' => getProjectCategoriesSeeder(),
        '../../database/seeds/03_production_blog_categories_seeder.php' => getBlogCategoriesSeeder(),
    ];
    
    $created = [];
    $existing = [];
    
    foreach ($files as $filePath => $content) {
        if (!file_exists($filePath)) {
            file_put_contents($filePath, $content);
            $created[] = basename($filePath);
        } else {
            $existing[] = basename($filePath);
        }
    }
    
    $message = "System files created!\n";
    if (!empty($created)) {
        $message .= "Created: " . implode(', ', $created) . "\n";
    }
    if (!empty($existing)) {
        $message .= "Already existed: " . implode(', ', $existing) . "\n";
    }
    
    return ['success' => true, 'message' => $message];
}

function testDatabaseConnection() {
    try {
        require_once '../../app/core/Database.php';
        $db = App\Core\Database::connect();
        $stmt = $db->query('SELECT 1');
        $result = $stmt->fetch();
        
        if ($result) {
            return ['success' => true, 'message' => 'Database connection successful! ✅'];
        } else {
            throw new Exception('Database query failed');
        }
    } catch (Exception $e) {
        throw new Exception('Database connection failed: ' . $e->getMessage());
    }
}

// Migration content functions (abbreviated for space)
function getUsersMigration() {
    return '<?php

class CreateUsersTable {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function up() {
        $sql = "
            CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                first_name VARCHAR(50),
                last_name VARCHAR(50),
                role ENUM(\'user\', \'admin\') DEFAULT \'user\',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ";
        
        $this->db->exec($sql);
    }
    
    public function down() {
        $sql = "DROP TABLE IF EXISTS users";
        $this->db->exec($sql);
    }
}
';
}

function getProjectCategoriesMigration() {
    return '<?php

class CreateProjectCategoriesTable {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function up() {
        $sql = "
            CREATE TABLE project_categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(50) NOT NULL,
                slug VARCHAR(50) NOT NULL UNIQUE,
                parent_id INT NULL,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (parent_id) REFERENCES project_categories(id) ON DELETE SET NULL
            )
        ";
        
        $this->db->exec($sql);
    }
    
    public function down() {
        $sql = "DROP TABLE IF EXISTS project_categories";
        $this->db->exec($sql);
    }
}
';
}

function getProjectsMigration() {
    return '<?php

class CreateProjectsTable {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function up() {
        $sql = "
            CREATE TABLE projects (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(100) NOT NULL,
                slug VARCHAR(100) NOT NULL UNIQUE,
                description TEXT NOT NULL,
                content LONGTEXT,
                category_id INT NOT NULL,
                featured_image VARCHAR(255),
                client VARCHAR(100),
                completion_date DATE,
                technologies VARCHAR(255),
                project_url VARCHAR(255),
                github_url VARCHAR(255),
                is_featured BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (category_id) REFERENCES project_categories(id)
            )
        ";
        
        $this->db->exec($sql);
    }
    
    public function down() {
        $sql = "DROP TABLE IF EXISTS projects";
        $this->db->exec($sql);
    }
}
';
}

function getBlogCategoriesMigration() {
    return '<?php

class CreateBlogCategoriesTable {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function up() {
        $sql = "
            CREATE TABLE blog_categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(50) NOT NULL,
                slug VARCHAR(50) NOT NULL UNIQUE,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ";
        
        $this->db->exec($sql);
    }
    
    public function down() {
        $sql = "DROP TABLE IF EXISTS blog_categories";
        $this->db->exec($sql);
    }
}
';
}

function getBlogPostsMigration() {
    return '<?php

class CreateBlogPostsTable {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function up() {
        $sql = "
            CREATE TABLE blog_posts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(200) NOT NULL,
                slug VARCHAR(200) NOT NULL UNIQUE,
                excerpt TEXT,
                content LONGTEXT NOT NULL,
                featured_image VARCHAR(255),
                category_id INT,
                author_id INT NOT NULL,
                status ENUM(\'draft\', \'published\') DEFAULT \'draft\',
                is_featured BOOLEAN DEFAULT FALSE,
                views INT DEFAULT 0,
                published_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE SET NULL,
                FOREIGN KEY (author_id) REFERENCES users(id)
            )
        ";
        
        $this->db->exec($sql);
    }
    
    public function down() {
        $sql = "DROP TABLE IF EXISTS blog_posts";
        $this->db->exec($sql);
    }
}
';
}

function getResourcesMigration() {
    return '<?php

class CreateResourcesTable {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function up() {
        $sql = "
            CREATE TABLE resources (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(200) NOT NULL,
                slug VARCHAR(200) NOT NULL UNIQUE,
                description TEXT,
                file_path VARCHAR(255) NOT NULL,
                file_size INT,
                file_type VARCHAR(50),
                thumbnail VARCHAR(255),
                download_count INT DEFAULT 0,
                requires_login BOOLEAN DEFAULT TRUE,
                author_id INT NOT NULL,
                status ENUM(\'draft\', \'published\') DEFAULT \'draft\',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (author_id) REFERENCES users(id)
            )
        ";
        
        $this->db->exec($sql);
    }
    
    public function down() {
        $sql = "DROP TABLE IF EXISTS resources";
        $this->db->exec($sql);
    }
}
';
}

function getContactSubmissionsMigration() {
    return '<?php

class CreateContactSubmissionsTable {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function up() {
        $sql = "
            CREATE TABLE contact_submissions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL,
                subject VARCHAR(200),
                message TEXT NOT NULL,
                ip_address VARCHAR(45),
                is_read BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ";
        
        $this->db->exec($sql);
    }
    
    public function down() {
        $sql = "DROP TABLE IF EXISTS contact_submissions";
        $this->db->exec($sql);
    }
}
';
}

function getUsersSeeder() {
    return '<?php

class ProductionUsersSeeder {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function run() {
        $adminData = [
            \'username\' => \'admin\',
            \'email\' => \'admin@vuyanim.com\',
            \'password\' => password_hash(\'change_this_password_123!\', PASSWORD_DEFAULT),
            \'first_name\' => \'Vuyani\',
            \'last_name\' => \'Magibisela\',
            \'role\' => \'admin\'
        ];
        
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$adminData[\'email\']]);
        
        if ($stmt->fetchColumn() == 0) {
            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, password, first_name, last_name, role) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $adminData[\'username\'],
                $adminData[\'email\'], 
                $adminData[\'password\'],
                $adminData[\'first_name\'],
                $adminData[\'last_name\'],
                $adminData[\'role\']
            ]);
            
            echo "Admin user created successfully.\n";
        } else {
            echo "Admin user already exists, skipping...\n";
        }
    }
}
';
}

function getProjectCategoriesSeeder() {
    return '<?php

class ProductionProjectCategoriesSeeder {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function run() {
        $categories = [
            [\'name\' => \'Digital Design\', \'slug\' => \'digital-design\', \'parent_id\' => null, \'description\' => \'Digital design projects\'],
            [\'name\' => \'Web Development\', \'slug\' => \'web-dev\', \'parent_id\' => null, \'description\' => \'Web development projects\'],
            [\'name\' => \'App Development\', \'slug\' => \'app-dev\', \'parent_id\' => null, \'description\' => \'Mobile apps\'],
            [\'name\' => \'Game Development\', \'slug\' => \'game-dev\', \'parent_id\' => null, \'description\' => \'Games\'],
            [\'name\' => \'Maker Projects\', \'slug\' => \'maker-projects\', \'parent_id\' => null, \'description\' => \'DIY projects\']
        ];
        
        foreach ($categories as $category) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM project_categories WHERE slug = ?");
            $stmt->execute([$category[\'slug\']]);
            
            if ($stmt->fetchColumn() == 0) {
                $stmt = $this->db->prepare("INSERT INTO project_categories (name, slug, parent_id, description) VALUES (?, ?, ?, ?)");
                $stmt->execute([$category[\'name\'], $category[\'slug\'], $category[\'parent_id\'], $category[\'description\']]);
                echo "Category created: {$category[\'name\']}\n";
            }
        }
    }
}
';
}

function getBlogCategoriesSeeder() {
    return '<?php

class ProductionBlogCategoriesSeeder {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function run() {
        $categories = [
            [\'name\' => \'Articles\', \'slug\' => \'articles\', \'description\' => \'General articles\'],
            [\'name\' => \'Tutorials\', \'slug\' => \'tutorials\', \'description\' => \'Step-by-step guides\'],
            [\'name\' => \'Resources\', \'slug\' => \'resources\', \'description\' => \'Downloadable resources\'],
            [\'name\' => \'News\', \'slug\' => \'news\', \'description\' => \'Latest updates\']
        ];
        
        foreach ($categories as $category) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM blog_categories WHERE slug = ?");
            $stmt->execute([$category[\'slug\']]);
            
            if ($stmt->fetchColumn() == 0) {
                $stmt = $this->db->prepare("INSERT INTO blog_categories (name, slug, description) VALUES (?, ?, ?)");
                $stmt->execute([$category[\'name\'], $category[\'slug\'], $category[\'description\']]);
                echo "Blog category created: {$category[\'name\']}\n";
            }
        }
    }
}
';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio Website Setup</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .setup-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, #f5b642 0%, #e5a632 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .content {
            padding: 30px;
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .step.active {
            background: #f5b642;
            color: white;
        }
        
        .step.completed {
            background: #28a745;
            color: white;
        }
        
        .step-connector {
            height: 2px;
            width: 50px;
            background: #e9ecef;
            margin-top: 19px;
        }
        
        .step-connector.completed {
            background: #28a745;
        }
        
        .btn {
            background: #f5b642;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
        }
        
        .btn:hover {
            background: #e5a632;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .log-output {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            white-space: pre-wrap;
            max-height: 300px;
            overflow-y: auto;
            margin-top: 15px;
        }
        
        .setup-steps {
            display: grid;
            gap: 20px;
        }
        
        .setup-step {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            transition: all 0.3s;
        }
        
        .setup-step:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .step-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 10px;
            color: #495057;
        }
        
        .step-description {
            color: #6c757d;
            margin-bottom: 15px;
        }
        
        .step-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .completion-card {
            text-align: center;
            padding: 40px;
        }
        
        .completion-card h2 {
            color: #28a745;
            margin-bottom: 20px;
            font-size: 2rem;
        }
        
        .completion-card .icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        
        .next-steps {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .next-steps h4 {
            margin-bottom: 15px;
            color: #495057;
        }
        
        .next-steps ul {
            list-style: none;
            padding: 0;
        }
        
        .next-steps li {
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .next-steps li:last-child {
            border-bottom: none;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .content {
                padding: 20px;
            }
            
            .step-indicator {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="setup-card">
            <div class="header">
                <h1>🚀 Portfolio Setup</h1>
                <p>Shared Hosting Environment</p>
            </div>
            
            <div class="content">
                <?php if ($message): ?>
                    <div class="alert alert-success">
                        <strong>Success!</strong><br>
                        <div class="log-output"><?php echo htmlspecialchars($message); ?></div>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <strong>Error!</strong><br>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($setupComplete): ?>
                    <div class="completion-card">
                        <div class="icon">🎉</div>
                        <h2>Setup Complete!</h2>
                        <p>Your portfolio website has been successfully set up and is ready to use.</p>
                        
                        <div class="next-steps">
                            <h4>🔑 Admin Login Details:</h4>
                            <ul>
                                <li><strong>Email:</strong> admin@vuyanim.com</li>
                                <li><strong>Password:</strong> change_this_password_123!</li>
                            </ul>
                        </div>
                        
                        <div class="next-steps">
                            <h4>📋 Next Steps:</h4>
                            <ul>
                                <li>1. Change the default admin password immediately</li>
                                <li>2. Access the migration system: <a href="migrate.php" target="_blank">migrate.php</a></li>
                                <li>3. Upload additional files: <a href="upload.php" target="_blank">upload.php</a></li>
                                <li>4. Add your project images to public/images/projects/</li>
                                <li>5. Add your blog images to public/images/blog/</li>
                            </ul>
                        </div>
                        
                        <div style="margin-top: 30px;">
                            <a href="../../" class="btn btn-success">Visit Your Website</a>
                            <a href="migrate.php" class="btn">Migration System</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="setup-steps">
                        <div class="setup-step">
                            <div class="step-title">📁 Step 1: Create Directory Structure</div>
                            <div class="step-description">Create necessary directories for migrations, seeds, and uploads.</div>
                            <form method="POST">
                                <input type="hidden" name="action" value="create_structure">
                                <button type="submit" class="btn">Create Directories</button>
                            </form>
                        </div>
                        
                        <div class="setup-step">
                            <div class="step-title">📄 Step 2: Create System Files</div>
                            <div class="step-description">Generate migration and seeder files for database structure.</div>
                            <form method="POST">
                                <input type="hidden" name="action" value="create_files">
                                <button type="submit" class="btn">Create Files</button>
                            </form>
                        </div>
                        
                        <div class="setup-step">
                            <div class="step-title">🔍 Step 3: Test Database Connection</div>
                            <div class="step-description">Verify that your database configuration is working correctly.</div>
                            <form method="POST">
                                <input type="hidden" name="action" value="test_database">
                                <button type="submit" class="btn">Test Database</button>
                            </form>
                        </div>
                        
                        <div class="setup-step">
                            <div class="step-title">🗂️ Step 4: Run Database Migrations</div>
                            <div class="step-description">Create the database tables required for your portfolio.</div>
                            <form method="POST">
                                <input type="hidden" name="action" value="run_migrations">
                                <button type="submit" class="btn">Run Migrations</button>
                            </form>
                        </div>
                        
                        <div class="setup-step">
                            <div class="step-title">🌱 Step 5: Seed Initial Data</div>
                            <div class="step-description">Add essential data like admin user and categories.</div>
                            <form method="POST">
                                <input type="hidden" name="action" value="run_seeds">
                                <button type="submit" class="btn">Run Seeds</button>
                            </form>
                        </div>
                        
                        <div class="setup-step">
                            <div class="step-title">✅ Step 6: Complete Setup</div>
                            <div class="step-description">Finalize the setup process and get your login details.</div>
                            <form method="POST">
                                <input type="hidden" name="action" value="complete_setup">
                                <button type="submit" class="btn btn-success">Complete Setup</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="alert alert-info" style="margin-top: 30px;">
                        <strong>💡 Setup Tips for Shared Hosting:</strong><br>
                        • Run each step individually to avoid timeout issues<br>
                        • Ensure your database credentials are correct in app/config/database.php<br>
                        • Make sure the database exists before running migrations<br>
                        • If you encounter timeout errors, try refreshing and continuing
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-scroll to show new messages
        const logOutputs = document.querySelectorAll('.log-output');
        logOutputs.forEach(output => {
            output.scrollTop = output.scrollHeight;
        });
        
        // Form submission feedback
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                const button = form.querySelector('button[type="submit"]');
                const originalText = button.textContent;
                button.textContent = 'Processing...';
                button.disabled = true;
                
                // Re-enable after 10 seconds in case of issues
                setTimeout(() => {
                    button.textContent = originalText;
                    button.disabled = false;
                }, 10000);
            });
        });
    </script>
</body>
</html>

#README specifically for shared hosting:
# Migration & Seeding System Documentation
## Shared Hosting Edition

## Overview

This migration and seeding system provides a robust, automated way to manage your database schema and populate it with initial data. **This version is specifically designed for shared hosting environments** where CLI access is not available.

## 🌐 **Shared Hosting Setup**

### **For Production (Shared Hosting):**

1. **Upload Files via FTP/File Manager:**
   ```
   /public/admin/setup.php       # Web-based setup interface
   /public/admin/migrate.php     # Web-based migration manager
   /public/admin/upload.php      # File upload interface
   /app/core/MigrationManager.php # Core migration system
   ```

2. **Access Setup Interface:**
   ```
   https://yoursite.com/admin/setup.php
   ```
   - Password: `SetupVuyani2025!` (change this in the file)
   - Follow the step-by-step web interface

3. **Database Configuration:**
   - Update `app/config/database.php` with your hosting provider's database details
   - Ensure your database exists before running setup

### **For Development (Local with CLI):**

```bash
# Quick start with automated setup
php setup.php

# Manual CLI commands
php migrate.php status
php migrate.php migrate
php migrate.php seed production
```

## 🔒 **Security for Shared Hosting**

### **Change Default Passwords:**
1. **Setup Password** in `setup.php`: Change `SetupVuyani2025!`
2. **Migration Access** in `migrate.php`: Change `MigrateAdmin2025!`
3. **Admin Account**: Change `change_this_password_123!` after first login

### **Secure the Admin Directory:**
Add `.htaccess` to `/public/admin/`:
```apache
# Restrict access to admin files
<Files "*.php">
    Order Allow,Deny
    Allow from YOUR_IP_ADDRESS
    # Or use password protection
</Files>
```

## 📋 **Step-by-Step Shared Hosting Guide**

### **Step 1: Upload Core Files**
Upload these files to your shared hosting:
- `app/core/MigrationManager.php`
- `public/admin/setup.php`
- `public/admin/migrate.php` 
- `public/admin/upload.php`

### **Step 2: Configure Database**
Update `app/config/database.php`:
```php
<?php
return [
    'host' => 'your_db_host',        // Usually localhost
    'dbname' => 'your_db_name',      // Your database name
    'user' => 'your_db_user',        // Your database username
    'password' => 'your_db_password', // Your database password
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
```

### **Step 3: Run Web Setup**
1. Navigate to: `https://yoursite.com/admin/setup.php`
2. Enter setup password: `SetupVuyani2025!`
3. Follow each step in the web interface:
   - Create directories
   - Create system files
   - Test database connection
   - Run migrations
   - Seed initial data
   - Complete setup

### **Step 4: Access Migration System**
Navigate to: `https://yoursite.com/admin/migrate.php`
- Password: `MigrateAdmin2025!`
- View migration status
- Run migrations
- Manage seeders

## 🌐 **Web Interface Features**

### **Migration Manager (`migrate.php`)**
- ✅ View migration status
- 🚀 Run pending migrations  
- ⏪ Rollback migrations
- 🌱 Run production/development seeds
- 🔄 Fresh installation (destructive)
- 📊 Real-time progress feedback

### **File Upload Interface (`upload.php`)**
- 📤 Upload new migration files
- 📤 Upload new seeder files
- 📋 View existing files
- 🔍 File validation and security checks
- 📝 Code templates and examples

### **Setup Interface (`setup.php`)**
- 📁 Automated directory creation
- 📄 System file generation
- 🔍 Database connection testing
- 🗂️ Migration execution
- 🌱 Initial data seeding

## 📝 **Creating New Migrations for Shared Hosting**

### **Method 1: Local Development + Upload**
1. Create migration locally using CLI: `php migrate.php create:migration add_new_feature`
2. Upload the generated file via FTP to `database/migrations/`
3. Run via web interface: `https://yoursite.com/admin/migrate.php`

### **Method 2: Direct Upload via Web Interface**
1. Create migration file locally with proper naming: `2025_01_24_143022_add_new_feature.php`
2. Use upload interface: `https://yoursite.com/admin/upload.php`
3. Select "Migration" type and upload file
4. Run via migration manager

### **Method 3: Direct File Creation**
1. Use your hosting file manager or FTP
2. Create file in `database/migrations/` with timestamp prefix
3. Use the templates provided in the upload interface

## ⚠️ **Shared Hosting Limitations & Solutions**

### **Execution Time Limits**
**Problem:** Shared hosting limits script execution time
**Solution:** 
- Run migrations in small batches
- Use the web interface's step-by-step process
- Break large data migrations into smaller files

### **Memory Limits**
**Problem:** Large data imports may exceed memory limits
**Solution:**
- Process data in smaller chunks
- Use database streaming for large datasets
- Monitor memory usage in migration logs

### **File Permissions**
**Problem:** Cannot create directories or write files
**Solution:**
- Pre-create directories via FTP/File Manager
- Set proper permissions (755 for directories, 644 for files)
- Use hosting control panel file manager

### **Database Timeouts**
**Problem:** Long-running migrations timeout
**Solution:**
- Use smaller migration files
- Split complex operations across multiple migrations
- Contact hosting provider for timeout adjustments

## 🔧 **Troubleshooting Shared Hosting Issues**

### **Cannot Access Admin Files**
```
Error: 403 Forbidden
Solution: Check file permissions and .htaccess restrictions
```

### **Database Connection Fails**
```
Error: Database connection failed
Solution: 
1. Verify database credentials in config/database.php
2. Ensure database exists
3. Check hosting provider's database settings
```

### **Migration Timeout**
```
Error: Maximum execution time exceeded
Solution:
1. Break large migrations into smaller ones
2. Refresh page and continue where left off
3. Contact hosting provider about time limits
```

### **File Upload Errors**
```
Error: File upload failed
Solution:
1. Check file permissions on upload directories
2. Verify file size limits
3. Ensure proper file naming conventions
```

## 📊 **Environment Detection**

The system automatically detects shared hosting environments:
- No CLI access
- Limited execution time
- Restricted file permissions
- Web-based interface required

## 🚀 **Production Deployment Checklist**

### **Before Deployment:**
- [ ] Test all migrations locally
- [ ] Backup production database
- [ ] Update database configuration
- [ ] Change all default passwords
- [ ] Test database connection

### **During Deployment:**
- [ ] Upload system files via FTP
- [ ] Run web-based setup
- [ ] Verify migration status
- [ ] Test website functionality
- [ ] Check error logs

### **After Deployment:**
- [ ] Change admin password
- [ ] Restrict admin directory access
- [ ] Remove setup files (optional)
- [ ] Monitor system logs
- [ ] Document any custom changes

## 🔗 **File Locations for Shared Hosting**

```
your-domain.com/
├── public/
│   ├── admin/
│   │   ├── setup.php      # Initial setup interface
│   │   ├── migrate.php    # Migration management
│   │   └── upload.php     # File upload interface
│   └── index.php
├── app/
│   ├── core/
│   │   └── MigrationManager.php
│   └── config/
│       └── database.php   # Update with hosting DB details
└── database/
    ├── migrations/        # Upload migration files here
    ├── seeds/            # Upload seeder files here
    └── migration.log     # Automatic logging
```

## 🆘 **Support & Backup**

### **Database Backup (Shared Hosting)**
Most shared hosting providers offer:
- Control panel database backup tools
- phpMyAdmin export functionality
- Automatic backup services

### **File Backup**
- Download entire `/database/` folder before major changes
- Keep local copies of all custom migrations
- Use version control for migration files

## 🔄 **Migration Workflow for Teams**

1. **Developer** creates migration locally
2. **Upload** via file manager or FTP
3. **Team Lead** reviews via web interface
4. **Deploy** using web migration manager
5. **Verify** database changes
6. **Document** in project changelog

This shared hosting approach ensures you can manage your database professionally even without CLI access!

## Default Admin Account

After running the setup, you'll have an admin account:

- **Email**: admin@vuyanim.com
- **Password**: change_this_password_123!

**⚠️ CRITICAL**: Change this password immediately after first login!

## Support

For shared hosting specific issues:

1. Check the migration log: `database/migration.log`
2. Verify database connection via web interface
3. Ensure proper file permissions (755/644)
4. Contact your hosting provider for server-specific limits
5. Use the web interfaces for all operations

## Directory Structure

```
portfolio-website/
├── database/
│   ├── migrations/          # Database schema migrations
│   ├── seeds/              # Data seeders
│   └── migration.log       # Migration log file
├── app/core/
│   └── MigrationManager.php # Core migration system
├── migrate.php             # CLI tool
└── setup.php              # Automated setup script
```

## Creating Migrations

### Generate a new migration:

```bash
php migrate.php create:migration create_new_table
```

This creates a file like: `2025_01_24_143022_create_new_table.php`

### Migration Structure:

```php
<?php

class CreateNewTable {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Run the migration
     */
    public function up() {
        $sql = "
            CREATE TABLE new_table (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ";
        
        $this->db->exec($sql);
    }
    
    /**
     * Reverse the migration
     */
    public function down() {
        $sql = "DROP TABLE IF EXISTS new_table";
        $this->db->exec($sql);
    }
}
```

## Creating Seeders

### Generate a new seeder:

```bash
php migrate.php create:seeder sample_data_seeder
```

### Seeder Structure:

```php
<?php

class SampleDataSeeder {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Run the seeder
     */
    public function run() {
        $data = [
            ['name' => 'Sample 1', 'value' => 'Value 1'],
            ['name' => 'Sample 2', 'value' => 'Value 2'],
        ];
        
        foreach ($data as $row) {
            $stmt = $this->db->prepare("INSERT INTO sample_table (name, value) VALUES (?, ?)");
            $stmt->execute([$row['name'], $row['value']]);
        }
    }
}
```

## Production vs Development Seeds

### Naming Convention:

- **Production seeds**: Prefix with `production_` (e.g., `01_production_users_seeder.php`)
- **Development seeds**: Prefix with `development_` (e.g., `04_development_sample_data_seeder.php`)

### Running Seeds:

```bash
# Production environment (only production-safe seeds)
php migrate.php seed production

# Development environment (all seeds)
php migrate.php seed development
```

## Error Handling & Logging

### Transaction Support
All migrations and seeds run within database transactions. If any part fails, the entire operation is rolled back.

### Logging
All operations are logged to `database/migration.log`:

```
[2025-01-24 14:30:22] Starting migration process...
[2025-01-24 14:30:23] Running migration: 2025_01_24_120000_create_users_table
[2025-01-24 14:30:23] Migration completed: 2025_01_24_120000_create_users_table
[2025-01-24 14:30:23] Migration process completed successfully.
```

### Error Recovery
If a migration fails:

1. Check the log file for specific error details
2. Fix the migration file
3. Use `php migrate.php rollback` if needed
4. Re-run `php migrate.php migrate`

## Best Practices

### Migration Guidelines

1. **Always provide rollback logic** in the `down()` method
2. **Use descriptive names** for migrations
3. **Test migrations** before deploying to production
4. **Don't modify existing migrations** once they're deployed

### Seeder Guidelines

1. **Check for existing data** before inserting
2. **Use meaningful sample data** for development
3. **Keep production seeds minimal** and safe
4. **Use proper error handling** for required relationships

### Example Production-Safe Seeder:

```php
public function run() {
    // Check if data already exists
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute(['admin@example.com']);
    
    if ($stmt->fetchColumn() == 0) {
        // Safe to insert
        $stmt = $this->db->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
        $stmt->execute([
            'admin@example.com',
            password_hash('secure_password', PASSWORD_DEFAULT),
            'admin'
        ]);
        
        echo "Admin user created.\n";
    } else {
        echo "Admin user already exists, skipping...\n";
    }
}
```

## Troubleshooting

### Common Issues

#### 1. "Class not found" error
- Ensure your migration/seeder class name matches the filename
- Check that the file is in the correct directory

#### 2. "Table already exists" error
- Check if the migration was already run: `php migrate.php status`
- Use `IF NOT EXISTS` in your CREATE TABLE statements

#### 3. Foreign key constraint errors
- Ensure parent tables are created first
- Check that referenced data exists when seeding

#### 4. Permission denied errors
- Ensure database user has necessary permissions
- Check file permissions on the migration directories

### Migration Status Commands

```bash
# Check what migrations have been run
php migrate.php status

# Expected output:
# Migration Status:
# ================
# Executed migrations: 5
# Pending migrations: 0
# 
# Executed:
#   ✓ 2025_01_24_120000_create_users_table
#   ✓ 2025_01_24_120001_create_project_categories_table
#   ...
```

## Advanced Usage

### Custom Migration Manager

You can extend the `MigrationManager` class for custom functionality:

```php
use App\Core\MigrationManager;

class CustomMigrationManager extends MigrationManager {
    public function customMethod() {
        // Your custom logic here
    }
}
```

### Batch Operations

```php
// In your application code
$migrationManager = new MigrationManager();

// Run specific environment setup
$migrationManager->migrate();
$migrationManager->seed('production');
```

### Database Backups

Before running migrations in production, consider backing up your database:

```bash
# MySQL backup example
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql

# Run migrations
php migrate.php migrate

# If something goes wrong, restore:
mysql -u username -p database_name < backup_20250124_143022.sql
```

## Security Considerations

1. **Never commit sensitive data** in seeders
2. **Use environment variables** for production credentials
3. **Change default passwords** immediately after setup
4. **Restrict database permissions** appropriately
5. **Use secure passwords** in production seeders

## Default Admin Account

After running the setup, you'll have an admin account:

- **Email**: admin@vuyanim.com
- **Password**: change_this_password_123!

**⚠️ IMPORTANT**: Change this password immediately after first login!

## Support

If you encounter issues:

1. Check the migration log: `database/migration.log`
2. Verify database connection in `app/config/database.php`
3. Ensure proper file permissions
4. Check that all required dependencies are installed

For additional help, refer to the main project documentation or create an issue in the project repository.

Complete Shared Hosting Solution
🌐 Web-Based Interfaces

Setup Interface (public/admin/setup.php)

Beautiful step-by-step setup wizard
Password-protected access
Creates all directories and files automatically
Tests database connection
Runs initial migrations and seeds


Migration Manager (public/admin/migrate.php)

Full-featured web interface for all migration operations
Real-time status display
Run migrations, rollbacks, and seeds
Progress tracking and logging
Mobile-responsive design


File Upload Interface (public/admin/upload.php)

Upload new migration and seeder files
File validation and security checks
Code templates and examples
File management and viewing



🚀 Step-by-Step Setup for Shared Hosting
Step 1: Upload Files
Upload these key files to your shared hosting:
🔐 Security Features

Password Protection: All admin interfaces require authentication
File Validation: Upload interface validates PHP files for security
Transaction Safety: All operations wrapped in database transactions
Error Recovery: Automatic rollback on failures
Access Logging: All operations logged with timestamps
Environment Detection: Automatically adapts to shared hosting limitations

🛠️ Key Features for Shared Hosting
No CLI Required

Complete web-based interface
Click-button migration management
Visual progress indicators
Real-time status updates

Timeout Handling

Operations broken into manageable chunks
Step-by-step execution to avoid timeouts
Progress tracking and resume capability
Memory-efficient processing

File Management

Web-based file upload for new migrations
Template generation and examples
File validation and security checks
Directory structure management

Error Handling

Comprehensive error reporting
Transaction rollback on failures
Detailed logging for troubleshooting
User-friendly error messages

📋 What You Get Out of the Box
Database Tables Created:

users - Admin and user management
project_categories - Portfolio categorization
projects - Your project showcase
blog_categories - Blog organization
blog_posts - Content management
resources - Downloadable files
contact_submissions - Contact form data
migrations - Migration tracking

Initial Data:

Admin User: admin@vuyanim.com / change_this_password_123!
Project Categories: Digital Design, Web Dev, App Dev, Game Dev, Maker Projects
Blog Categories: Articles, Tutorials, Resources, News

🚨 Important Security Notes
Change These Passwords Immediately:

Setup Password in setup.php: Line with SetupVuyani2025!
Migration Password in migrate.php: Line with MigrateAdmin2025!
Admin Account: After first login to your site

Secure Your Admin Directory:
Create /public/admin/.htaccess:
🔐 Security Features

Password Protection: All admin interfaces require authentication
File Validation: Upload interface validates PHP files for security
Transaction Safety: All operations wrapped in database transactions
Error Recovery: Automatic rollback on failures
Access Logging: All operations logged with timestamps
Environment Detection: Automatically adapts to shared hosting limitations

🛠️ Key Features for Shared Hosting
No CLI Required

Complete web-based interface
Click-button migration management
Visual progress indicators
Real-time status updates

Timeout Handling

Operations broken into manageable chunks
Step-by-step execution to avoid timeouts
Progress tracking and resume capability
Memory-efficient processing

File Management

Web-based file upload for new migrations
Template generation and examples
File validation and security checks
Directory structure management

Error Handling

Comprehensive error reporting
Transaction rollback on failures
Detailed logging for troubleshooting
User-friendly error messages

📋 What You Get Out of the Box
Database Tables Created:

users - Admin and user management
project_categories - Portfolio categorization
projects - Your project showcase
blog_categories - Blog organization
blog_posts - Content management
resources - Downloadable files
contact_submissions - Contact form data
migrations - Migration tracking

Initial Data:

Admin User: admin@vuyanim.com / change_this_password_123!
Project Categories: Digital Design, Web Dev, App Dev, Game Dev, Maker Projects
Blog Categories: Articles, Tutorials, Resources, News

🚨 Important Security Notes
Change These Passwords Immediately:

Setup Password in setup.php: Line with SetupVuyani2025!
Migration Password in migrate.php: Line with MigrateAdmin2025!
Admin Account: After first login to your site

Secure Your Admin Directory:
Create /public/admin/.htaccess:
# Restrict access to admin area
AuthType Basic
AuthName "Admin Area"
AuthUserFile /path/to/.htpasswd
Require valid-user

# Or restrict by IP
<RequireAll>
    Require ip YOUR.IP.ADDRESS.HERE
</RequireAll>

Migration Workflow for Production
Adding New Features:

Create locally: Develop and test migration files
Upload: Use the web upload interface
Review: Check migration status via web interface
Deploy: Run migrations with one click
Verify: Check logs and database changes

Managing Data:

Production Seeds: Safe data for live sites
Development Seeds: Sample data for testing
Backup: Always backup before major changes
Rollback: Easy one-click rollback if needed

💡 Best Practices for Shared Hosting
Before You Start:

Backup your database
Test locally first
Have your hosting DB credentials ready
Ensure your database exists

During Migration:

Run one step at a time
Monitor execution time
Check logs for any warnings
Verify each step completes successfully

After Migration:

Change all default passwords
Test your website functionality
Secure the admin directory
Remove setup files if desired

🆘 Troubleshooting
Common Issues & Solutions:
Database Connection Fails:

Check credentials in app/config/database.php
Verify database exists
Contact hosting provider for correct settings

File Upload Errors:

Check directory permissions (755)
Verify file size limits
Ensure proper file naming

Execution Timeouts:

Break large operations into smaller steps
Contact hosting provider about time limits
Use the step-by-step interfaces

Permission Denied:

Set directory permissions to 755
Set file permissions to 644
Use hosting file manager to adjust

The system should be able to:
You can now:

✅ Manage database schema changes professionally
✅ Deploy updates safely with rollback capability
✅ Collaborate with teams using version-controlled migrations
✅ Scale your project confidence
✅ Maintain data integrity with transaction safety
