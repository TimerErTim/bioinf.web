<?php

declare(strict_types=1);

namespace App;

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
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        if (!Csrf::validate(is_string($token) ? $token : null)) {
            Flash::error('Sicherheits-Token ungültig. Bitte nochmal versuchen.');
            View::redirect('/');
        }
    }

    public static function redirectBack(string $fallback): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $host = $_SERVER['HTTP_HOST'] ?? '';

        if (is_string($referer) && $referer !== '' && $host !== '' && str_contains($referer, $host)) {
            header('Location: ' . $referer);
            exit;
        }

        View::redirect($fallback);
    }
}
