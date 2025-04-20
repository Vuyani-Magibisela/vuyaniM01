<?php
/**
 * Database configuration
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Change in production
define('DB_PASS', ''); // Change in production
define('DB_NAME', 'vuyanim');
define('DB_CHARSET', 'utf8mb4');

// PDO connection options
define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);