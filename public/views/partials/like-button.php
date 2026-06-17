<?php

use App\Html;
use App\Service\AuthService;

if (!function_exists('renderLikeButton')) {
    /**
     * @param array{ id: int|string, user_liked?: int|string|bool, like_count?: int|string } $quote
     */
    function renderLikeButton(array $quote, string $size = 'md'): void
    {
        $quoteId = (int) $quote['id'];
        $likeCount = (int) ($quote['like_count'] ?? 0);
        $userLiked = !empty($quote['user_liked']);
        $url = '/quotes/' . $quoteId . '/likes';

        $btnClass = match ($size) {
            'sm' => 'px-2 py-1 text-xs gap-1',
            default => 'px-3 py-1.5 text-sm gap-1.5',
        };

        if (!AuthService::check()) {
            ?>
            <a href="/login"
               class="inline-flex items-center <?= $btnClass ?> rounded-lg border border-stone-700 text-stone-400 hover:border-amber-700 hover:text-amber-400 transition-colors"
               title="Einloggen zum Liken">
                <span aria-hidden="true">♡</span>
                <span><?= $likeCount ?></span>
            </a>
            <?php
            return;
        }

        ?>
        <button type="button"
                data-like-toggle="<?= Html::e($url) ?>"
                data-liked="<?= $userLiked ? '1' : '0' ?>"
                class="inline-flex items-center <?= $btnClass ?> rounded-lg border transition-colors cursor-pointer
                       <?= $userLiked
                           ? 'border-amber-600/60 bg-amber-950/40 text-amber-400 hover:bg-amber-950/60'
                           : 'border-stone-700 text-stone-400 hover:border-amber-700 hover:text-amber-400' ?>"
                title="<?= $userLiked ? 'Like entfernen' : 'Gefällt mir' ?>">
            <span aria-hidden="true" data-like-icon><?= $userLiked ? '♥' : '♡' ?></span>
            <span data-like-count><?= $likeCount ?></span>
        </button>
        <?php
    }
}

if (!function_exists('renderQuoteStats')) {
    function renderQuoteStats(array $quote): void
    {
        $commentCount = (int) ($quote['comment_count'] ?? 0);
        ?>
        <div class="flex flex-wrap items-center gap-2">
            <?php renderLikeButton($quote, 'sm'); ?>
            <span class="inline-flex items-center gap-1 rounded-full bg-stone-800 px-2.5 py-0.5 text-xs text-stone-400">
                <?= $commentCount ?> <?= $commentCount === 1 ? 'Antwort' : 'Antworten' ?>
            </span>
        </div>
        <?php
    }
}
