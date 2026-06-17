<?php

declare(strict_types=1);

namespace App\Service;

use App\Flash;
use App\Response;
use App\View;

final class AuthService
{
    public static function login(int $userId, string $username, bool $isAdmin, ?string $avatarPath = null): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['is_admin'] = $isAdmin;
        $_SESSION['avatar_path'] = $avatarPath;
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['is_admin'], $_SESSION['avatar_path']);
        session_regenerate_id(true);
    }

    public static function refreshAvatar(?string $avatarPath): void
    {
        if (self::check()) {
            $_SESSION['avatar_path'] = $avatarPath;
        }
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

    public static function avatarPath(): ?string
    {
        $path = $_SESSION['avatar_path'] ?? null;

        return is_string($path) && $path !== '' ? $path : null;
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
