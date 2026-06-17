<?php

declare(strict_types=1);

namespace App\Service;

final class ValidationService
{
    public static function username(?string $username): array
    {
        $errors = [];
        $username = trim((string) $username); // remove whitespace

        if ($username === '') {
            $errors[] = 'Benutzername ist Pflicht.';
        } elseif (strlen($username) < 3 || strlen($username) > 50) {
            $errors[] = 'Benutzername: 3–50 Zeichen.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Benutzername: nur Buchstaben, Zahlen, Unterstrich.';
        }

        return $errors;
    }

    public static function password(?string $password): array
    {
        $errors = [];
        $password = (string) $password;

        if ($password === '') {
            $errors[] = 'Passwort ist Pflicht.';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Passwort: mindestens 8 Zeichen.';
        }

        return $errors;
    }

    public static function quoteText(?string $text): array
    {
        $errors = [];
        $text = trim((string) $text);

        if ($text === '') {
            $errors[] = 'Zitat-Text ist Pflicht.';
        } elseif (strlen($text) > 2000) {
            $errors[] = 'Zitat-Text: max. 2000 Zeichen.';
        }

        return $errors;
    }

    public static function speaker(?string $speaker): array
    {
        $errors = [];
        $speaker = trim((string) $speaker);

        if ($speaker === '') {
            $errors[] = 'Sprecher ist Pflicht.';
        } elseif (strlen($speaker) > 100) {
            $errors[] = 'Sprecher: max. 100 Zeichen.';
        }

        return $errors;
    }

    public static function commentContent(?string $content): array
    {
        $errors = [];
        $content = trim((string) $content);

        if ($content === '') {
            $errors[] = 'Kommentar darf nicht leer sein.';
        } elseif (strlen($content) > 1000) {
            $errors[] = 'Kommentar: max. 1000 Zeichen.';
        }

        return $errors;
    }

    public static function optionalUint(?string $value, string $fieldLabel): array
    {
        // Allow empty input and skip validation in that case
        if ($value === null || trim($value) === '') {
            return [];
        }

        // Must be digits, from 1 to 255
        if (!ctype_digit(trim($value)) || (int) $value < 1 || (int) $value > 255) {
            return ["{$fieldLabel}: Zahl zwischen 1 und 255."];
        }

        return [];
    }
}
