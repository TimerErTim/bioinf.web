<?php

declare(strict_types=1);

namespace App;

final class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    public static function token(): string
    {
        // Generate random token if not set in session
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . Html::e(self::token()) . '">';
    }

    public static function validate(?string $token): bool
    {
        // Mitigate timing attacks with hash_equals and check both presence and type
        $expected = $_SESSION[self::SESSION_KEY] ?? '';

        return is_string($token)
            && $expected !== ''
            && hash_equals($expected, $token);
    }
}
