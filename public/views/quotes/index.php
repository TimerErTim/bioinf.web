<?php

use App\Html;

/** @var string $title */
/** @var list<array<string, mixed>> $quotes */
/** @var int $page */
/** @var int $totalPages */
/** @var int $total */
?>
<h1>Game of Thrones Zitate</h1>
<p class="quote-meta"><?= (int) $total ?> Zitate</p>

<?php if ($quotes === []): ?>
    <p class="empty-state">Noch keine Zitate vorhanden.</p>
<?php else: ?>
    <?php foreach ($quotes as $quote): ?>
        <article class="card">
            <p class="quote-excerpt">
                „<?= Html::e(mb_strlen($quote['text']) > 120 ? mb_substr($quote['text'], 0, 120) . '…' : $quote['text']) ?>"
            </p>
            <p class="quote-meta">
                — <?= Html::e($quote['speaker']) ?>
                <?php if (!empty($quote['season']) && !empty($quote['episode'])): ?>
                    (S<?= str_pad((string) $quote['season'], 2, '0', STR_PAD_LEFT) ?>E<?= str_pad((string) $quote['episode'], 2, '0', STR_PAD_LEFT) ?>)
                <?php endif; ?>
                · <?= (int) $quote['comment_count'] ?> Kommentar<?= (int) $quote['comment_count'] === 1 ? '' : 'e' ?>
            </p>
            <a href="/quotes/<?= (int) $quote['id'] ?>" class="btn btn-secondary btn-sm">Details ansehen</a>
        </article>
    <?php endforeach; ?>

    <?php if ($totalPages > 1): ?>
        <nav class="pagination" aria-label="Seitennavigation">
            <?php if ($page > 1): ?>
                <a href="/?page=<?= $page - 1 ?>" class="btn btn-secondary">← Zurück</a>
            <?php endif; ?>
            <span class="btn active"><?= $page ?> / <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="/?page=<?= $page + 1 ?>" class="btn btn-secondary">Weiter →</a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
<?php endif; ?>
