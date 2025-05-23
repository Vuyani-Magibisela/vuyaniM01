<?php
namespace App\Core;

use PDO;
use PDOException;

/**
 * Database Connection Class
 * Creates a PDO connection to the database
 */
class Database {
    /**
     * PDO instance
     * @var PDO
     */
    private static $instance = null;
    
    /**
     * Configuration
     * @var array
     */
    private static $config = null;

    /**
     * Get a database connection, creating one if it doesn't exist
     * @return PDO Database connection
     * @throws PDOException If connection fails
     */
    public static function connect() {
        if (self::$instance === null) {
            self::loadConfig();
            
            try {
                $dsn = "mysql:host=" . self::$config['host'];
                
                // Add database name to DSN if provided
                if (!empty(self::$config['dbname'])) {
                    $dsn .= ";dbname=" . self::$config['dbname'];
                }
                
                // Add charset if provided
                if (!empty(self::$config['charset'])) {
                    $dsn .= ";charset=" . self::$config['charset'];
                }
                
                // Create PDO instance
                self::$instance = new PDO(
                    $dsn,
                    self::$config['user'],
                    self::$config['password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e) {
                // Log error message
                error_log("Database Connection Error: " . $e->getMessage());
                
                // Rethrow the exception
                throw $e;
            }
        }

        return self::$instance;
    }
    
    /**
     * Load database configuration
     */
    private static function loadConfig() {
        // Only load config once
        if (self::$config === null) {
            $configFile = dirname(__DIR__) . '/config/database.php';
            
            if (file_exists($configFile)) {
                self::$config = require $configFile;
            } else {
                // If config file doesn't exist, use default values
                self::$config = [
                    'host' => 'localhost',
                    'dbname' => 'vuyanim',
                    'user' => 'root',
                    'password' => '',
                    'charset' => 'utf8mb4'
                ];
                
                // Log warning
                error_log("Database configuration file not found. Using default values.");
            }
        }
    }
    
    /**
     * Close the database connection
     */
    public static function close() {
        self::$instance = null;
    }
    
    /**
     * Private constructor to prevent instantiation
     */
    private function __construct() {
        // Prevent instantiation
    }
    
    /**
     * Private clone method to prevent cloning
     */
    private function __clone() {
        // Prevent cloning
    }
}