<?php

declare(strict_types=1);

namespace App;

// HTTP helpers: 403, 404, check POST and CSRF
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
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error('Sicherheits-Token ungültig — bitte nochmal versuchen.');
            View::redirect('/');
        }
    }
}
