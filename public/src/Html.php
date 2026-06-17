<?php

declare(strict_types=1);

namespace App;

/*
 * Escape text before printing it in HTML.
 *
 * XSS = attacker injects script via user input (e.g. comment text).
 * htmlspecialchars turns < into &lt; so the browser shows text, not HTML/JS.
 * Always use Html::e() for data that came from users or the database.
 */
final class Html
{
    public static function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
