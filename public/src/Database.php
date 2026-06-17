<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

final class Database
{
    // Holds the singleton PDO connection
    private static ?PDO $connection = null;

    public static function connection(array $dbConfig): PDO
    {
        // If already connected, reuse the same PDO instance
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        // Construct DSN string with parameters from config
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $dbConfig['host'],
            $dbConfig['port'],
            $dbConfig['database'],
            $dbConfig['charset'],
        );

        try {
            // Create PDO connection with exception mode and assoc fetch mode
            self::$connection = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $exception) {
            // If connection fails, send HTTP 500 and terminate script
            http_response_code(500);
            echo 'Datenbankverbindung fehlgeschlagen.';
            exit(1);
        }

        return self::$connection;
    }
}
