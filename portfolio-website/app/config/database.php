<?php
// Detect environment and return appropriate database configuration
if ($_SERVER['HTTP_HOST'] === 'localhost' || 
    strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false || 
    strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
    
    // Local development configuration
    return [
        'host' => 'localhost',
        'dbname' => 'vuyanim',
        'user' => 'vuksDev',
        'password' => 'Vu13#k*s3D3V',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    ];
    
} else {
    
    // Production server configuration
    return [
        'host' => 'localhost',
        'dbname' => 'vuyanjcb_vuyanim',
        'user' => 'vuyanjcb_vuyaniM',
        'password' => '=bQw^WUglto@IhRJ',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    ];
}