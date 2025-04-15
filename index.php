<?php
// Front controller pattern
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Configuration
require 'config/database.php';
require 'config/routes.php';

// Bootstrap
session_start();
require 'app/Helpers/ClassLoader.php';

// Register autoloader
ClassLoader::register();

// Dispatch request
$router = new Router();
$router->dispatch();
?>
