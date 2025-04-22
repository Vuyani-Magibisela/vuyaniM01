<?php
namespace App\core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;

    public static function connect() {
        if (self::$instance === null) {
            $config = require '../app/config/database.php';
            try {
                self::$instance = new PDO(
                    "mysql:host={$config['host']};dbname={$config['dbname']}",
                    $config['user'],
                    $config['password']
                );
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Database Connection Failed: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
