<?php
/**
 * Main configuration file
 */

// Application configuration
define('SITE_NAME', 'Your Portfolio');
define('APP_ROOT', dirname(dirname(__FILE__)));
define('URL_ROOT', 'http://localhost/portfolio-website');
define('URL_SUBFOLDER', '');

// Paths
define('VIEWS_PATH', APP_ROOT . '/views/');
define('UPLOADS_PATH', dirname(dirname(APP_ROOT)) . '/public/uploads/');
define('RESOURCES_PATH', dirname(dirname(APP_ROOT)) . '/public/resources/');

// Default controller and method
define('DEFAULT_CONTROLLER', 'Home');
define('DEFAULT_METHOD', 'index');

// Environment variables
define('ENV', 'development'); // Options: development, production

// Error reporting
if (ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Session configuration
define('SESSION_NAME', 'portfolio_session');
define('SESSION_LIFETIME', 7200); // 2 hours
define('SESSION_PATH', '/');
define('SESSION_SECURE', false); // Set to true if using HTTPS
define('SESSION_HTTP_ONLY', true);

// CSRF Protection
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_LIFETIME', 3600); // 1 hour

// Pagination defaults
define('ITEMS_PER_PAGE', 10);

// Default image placeholders
define('DEFAULT_PROFILE_IMAGE', URL_ROOT . '/images/default-profile.jpg');
define('DEFAULT_PROJECT_IMAGE', URL_ROOT . '/images/default-project.jpg');