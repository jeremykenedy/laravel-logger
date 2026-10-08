<?php

return [
    'default' => env('LOGGER_TEST_DB_CONNECTION', 'sqlite'),
    'connections' => [
        'sqlite' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
        'mysql' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => env('LOGGER_TEST_DB_PORT', 3306),
            'database' => 'laravel_logger_testing',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
        ],
    ],
];
