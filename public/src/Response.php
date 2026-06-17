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

    /**
     * Ensures the current request method is in the list of allowed methods.
     * @param list<string> $allowed
     */
    public static function requireMethod(array $allowed): void
    {
        // Get the HTTP request method, default to GET if not set (can be ENV dependent)
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (!in_array($method, $allowed, true)) {
            self::methodNotAllowed();
        }
    }

    public static function requirePost(): void
    {
        self::requireMethod(['POST']);
    }

    /** Validate CSRF token from form or AJAX header */
    public static function requireCsrf(): void
    {
        // Get token from POST body or HTTP header. Ambiguous which user agent provided which; both are checked.
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        if (!Csrf::validate(is_string($token) ? $token : null)) {
            Flash::error('Sicherheits-Token ungültig. Bitte nochmal versuchen.');
            View::redirect('/');
        }
    }

    /**
     * Redirects back to the previous page if the referer is valid and same host;
     * otherwise, uses provided fallback. This check reduces open redirect risk.
     */
    public static function redirectBack(string $fallback): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $host = $_SERVER['HTTP_HOST'] ?? '';

        // Only redirect to referer if it's present and matches this host
        if (is_string($referer) && $referer !== '' && $host !== '' && str_contains($referer, $host)) {
            header('Location: ' . $referer);
            exit;
        }

        View::redirect($fallback);
    }
}
