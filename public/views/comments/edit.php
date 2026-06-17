<?php

use App\Csrf;
use App\Html;

/** @var string $title */
/** @var array<string, mixed> $comment */
/** @var list<string> $errors */
?>
<h1 class="text-2xl font-bold text-stone-100 mb-6">Kommentar bearbeiten</h1>

<?php if ($errors !== []): ?>
    <ul class="mb-4 space-y-1 text-sm text-red-400 rounded-xl border border-red-900/50 bg-red-950/30 px-4 py-3">
        <?php foreach ($errors as $error): ?>
            <li><?= Html::e($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<div class="rounded-2xl border border-stone-800 bg-stone-900/50 p-6 max-w-2xl">
    <form method="post" action="/comments/<?= (int) $comment['id'] ?>" class="space-y-4">
        <?= Csrf::field() ?>
        <input type="hidden" name="_method" value="PUT">
        <div>
            <label for="content" class="block text-sm font-medium text-stone-300 mb-1">Kommentar</label>
            <textarea id="content" name="content" rows="5" maxlength="1000" required
                      class="w-full rounded-xl border border-stone-700 bg-stone-950 px-4 py-3 text-stone-100 focus:border-amber-600 focus:ring-1 focus:ring-amber-600 outline-none resize-y"><?= Html::e($comment['content']) ?></textarea>
        </div>
        <div class="flex flex-wrap gap-3">
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-stone-950 font-semibold transition-colors cursor-pointer">Speichern</button>
            <a href="/quotes/<?= (int) $comment['quote_id'] ?>" class="px-5 py-2.5 rounded-xl border border-stone-700 text-stone-300 hover:border-stone-600 transition-colors">Abbrechen</a>
        </div>
    </form>
</div>
