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
    
    // Live server configuration - UPDATE THESE VALUES
    return [
        'host' => 'localhost', // Usually 'localhost' for shared hosting
        'dbname' => 'vuyanjcb_portfolio', //for production : 'vuyanjcb_vuyanim' - remove this comment
        'user' => 'vuyanjcb_user',  //for production : 'vuyanjcb_vuyaniM' - remove this comment
        'password' => 'your_database_password', //for production : '=bQw^WUglto@IhRJ' - remove this comment
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    ];
}