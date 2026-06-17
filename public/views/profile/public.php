<?php

use App\Html;

require_once __DIR__ . '/../partials/avatar.php';
require_once __DIR__ . '/../partials/quote-card.php';
require_once __DIR__ . '/../partials/centered-page.php';

/** @var string $title */
/** @var array<string, mixed> $user */
/** @var int $commentCount */
/** @var int $commentScore */
/** @var int $likeCount */
/** @var list<array<string, mixed>> $comments */
/** @var list<array<string, mixed>> $likedQuotes */
/** @var bool $isOwnProfile */

if (!function_exists('formatProfileDate')) {
    function formatProfileDate(?string $datetime): string
    {
        if ($datetime === null) {
            return '';
        }
        $ts = strtotime($datetime);
        return $ts !== false ? date('d.m.Y H:i', $ts) : Html::e($datetime);
    }
}

if (!function_exists('formatProfileScore')) {
    function formatProfileScore(int $score): string
    {
        return $score > 0 ? '+' . $score : (string) $score;
    }

    function profileScoreClass(int $score): string
    {
        return match (true) {
            $score > 0 => 'text-amber-400 bg-amber-950/40 border-amber-800/50',
            $score < 0 => 'text-red-400 bg-red-950/40 border-red-800/50',
            default => 'text-stone-400 bg-stone-800/60 border-stone-700',
        };
    }
}
?>
<?php openCenteredPage('xl'); ?>
<div class="rounded-2xl border border-stone-800 bg-stone-900/50 p-8 sm:p-10 shadow-xl shadow-black/20 text-center mb-8">
    <div class="flex justify-center mb-4">
        <?php renderAvatar($user, 'lg'); ?>
    </div>
    <h1 class="text-2xl font-bold text-stone-100"><?= Html::e($user['username']) ?></h1>
    <?php if ((bool) $user['is_admin']): ?>
        <span class="inline-flex mt-2 px-2.5 py-0.5 rounded-full text-xs bg-amber-950 text-amber-400 border border-amber-800/50">Admin</span>
    <?php endif; ?>
    <p class="mt-3 text-sm text-stone-500">Mitglied seit <?= Html::e(date('d.m.Y', strtotime($user['created_at']))) ?></p>
    <div class="mt-2 flex flex-wrap justify-center gap-x-4 gap-y-2 text-sm text-stone-400">
        <span><?= (int) $commentCount ?> <?= (int) $commentCount === 1 ? 'Kommentar' : 'Kommentare' ?></span>
        <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold tabular-nums <?= profileScoreClass((int) $commentScore) ?>">
            Score <?= formatProfileScore((int) $commentScore) ?>
        </span>
        <span>
            <?= (int) $likeCount ?> <?= (int) $likeCount === 1 ? 'geliktes Zitat' : 'gelikte Zitate' ?>
        </span>
    </div>

    <?php if ($isOwnProfile): ?>
        <a href="/profile" class="inline-flex mt-6 px-4 py-2 rounded-lg bg-amber-600 hover:bg-amber-500 text-stone-950 font-medium text-sm transition-colors">
            Profil bearbeiten
        </a>
    <?php endif; ?>
</div>

<section class="mb-10 text-left">
    <h2 class="text-lg font-semibold text-stone-200 mb-4">Kommentare</h2>
    <?php if ($comments === []): ?>
        <p class="text-sm text-stone-500 rounded-xl border border-dashed border-stone-700 px-4 py-8 text-center">Noch keine Kommentare geschrieben.</p>
    <?php else: ?>
        <ul class="space-y-3">
            <?php foreach ($comments as $comment): ?>
                <?php $score = (int) ($comment['score'] ?? 0); ?>
                <li class="rounded-xl border border-stone-800 bg-stone-900/40 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-stone-300 text-sm leading-relaxed whitespace-pre-wrap break-words flex-1"><?= nl2br(Html::e($comment['content'])) ?></p>
                        <span class="shrink-0 inline-flex flex-col items-center rounded-lg border px-2 py-1 text-xs font-semibold tabular-nums <?= profileScoreClass($score) ?>"
                              title="Upvotes: <?= (int) ($comment['upvotes'] ?? 0) ?> · Downvotes: <?= (int) ($comment['downvotes'] ?? 0) ?>">
                            <span class="text-[10px] font-normal opacity-70">Score</span>
                            <?= formatProfileScore($score) ?>
                        </span>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-stone-500">
                        <span><?= formatProfileDate($comment['created_at']) ?></span>
                        <span>·</span>
                        <a href="/quotes/<?= (int) $comment['quote_id'] ?>" class="text-amber-500/90 hover:text-amber-400 transition-colors">
                            zu „<?= Html::e(mb_strlen($comment['quote_text']) > 50 ? mb_substr($comment['quote_text'], 0, 50) . '…' : $comment['quote_text']) ?>"
                            — <?= Html::e($comment['quote_speaker']) ?>
                        </a>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<section class="mb-8 text-left">
    <h2 class="text-lg font-semibold text-stone-200 mb-4">Gelikte Zitate</h2>
    <?php if ($likedQuotes === []): ?>
        <p class="text-sm text-stone-500 rounded-xl border border-dashed border-stone-700 px-4 py-8 text-center">Noch keine Zitate geliked.</p>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($likedQuotes as $quote): ?>
                <?php renderQuoteCard($quote, true); ?>
                <p class="text-xs text-stone-600 -mt-2 ml-1">Geliked am <?= formatProfileDate($quote['liked_at'] ?? null) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<p class="text-center">
    <a href="/" class="text-sm text-stone-500 hover:text-amber-400">← Zurück zum Feed</a>
</p>
<?php closeCenteredPage(); ?>
