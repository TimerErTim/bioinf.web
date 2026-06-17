<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

/*
 * PDO = PHP Data Objects. Database access API (like JDBC in Java).
 *
 * We use one shared connection per request (static $connection).
 * Prepared statements with :placeholders prevent SQL injection.
 */
final class Database
{
    private static ?PDO $connection = null;

    public static function connection(array $dbConfig): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        // DSN = connection string (host, port, database name, charset).
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $dbConfig['host'],
            $dbConfig['port'],
            $dbConfig['database'],
            $dbConfig['charset'],
        );

        try {
            self::$connection = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
                // Throw exceptions on SQL errors instead of silent failures.
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                // fetch() returns associative arrays ['id' => 1, 'name' => '...'].
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $exception) {
            http_response_code(500);
            echo 'Datenbankverbindung fehlgeschlagen.';
            exit(1);
        }

        return self::$connection;
    }
}
