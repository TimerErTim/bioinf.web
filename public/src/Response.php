<?php

declare(strict_types=1);

namespace App;

/**
 * HTTP response helpers.
 */
final class Response
{
    public static function forbidden(): never
    {
        http_response_code(403);
        View::render('errors/403', ['title' => 'Forbidden']);
        exit;
    }

    public static function notFound(): never
    {
        http_response_code(404);
        View::render('errors/404', ['title' => 'Not Found']);
        exit;
    }

    public static function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }
    }

    public static function requireCsrf(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error('Invalid security token. Please try again.');
            View::redirect('/');
        }
    }
}
