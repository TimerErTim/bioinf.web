<?php

use App\Html;

/** @var string $title */
/** @var list<array<string, mixed>> $quotes */
/** @var int $page */
/** @var int $totalPages */
/** @var int $total */
?>
<div class="mb-8">
    <h1 class="text-3xl font-bold text-stone-100 tracking-tight">Zitate-Forum</h1>
    <p class="mt-2 text-stone-400"><?= (int) $total ?> Beiträge · Diskutiere die Weisheiten von Westeros</p>
</div>

<?php if ($quotes === []): ?>
    <div class="rounded-2xl border border-dashed border-stone-700 bg-stone-900/50 px-6 py-16 text-center text-stone-500">
        Noch keine Zitate vorhanden.
    </div>
<?php else: ?>
    <div class="space-y-4">
        <?php foreach ($quotes as $quote): ?>
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
                            „<?= Html::e(mb_strlen($quote['text']) > 160 ? mb_substr($quote['text'], 0, 160) . '…' : $quote['text']) ?>"
                        </blockquote>
                        <div class="mt-4 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-stone-500">
                            <span class="text-amber-500/90 font-medium">— <?= Html::e($quote['speaker']) ?></span>
                            <?php if (!empty($quote['season']) && !empty($quote['episode'])): ?>
                                <span>S<?= str_pad((string) $quote['season'], 2, '0', STR_PAD_LEFT) ?>E<?= str_pad((string) $quote['episode'], 2, '0', STR_PAD_LEFT) ?></span>
                            <?php endif; ?>
                            <span class="inline-flex items-center gap-1 rounded-full bg-stone-800 px-2.5 py-0.5 text-xs text-stone-400">
                                <?= (int) $quote['comment_count'] ?> <?= (int) $quote['comment_count'] === 1 ? 'Antwort' : 'Antworten' ?>
                            </span>
                        </div>
                        <a href="/quotes/<?= (int) $quote['id'] ?>"
                           class="inline-flex mt-4 items-center gap-1 text-sm font-medium text-amber-500 hover:text-amber-400 transition-colors">
                            Thread öffnen →
                        </a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <nav class="mt-8 flex items-center justify-center gap-2" aria-label="Seitennavigation">
            <?php if ($page > 1): ?>
                <a href="/?page=<?= $page - 1 ?>" class="px-4 py-2 rounded-lg border border-stone-700 text-stone-300 hover:border-amber-700 hover:text-amber-400 transition-colors">← Zurück</a>
            <?php endif; ?>
            <span class="px-4 py-2 rounded-lg bg-stone-800 text-stone-300 text-sm"><?= $page ?> / <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="/?page=<?= $page + 1 ?>" class="px-4 py-2 rounded-lg border border-stone-700 text-stone-300 hover:border-amber-700 hover:text-amber-400 transition-colors">Weiter →</a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
<?php endif; ?>
