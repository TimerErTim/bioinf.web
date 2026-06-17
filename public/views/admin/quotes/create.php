<?php

use App\Html;

/** @var string $title */
/** @var array<string, mixed> $quote */
/** @var list<string> $errors */
?>
<h1 class="text-2xl font-bold text-stone-100 mb-6">Neues Zitat</h1>

<?php if ($errors !== []): ?>
    <ul class="mb-4 space-y-1 text-sm text-red-400 rounded-xl border border-red-900/50 bg-red-950/30 px-4 py-3">
        <?php foreach ($errors as $error): ?>
            <li><?= Html::e($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<div class="rounded-2xl border border-stone-800 bg-stone-900/50 p-6 max-w-2xl">
    <?php require __DIR__ . '/_form.php'; ?>
</div>

<p class="mt-4"><a href="/admin/quotes" class="text-sm text-stone-500 hover:text-amber-400">← Zurück zur Liste</a></p>
