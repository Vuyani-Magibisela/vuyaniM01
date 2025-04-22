<?php
// File: public/index.php
require_once __DIR__ . '/../vendor/autoload.php';
require_once '../app/core/App.php';
require_once '../app/core/Router.php';
require_once '../app/core/Database.php';
require_once '../app/core/Helpers.php';

require_once __DIR__ . '/../app/core/Router.php';
use App\core\Router;

// session_start();

$router = new Router();
$router->dispatch();
