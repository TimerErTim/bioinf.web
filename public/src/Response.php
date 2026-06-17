<?php

declare(strict_types=1);

namespace App;

/*
 * Small HTTP helpers used by controllers before doing work.
 *
 * REST: DELETE/PATCH/PUT use Fetch from the browser; CSRF via header or form field.
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

    public static function methodNotAllowed(): void
    {
        http_response_code(405);
        exit;
    }

    /** @param list<string> $allowed */
    public static function requireMethod(array $allowed): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (!in_array($method, $allowed, true)) {
            self::methodNotAllowed();
        }
    }

    public static function requirePost(): void
    {
        self::requireMethod(['POST']);
    }

    public static function requireCsrf(): void
    {
        $token = $_POST['_csrf'] ?? null;

        if ($token === null) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        }

        if (!Csrf::validate(is_string($token) ? $token : null)) {
            Flash::error('Sicherheits-Token ungültig. Bitte nochmal versuchen.');
            View::redirect('/');
        }
    }
}
