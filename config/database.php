<?php
// Basic database configuration
return [
    'default' => 'development',
    'connections' => [
        'development' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ],
    ],
];
