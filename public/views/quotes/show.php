<?php

use App\Csrf;
use App\Html;
use App\Service\AuthService;

require_once __DIR__ . '/../partials/comment-tree.php';

/** @var string $title */
/** @var array<string, mixed> $quote */
/** @var list<array<string, mixed>> $commentTree */
/** @var int $commentCount */
/** @var list<string> $commentErrors */
/** @var string $oldComment */
/** @var int|null $replyToId */
?>
<a href="/" class="inline-flex items-center gap-1 text-sm text-stone-500 hover:text-amber-400 transition-colors mb-6">← Zurück zum Feed</a>

<article class="rounded-2xl border border-stone-800 bg-gradient-to-b from-stone-900/80 to-stone-950 overflow-hidden shadow-xl shadow-black/30 mb-10">
    <?php if (!empty($quote['image_path'])): ?>
        <div class="aspect-[21/9] max-h-72 overflow-hidden">
            <img src="<?= Html::e($quote['image_path']) ?>" alt=""
                 class="w-full h-full object-cover">
        </div>
    <?php endif; ?>
    <div class="p-6 sm:p-10">
        <div class="text-6xl text-amber-800/40 quote-serif leading-none select-none mb-2">“</div>
        <blockquote class="quote-serif text-2xl sm:text-3xl text-stone-100 leading-relaxed italic -mt-8">
            <?= Html::e($quote['text']) ?>
        </blockquote>
        <footer class="mt-6 pt-6 border-t border-stone-800 flex flex-wrap items-center gap-3 text-stone-400">
            <span class="text-amber-500 font-medium text-lg">— <?= Html::e($quote['speaker']) ?></span>
            <?php if (!empty($quote['season']) && !empty($quote['episode'])): ?>
                <span class="text-sm rounded-full bg-stone-800 px-3 py-1">Staffel <?= (int) $quote['season'] ?> · Episode <?= (int) $quote['episode'] ?></span>
            <?php endif; ?>
        </footer>
    </div>
</article>

<section>
    <h2 class="text-xl font-semibold text-stone-200 mb-6 flex items-center gap-2">
        Diskussion
        <span class="text-sm font-normal text-stone-500">(<?= (int) $commentCount ?>)</span>
    </h2>

    <?php if ($commentTree === []): ?>
        <div class="rounded-xl border border-dashed border-stone-700 px-6 py-10 text-center text-stone-500 mb-8">
            <?php if (AuthService::check()): ?>
                Noch keine Kommentare. Sei der Erste!
            <?php else: ?>
                Noch keine Kommentare.
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="space-y-1 mb-8">
            <?php renderCommentTree($commentTree); ?>
        </div>
    <?php endif; ?>
</section>

<?php if (AuthService::check()): ?>
    <section class="rounded-2xl border border-stone-800 bg-stone-900/50 p-6">
        <h2 class="text-lg font-medium text-stone-200 mb-4">Neuer Kommentar</h2>
        <?php if ($commentErrors !== []): ?>
            <ul class="mb-4 space-y-1 text-sm text-red-400">
                <?php foreach ($commentErrors as $error): ?>
                    <li><?= Html::e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <form method="post" action="/quotes/<?= (int) $quote['id'] ?>/comments" class="space-y-4">
            <?= Csrf::field() ?>
            <textarea id="content" name="content" rows="4" maxlength="1000" required
                      placeholder="Was denkst du über dieses Zitat?"
                      class="w-full rounded-xl border border-stone-700 bg-stone-950 px-4 py-3 text-stone-100 placeholder-stone-600 focus:border-amber-600 focus:ring-1 focus:ring-amber-600 outline-none resize-y"><?= Html::e($oldComment) ?></textarea>
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-stone-950 font-semibold transition-colors shadow-md shadow-amber-900/30 cursor-pointer">
                Absenden
            </button>
        </form>
    </section>
<?php else: ?>
    <p class="text-center text-stone-500 py-6">
        <a href="/login" class="text-amber-500 hover:text-amber-400">Einloggen</a>, um mitzudiskutieren.
    </p>
<?php endif; ?>
