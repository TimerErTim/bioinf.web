<?php

declare(strict_types=1);

namespace App;

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

    public static function consume(): array
    {
        // Retrieve and remove all flash messages from session at once
        $messages = $_SESSION[self::SESSION_KEY] ?? [];
        unset($_SESSION[self::SESSION_KEY]);

        return is_array($messages) ? $messages : [];
    }

    private static function push(string $type, string $message): void
    {
        // Lazily initialize the session array for flash messages if not present
        $_SESSION[self::SESSION_KEY] ??= [];
        $_SESSION[self::SESSION_KEY][] = ['type' => $type, 'message' => $message];
    }
}
