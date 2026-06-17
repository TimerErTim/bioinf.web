<?php

if (!function_exists('openCenteredPage')) {
    /** Narrow centered column for auth, profile, and form pages. */
    function openCenteredPage(string $width = 'md'): void
    {
        $max = match ($width) {
            'lg' => 'max-w-lg',
            'xl' => 'max-w-2xl',
            default => 'max-w-md',
        };
        echo '<div class="mx-auto w-full ' . $max . '">';
    }

    function closeCenteredPage(): void
    {
        echo '</div>';
    }
}
