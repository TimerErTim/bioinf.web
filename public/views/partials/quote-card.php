<?php

use App\Html;

require_once __DIR__ . '/like-button.php';

if (!function_exists('renderQuoteCard')) {
    /**
     * @param array<string, mixed> $quote
     */
    function renderQuoteCard(array $quote, bool $compact = false): void
    {
        $excerptLen = $compact ? 100 : 160;
        $text = $quote['text'] ?? '';
        $excerpt = mb_strlen($text) > $excerptLen ? mb_substr($text, 0, $excerptLen) . '…' : $text;
        ?>
        <article class="group rounded-2xl border border-stone-800 bg-stone-900/40 hover:bg-stone-900/70 hover:border-stone-700 transition-all duration-200 overflow-hidden shadow-lg shadow-black/20">
            <div class="flex flex-col sm:flex-row">
                <?php if (!empty($quote['image_path'])): ?>
                    <div class="sm:w-48 h-36 sm:h-auto shrink-0">
                        <img src="<?= Html::e($quote['image_path']) ?>" alt=""
                             class="w-full h-full object-cover">
                    </div>
                <?php endif; ?>
                <div class="flex-1 p-5 sm:p-6">
                    <blockquote class="quote-serif text-lg sm:text-xl text-stone-200 leading-relaxed italic">
                        „<?= Html::e($excerpt) ?>"
                    </blockquote>
                    <div class="mt-4 flex flex-wrap items-center gap-x-3 gap-y-2 text-sm text-stone-500">
                        <span class="text-amber-500/90 font-medium">— <?= Html::e($quote['speaker']) ?></span>
                        <?php if (!empty($quote['season']) && !empty($quote['episode'])): ?>
                            <span>S<?= str_pad((string) $quote['season'], 2, '0', STR_PAD_LEFT) ?>E<?= str_pad((string) $quote['episode'], 2, '0', STR_PAD_LEFT) ?></span>
                        <?php endif; ?>
                        <?php renderQuoteStats($quote); ?>
                    </div>
                    <a href="/quotes/<?= (int) $quote['id'] ?>"
                       class="inline-flex mt-4 items-center gap-1 text-sm font-medium text-amber-500 hover:text-amber-400 transition-colors">
                        Thread öffnen →
                    </a>
                </div>
            </div>
        </article>
        <?php
    }
}
