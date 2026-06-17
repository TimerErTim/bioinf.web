<?php

declare(strict_types=1);

namespace App;

/*
 * Flash messages for Post/Redirect/Get (PRG).
 *
 * After a form POST we redirect to another page. A normal variable would be
 * lost. We store the message in $_SESSION, show it once in the layout, then
 * delete it (consume).
 */
final class Flash
{
    private const SESSION_KEY = '_flash';

    public static function success(string $message): void
    {
        self::push('success', $message);
    }

    public static function error(string $message): void
    {
        self::push('error', $message);
    }

    public static function errors(array $messages): void
    {
        foreach ($messages as $message) {
            self::push('error', $message);
        }
    }

    /** Read all messages and clear them from session. */
    public static function consume(): array
    {
        $messages = $_SESSION[self::SESSION_KEY] ?? [];
        unset($_SESSION[self::SESSION_KEY]);

        return is_array($messages) ? $messages : [];
    }

    private static function push(string $type, string $message): void
    {
        // ??= means: use existing array or create empty array first.
        $_SESSION[self::SESSION_KEY] ??= [];
        $_SESSION[self::SESSION_KEY][] = ['type' => $type, 'message' => $message];
    }
}
