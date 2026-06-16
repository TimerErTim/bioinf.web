<?php

declare(strict_types=1);

/**
 * Application configuration.
 * Supports local development (mise, port 33060) and standard XAMPP (port 3306).
 */
return [
    'app' => [
        'name' => 'GoT Quotes',
        'base_path' => '',
    ],
    'db' => [
        'host' => getenv('MYSQL_HOST') ?: '127.0.0.1',
        'port' => (int) (getenv('MYSQL_TCP_PORT') ?: 3306),
        'database' => getenv('MYSQL_DATABASE') ?: 'team_4',
        'username' => getenv('MYSQL_USER') ?: 'fh_webphp',
        'password' => getenv('MYSQL_PASSWORD') ?: 'fh_webphp',
        'charset' => 'utf8mb4',
    ],
];
