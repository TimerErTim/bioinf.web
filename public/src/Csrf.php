<?php

declare(strict_types=1);

namespace App;

/*
 * CSRF = Cross-Site Request Forgery protection.
 *
 * Problem: another website could trick the browser into POSTing to our site
 * while the user is logged in. The hidden token proves the form came from us.
 *
 * Flow: token is stored in $_SESSION and also sent as hidden field _csrf in forms.
 * On POST we compare both values with hash_equals() (safe string compare).
 */
final class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    public static function token(): string
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    /** Hidden input HTML for forms. Always use inside POST forms. */
    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . Html::e(self::token()) . '">';
    }

    public static function validate(?string $token): bool
    {
        $expected = $_SESSION[self::SESSION_KEY] ?? '';

        return is_string($token)
            && $expected !== ''
            // hash_equals avoids timing attacks (do not use === for secrets).
            && hash_equals($expected, $token);
    }
}
