<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Session-based authentication state.
 */
final class AuthService
{
    private const SESSION_USER_ID = 'user_id';
    private const SESSION_USERNAME = 'username';
    private const SESSION_IS_ADMIN = 'is_admin';

    public static function login(int $userId, string $username, bool $isAdmin): void
    {
        session_regenerate_id(true);
        $_SESSION[self::SESSION_USER_ID] = $userId;
        $_SESSION[self::SESSION_USERNAME] = $username;
        $_SESSION[self::SESSION_IS_ADMIN] = $isAdmin;
    }

    public static function logout(): void
    {
        unset(
            $_SESSION[self::SESSION_USER_ID],
            $_SESSION[self::SESSION_USERNAME],
            $_SESSION[self::SESSION_IS_ADMIN],
        );
        session_regenerate_id(true);
    }

    public static function check(): bool
    {
        return isset($_SESSION[self::SESSION_USER_ID]);
    }

    public static function userId(): ?int
    {
        return isset($_SESSION[self::SESSION_USER_ID])
            ? (int) $_SESSION[self::SESSION_USER_ID]
            : null;
    }

    public static function username(): ?string
    {
        return $_SESSION[self::SESSION_USERNAME] ?? null;
    }

    public static function isAdmin(): bool
    {
        return !empty($_SESSION[self::SESSION_IS_ADMIN]);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            \App\Flash::error('Please log in to continue.');
            \App\View::redirect('/login');
        }
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (!self::isAdmin()) {
            \App\Response::forbidden();
        }
    }
}
