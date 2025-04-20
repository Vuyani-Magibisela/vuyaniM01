<?php
/**
 * Helper Functions
 * Common utility functions
 */

/**
 * Clean input data
 * @param mixed $data Input data
 * @return mixed Cleaned data
 */
function clean($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Generate URL slug from string
 * @param string $string Input string
 * @return string URL-friendly slug
 */
function slugify($string) {
    // Replace non-alphanumeric characters with hyphens
    $string = preg_replace('/[^\p{L}\p{N}]+/u', '-', $string);
    // Remove duplicate hyphens
    $string = preg_replace('/-+/', '-', $string);
    // Remove leading/trailing hyphens
    $string = trim($string, '-');
    // Convert to lowercase
    $string = strtolower($string);
    return $string;
}

/**
 * Generate random string
 * @param int $length String length
 * @return string Random string
 */
function randomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * Format date
 * @param string $date Date string
 * @param string $format Date format
 * @return string Formatted date
 */
function formatDate($date, $format = 'F j, Y') {
    return date($format, strtotime($date));
}

/**
 * Format time ago
 * @param string $datetime Date and time string
 * @return string Time ago text
 */
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = round($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = round($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = round($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 2592000) {
        $weeks = round($diff / 604800);
        return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 31536000) {
        $months = round($diff / 2592000);
        return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
    } else {
        $years = round($diff / 31536000);
        return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
    }
}

/**
 * Truncate text to specified length
 * @param string $text Text to truncate
 * @param int $length Maximum length
 * @param string $suffix Suffix to add if truncated
 * @return string Truncated text
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . $suffix;
}

/**
 * Convert bytes to human-readable format
 * @param int $bytes Bytes to convert
 * @param int $precision Decimal precision
 * @return string Human-readable size
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generateCsrfToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool Whether token is valid
 */
function verifyCsrfToken($token) {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        return false;
    }
    
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Display flash message
 * @param string $name Message name
 * @return string HTML for flash message
 */
function flash($name) {
    if (isset($_SESSION['flash'][$name])) {
        $flash = $_SESSION['flash'][$name];
        unset($_SESSION['flash'][$name]);
        
        return '<div class="' . $flash['class'] . '" id="flash-message">' . $flash['message'] . '</div>';
    }
    
    return '';
}

/**
 * Check if current URL matches the given path
 * @param string $path Path to check
 * @return bool Whether current URL matches
 */
function isCurrentPath($path) {
    $currentPath = $_GET['url'] ?? '';
    return $currentPath === $path;
}

/**
 * Get active class if current URL matches the given path
 * @param string $path Path to check
 * @param string $class Class name
 * @return string Class if current URL matches, empty string otherwise
 */
function activeClass($path, $class = 'active') {
    return isCurrentPath($path) ? $class : '';
}

/**
 * Get current URL
 * @return string Current URL
 */
function currentUrl() {
    return URL_ROOT . '/' . ($_GET['url'] ?? '');
}

/**
 * Redirect to URL
 * @param string $url URL to redirect to
 * @return void
 */
function redirect($url) {
    header('Location: ' . URL_ROOT . '/' . $url);
    exit;
}

/**
 * Check if user is logged in
 * @return bool Whether user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * @return bool Whether user is admin
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}