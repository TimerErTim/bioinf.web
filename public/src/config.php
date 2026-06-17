<?php

declare(strict_types=1);

/*
 * App settings. This file returns an array (included via require in bootstrap.php).
 * Values match the project brief for XAMPP / phpMyAdmin import.
 */
return [
    'app' => [
        'name' => 'GoT Quotes',
    ],
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'team_4',
        'username' => 'fh_webphp',
        'password' => 'fh_webphp',
        'charset' => 'utf8mb4',
    ],
];
