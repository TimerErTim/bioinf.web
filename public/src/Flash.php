<?php

declare(strict_types=1);

namespace App;

/**
 * Session-backed flash messages (one-time display).
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

    /** @return list<array{type: string, message: string}> */
    public static function consume(): array
    {
        $messages = $_SESSION[self::SESSION_KEY] ?? [];
        unset($_SESSION[self::SESSION_KEY]);

        return is_array($messages) ? $messages : [];
    }

    private static function push(string $type, string $message): void
    {
        $_SESSION[self::SESSION_KEY] ??= [];
        $_SESSION[self::SESSION_KEY][] = ['type' => $type, 'message' => $message];
    }
}
