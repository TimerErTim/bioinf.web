<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Server-side input validation rules.
 */
final class ValidationService
{
    /** @return list<string> */
    public static function username(?string $username): array
    {
        $errors = [];
        $username = trim((string) $username);

        if ($username === '') {
            $errors[] = 'Username is required.';
        } elseif (strlen($username) < 3 || strlen($username) > 50) {
            $errors[] = 'Username must be between 3 and 50 characters.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Username may only contain letters, numbers, and underscores.';
        }

        return $errors;
    }

    /** @return list<string> */
    public static function password(?string $password): array
    {
        $errors = [];
        $password = (string) $password;

        if ($password === '') {
            $errors[] = 'Password is required.';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long.';
        }

        return $errors;
    }

    /** @return list<string> */
    public static function quoteText(?string $text): array
    {
        $errors = [];
        $text = trim((string) $text);

        if ($text === '') {
            $errors[] = 'Quote text is required.';
        } elseif (strlen($text) > 2000) {
            $errors[] = 'Quote text must not exceed 2000 characters.';
        }

        return $errors;
    }

    /** @return list<string> */
    public static function speaker(?string $speaker): array
    {
        $errors = [];
        $speaker = trim((string) $speaker);

        if ($speaker === '') {
            $errors[] = 'Speaker is required.';
        } elseif (strlen($speaker) > 100) {
            $errors[] = 'Speaker name must not exceed 100 characters.';
        }

        return $errors;
    }

    /** @return list<string> */
    public static function imagePath(?string $path): array
    {
        if ($path === null || trim($path) === '') {
            return [];
        }

        $path = trim($path);
        if (!preg_match('/^assets\/images\/quotes\/[a-zA-Z0-9_\-]+\.(jpg|jpeg|png|webp)$/', $path)) {
            return ['Image path must be a valid path under assets/images/quotes/.'];
        }

        return [];
    }

    /** @return list<string> */
    public static function commentContent(?string $content): array
    {
        $errors = [];
        $content = trim((string) $content);

        if ($content === '') {
            $errors[] = 'Comment cannot be empty.';
        } elseif (strlen($content) > 1000) {
            $errors[] = 'Comment must not exceed 1000 characters.';
        }

        return $errors;
    }

    public static function optionalUint(?string $value, string $fieldLabel): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }

        if (!ctype_digit(trim($value)) || (int) $value < 1 || (int) $value > 255) {
            return ["{$fieldLabel} must be a number between 1 and 255."];
        }

        return [];
    }
}
