<?php
/**
 * App Class
 * Application bootstrap and core functionality
 */

class App {
    /**
     * Initialize the application
     */
    public function __construct() {
        // Start session
        $this->startSession();
        
        // Load configuration
        $this->loadConfig();
        
        // Initialize router
        new Router();
    }
    
    /**
     * Start and configure the session
     */
    private function startSession() {
        // Set session name
        session_name(SESSION_NAME);
        
        // Set session parameters
        session_set_cookie_params(
            SESSION_LIFETIME,
            SESSION_PATH,
            $_SERVER['HTTP_HOST'],
            SESSION_SECURE,
            SESSION_HTTP_ONLY
        );
        
        // Start session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Regenerate session ID periodically for security
        if (!isset($_SESSION['last_regeneration'])) {
            $this->regenerateSessionId();
        } else {
            // Regenerate session ID every 30 minutes
            $interval = 30 * 60;
            if ($_SESSION['last_regeneration'] + $interval < time()) {
                $this->regenerateSessionId();
            }
        }
    }
    
    /**
     * Regenerate session ID securely
     */
    private function regenerateSessionId() {
        // Regenerate session ID
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
    
    /**
     * Load application configuration
     */
    private function loadConfig() {
        // Load main configuration
        require_once '../app/config/config.php';
        
        // Load database configuration
        require_once '../app/config/database.php';
        
        // Load helper functions
        require_once '../app/core/Helpers.php';
    }
}