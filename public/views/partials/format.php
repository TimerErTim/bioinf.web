<?php

use App\Html;

if (!function_exists('formatDateTime')) {
    function formatDateTime(?string $datetime): string
    {
        if ($datetime === null) {
            return '';
        }

        $timestamp = strtotime($datetime);
        if ($timestamp === false) {
            return Html::e($datetime);
        }

        return date('d.m.Y H:i', $timestamp);
    }
}

if (!function_exists('formatScore')) {
    function formatScore(int $score): string
    {
        return $score > 0 ? '+' . $score : (string) $score;
    }

    function scoreClass(int $score): string
    {
        return match (true) {
            $score > 0 => 'text-amber-400 bg-amber-950/40 border-amber-800/50',
            $score < 0 => 'text-red-400 bg-red-950/40 border-red-800/50',
            default => 'text-stone-400 bg-stone-800/60 border-stone-700',
        };
    }
}
