<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

/**
 * PDO connection factory (singleton per request).
 */
final class Database
{
    private static ?PDO $connection = null;

    public static function connection(array $dbConfig): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $dbConfig['host'],
            $dbConfig['port'],
            $dbConfig['database'],
            $dbConfig['charset'],
        );

        try {
            self::$connection = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            http_response_code(500);
            echo 'Database connection failed.';
            exit(1);
        }

        return self::$connection;
    }
}
