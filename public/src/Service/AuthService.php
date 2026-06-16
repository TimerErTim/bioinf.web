<?php

declare(strict_types=1);

namespace App\Service;

use App\Flash;
use App\Response;
use App\View;

// Login state in $_SESSION (session_start() runs in bootstrap.php)
final class AuthService
{
    public static function login(int $userId, string $username, bool $isAdmin): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['is_admin'] = $isAdmin;
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['is_admin']);
        session_regenerate_id(true);
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function userId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function username(): ?string
    {
        return $_SESSION['username'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return !empty($_SESSION['is_admin']);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            Flash::error('Bitte einloggen.');
            View::redirect('/login');
        }
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (!self::isAdmin()) {
            Response::forbidden();
        }
    }
}
