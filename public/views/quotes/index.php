<?php

use App\Html;

require_once __DIR__ . '/../partials/quote-card.php';

/** @var string $title */
/** @var list<array<string, mixed>> $quotes */
/** @var int $page */
/** @var int $totalPages */
/** @var int $total */
/** @var string $sort */

$sorts = [
    'new' => 'Neu',
    'top' => 'Top',
    'trending' => 'Trending',
];

function sortUrl(string $sort, int $page = 1): string
{
    return '/?sort=' . urlencode($sort) . ($page > 1 ? '&page=' . $page : '');
}
?>
<div class="mb-8 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
    <div>
        <h1 class="text-3xl font-bold text-stone-100 tracking-tight">Zitate-Forum</h1>
        <p class="mt-2 text-stone-400"><?= (int) $total ?> Beiträge · Diskutiere die Weisheiten von Westeros</p>
    </div>
    <nav class="flex flex-wrap gap-1 rounded-xl border border-stone-800 bg-stone-900/50 p-1" aria-label="Sortierung">
        <?php foreach ($sorts as $key => $label): ?>
            <a href="<?= Html::e(sortUrl($key)) ?>"
               class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                      <?= $sort === $key
                          ? 'bg-amber-600 text-stone-950'
                          : 'text-stone-400 hover:text-stone-200 hover:bg-stone-800' ?>">
                <?= Html::e($label) ?>
            </a>
        <?php endforeach; ?>
    </nav>
</div>

<?php if ($sort === 'top'): ?>
    <p class="mb-4 text-xs text-stone-500">Sortiert nach den meisten Likes.</p>
<?php elseif ($sort === 'trending'): ?>
    <p class="mb-4 text-xs text-stone-500">Trending: Likes &amp; Kommentare der letzten 7 Tage.</p>
<?php endif; ?>

<?php if ($quotes === []): ?>
    <div class="rounded-2xl border border-dashed border-stone-700 bg-stone-900/50 px-6 py-16 text-center text-stone-500">
        Noch keine Zitate vorhanden.
    </div>
<?php else: ?>
    <div class="space-y-4">
        <?php foreach ($quotes as $quote): ?>
            <?php renderQuoteCard($quote); ?>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <nav class="mt-8 flex items-center justify-center gap-2" aria-label="Seitennavigation">
            <?php if ($page > 1): ?>
                <a href="<?= Html::e(sortUrl($sort, $page - 1)) ?>" class="px-4 py-2 rounded-lg border border-stone-700 text-stone-300 hover:border-amber-700 hover:text-amber-400 transition-colors">← Zurück</a>
            <?php endif; ?>
            <span class="px-4 py-2 rounded-lg bg-stone-800 text-stone-300 text-sm"><?= $page ?> / <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="<?= Html::e(sortUrl($sort, $page + 1)) ?>" class="px-4 py-2 rounded-lg border border-stone-700 text-stone-300 hover:border-amber-700 hover:text-amber-400 transition-colors">Weiter →</a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
<?php endif; ?>
