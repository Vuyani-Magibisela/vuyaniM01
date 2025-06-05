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

use App\core\Router;

// session_start();

$router = new Router();
$router->dispatch();