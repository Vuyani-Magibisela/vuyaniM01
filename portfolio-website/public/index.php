<?php
// File: public/index.php

// Error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Determine the correct path based on server structure
$basePath = dirname(__DIR__);

require_once $basePath . '/vendor/autoload.php';
require_once $basePath . '/app/core/App.php';
require_once $basePath . '/app/core/Router.php';
require_once $basePath . '/app/core/Database.php';
require_once $basePath . '/app/core/Helpers.php';
require_once $basePath . '/app/core/Session.php';

use App\core\Router;
use App\Core\Session;

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