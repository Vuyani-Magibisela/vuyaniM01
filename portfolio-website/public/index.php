<?php
// File: public/index.php

// Determine the correct path based on server structure
$basePath = dirname(__DIR__);

require_once $basePath . '/vendor/autoload.php';
require_once $basePath . '/app/core/Env.php';
require_once $basePath . '/app/core/App.php';
require_once $basePath . '/app/core/Router.php';
require_once $basePath . '/app/core/Database.php';
require_once $basePath . '/app/core/Helpers.php';
require_once $basePath . '/app/core/Session.php';
require_once $basePath . '/app/core/Email.php';

use App\core\Router;
use App\Core\Session;
use App\Core\Env;

// Error handling is environment-driven: visible in development, logged
// only in production. Never echo stack traces to visitors.
$debug = Env::bool('APP_DEBUG', false);

error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('display_startup_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');

$logDir = $basePath . '/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0775, true);
}
ini_set('error_log', $logDir . '/php-errors.log');

// Initialize session with secure configuration
Session::init();

// Check for session timeout on every request
Session::checkTimeout();

// Clean up old login attempts periodically (1% chance per request)
if (rand(1, 100) === 1) {
    Session::cleanupLoginAttempts();
}

$router = new Router();
$router->dispatch();
