<?php

declare(strict_types=1);

namespace App;

/*
 * Small HTTP helpers used by controllers before doing work.
 *
 * Mutating actions (delete, update, login) should use POST, not GET.
 * GET links can be prefetched or opened by accident; POST + CSRF is safer.
 */
final class Response
{
    public static function forbidden(): void
    {
        http_response_code(403);
        View::render('errors/403', ['title' => 'Kein Zugriff']);
        exit;
    }

    public static function notFound(): void
    {
        http_response_code(404);
        View::render('errors/404', ['title' => 'Nicht gefunden']);
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
        // $_POST contains form fields from the request body (POST only).
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error('Sicherheits-Token ungültig. Bitte nochmal versuchen.');
            View::redirect('/');
        }
    }
}
