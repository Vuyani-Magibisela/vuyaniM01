<?php

namespace App\Core;

class Session {
    private static $started = false;
    private static $timeout = 1800; // 30 minutes in seconds

    /**
     * Initialize session with secure configuration
     */
    public static function init() {
        if (self::$started) {
            return;
        }

        // Secure session configuration
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 1 : 0);
        ini_set('session.cookie_samesite', 'Lax');

        // Start session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            self::$started = true;

            // Initialize session activity tracker
            if (!isset($_SESSION['LAST_ACTIVITY'])) {
                $_SESSION['LAST_ACTIVITY'] = time();
            }

            // Initialize login attempts tracker
            if (!isset($_SESSION['login_attempts'])) {
                $_SESSION['login_attempts'] = [];
            }
        }
    }

    /**
     * Set session variable
     * @param string $key
     * @param mixed $value
     */
    public static function set($key, $value) {
        self::init();
        $_SESSION[$key] = $value;
    }

    /**
     * Get session variable
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null) {
        self::init();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if session variable exists
     * @param string $key
     * @return bool
     */
    public static function has($key) {
        self::init();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session variable
     * @param string $key
     */
    public static function remove($key) {
        self::init();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Check if user is authenticated
     * @return bool
     */
    public static function isAuthenticated() {
        return self::has('user_id') && self::has('user_role');
    }

    /**
     * Check if user is admin
     * @return bool
     */
    public static function isAdmin() {
        return self::isAuthenticated() && self::get('user_role') === 'admin';
    }

    /**
     * Get authenticated user ID
     * @return int|null
     */
    public static function getUserId() {
        return self::get('user_id');
    }

    /**
     * Get authenticated user role
     * @return string|null
     */
    public static function getUserRole() {
        return self::get('user_role');
    }

    /**
     * Set authenticated user data
     * @param object $user
     */
    public static function login($user) {
        self::set('user_id', $user->id);
        self::set('user_role', $user->role);
        self::set('username', $user->username);
        self::set('email', $user->email);
        self::set('LAST_ACTIVITY', time());

        // Regenerate session ID to prevent fixation attacks
        session_regenerate_id(true);
    }

    /**
     * Clear all session data and destroy session
     */
    public static function logout() {
        self::init();

        // Clear session data
        $_SESSION = [];

        // Delete session cookie
        if (isset($_COOKIE[session_name()])) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Destroy session
        session_destroy();
        self::$started = false;
    }

    /**
     * Check for session timeout and destroy if expired
     * @return bool True if session is valid, false if timed out
     */
    public static function checkTimeout() {
        self::init();

        if (!self::isAuthenticated()) {
            return true; // Not logged in, no timeout to check
        }

        $lastActivity = self::get('LAST_ACTIVITY', 0);
        $currentTime = time();

        // Check if session has timed out
        if (($currentTime - $lastActivity) > self::$timeout) {
            self::logout();
            return false;
        }

        // Update last activity time
        self::set('LAST_ACTIVITY', $currentTime);
        return true;
    }

    /**
     * Set flash message (one-time message)
     * @param string $type (success, error, warning, info)
     * @param string $message
     */
    public static function setFlash($type, $message) {
        self::set('flash_' . $type, $message);
    }

    /**
     * Get and remove flash message
     * @param string $type
     * @return string|null
     */
    public static function getFlash($type) {
        $message = self::get('flash_' . $type);
        self::remove('flash_' . $type);
        return $message;
    }

    /**
     * Track login attempts for brute force protection
     * @param string $identifier Username or email
     * @return int Number of attempts
     */
    public static function trackLoginAttempt($identifier) {
        self::init();

        $attempts = self::get('login_attempts', []);
        $key = md5($identifier);

        if (!isset($attempts[$key])) {
            $attempts[$key] = [
                'count' => 0,
                'last_attempt' => 0,
                'locked_until' => 0
            ];
        }

        $attempts[$key]['count']++;
        $attempts[$key]['last_attempt'] = time();

        // Lock account after 5 failed attempts for 15 minutes
        if ($attempts[$key]['count'] >= 5) {
            $attempts[$key]['locked_until'] = time() + (15 * 60);
        }

        self::set('login_attempts', $attempts);

        return $attempts[$key]['count'];
    }

    /**
     * Check if login is locked for identifier
     * @param string $identifier Username or email
     * @return bool True if locked
     */
    public static function isLoginLocked($identifier) {
        self::init();

        $attempts = self::get('login_attempts', []);
        $key = md5($identifier);

        if (!isset($attempts[$key])) {
            return false;
        }

        $lockedUntil = $attempts[$key]['locked_until'] ?? 0;

        // Check if lock has expired
        if ($lockedUntil > 0 && time() < $lockedUntil) {
            return true;
        }

        // Lock expired, reset attempts
        if ($lockedUntil > 0 && time() >= $lockedUntil) {
            self::resetLoginAttempts($identifier);
        }

        return false;
    }

    /**
     * Get remaining lockout time in seconds
     * @param string $identifier Username or email
     * @return int Seconds remaining
     */
    public static function getLockoutRemaining($identifier) {
        self::init();

        $attempts = self::get('login_attempts', []);
        $key = md5($identifier);

        if (!isset($attempts[$key]) || !isset($attempts[$key]['locked_until'])) {
            return 0;
        }

        $remaining = $attempts[$key]['locked_until'] - time();
        return max(0, $remaining);
    }

    /**
     * Reset login attempts for identifier
     * @param string $identifier Username or email
     */
    public static function resetLoginAttempts($identifier) {
        self::init();

        $attempts = self::get('login_attempts', []);
        $key = md5($identifier);

        if (isset($attempts[$key])) {
            unset($attempts[$key]);
            self::set('login_attempts', $attempts);
        }
    }

    /**
     * Clean up old login attempt records (call periodically)
     */
    public static function cleanupLoginAttempts() {
        self::init();

        $attempts = self::get('login_attempts', []);
        $currentTime = time();
        $cleanupAge = 3600; // 1 hour

        foreach ($attempts as $key => $data) {
            // Remove attempts older than 1 hour with no lock
            if (($currentTime - $data['last_attempt']) > $cleanupAge && $data['locked_until'] === 0) {
                unset($attempts[$key]);
            }
        }

        self::set('login_attempts', $attempts);
    }

    /**
     * Generate CSRF token
     * @return string
     */
    public static function generateCsrfToken() {
        self::init();

        if (!self::has('csrf_token')) {
            $token = bin2hex(random_bytes(32));
            self::set('csrf_token', $token);
        }

        return self::get('csrf_token');
    }

    /**
     * Verify CSRF token
     * @param string $token
     * @return bool
     */
    public static function verifyCsrfToken($token) {
        self::init();
        return hash_equals(self::get('csrf_token', ''), $token);
    }
}
