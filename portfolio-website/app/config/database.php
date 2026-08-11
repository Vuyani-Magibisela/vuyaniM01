<?php
/**
 * Database Configuration
 *
 * Values come from the environment (Docker Compose) or an untracked .env
 * file. No credentials live in version control.
 *
 * See .env.example for the full list of supported keys.
 */

require_once dirname(__DIR__) . '/core/Env.php';

use App\Core\Env;

return [
    'host'     => Env::get('DB_HOST', 'localhost'),
    'port'     => Env::int('DB_PORT', 3306),
    'dbname'   => Env::get('DB_NAME', 'vuyanim'),
    'user'     => Env::get('DB_USER', 'root'),
    'password' => Env::get('DB_PASSWORD', ''),
    'charset'  => Env::get('DB_CHARSET', 'utf8mb4'),
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];
