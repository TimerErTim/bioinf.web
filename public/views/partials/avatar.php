<?php

use App\Html;

if (!function_exists('userProfileUrl')) {
    function userProfileUrl(?int $userId): ?string
    {
        return $userId !== null ? '/users/' . $userId : null;
    }
}

if (!function_exists('renderAvatar')) {
    /**
     * @param array{username?: string|null, avatar_path?: string|null, user_id?: int|null} $user
     */
    function renderAvatar(array $user, string $size = 'md', bool $asLink = false): void
    {
        $sizes = [
            'sm' => 'h-8 w-8 text-xs',
            'md' => 'h-10 w-10 text-sm',
            'lg' => 'h-16 w-16 text-xl',
        ];
        $cls = $sizes[$size] ?? $sizes['md'];

        ob_start();
        $path = $user['avatar_path'] ?? null;
        if (is_string($path) && $path !== '') {
            echo '<img src="' . Html::e($path) . '" alt="" class="' . $cls . ' rounded-full object-cover ring-2 ring-stone-700 shrink-0">';
        } else {
            $name = $user['username'] ?? '?';
            $initial = strtoupper(mb_substr((string) $name, 0, 1));
            echo '<span class="' . $cls . ' inline-flex items-center justify-center rounded-full bg-gradient-to-br from-amber-700 to-stone-700 font-semibold text-stone-100 ring-2 ring-stone-700 shrink-0">' . Html::e($initial) . '</span>';
        }
        $inner = ob_get_clean();

        $profileUrl = $asLink ? userProfileUrl(isset($user['user_id']) ? (int) $user['user_id'] : null) : null;
        if ($profileUrl !== null) {
            echo '<a href="' . Html::e($profileUrl) . '" class="shrink-0 rounded-full hover:ring-2 hover:ring-amber-500/40 transition-all">' . $inner . '</a>';
            return;
        }

        echo $inner;
    }
}
