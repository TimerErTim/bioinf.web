<?php

use App\Csrf;
use App\Html;

/** @var array<string, mixed> $quote */
$isEdit = isset($quote['id']);
$action = $isEdit ? '/admin/quotes/' . (int) $quote['id'] : '/admin/quotes';
?>
<form method="post" action="<?= Html::e($action) ?>" enctype="multipart/form-data" class="space-y-4">
    <?= Csrf::field() ?>
    <div>
        <label for="text" class="block text-sm font-medium text-stone-300 mb-1">Zitat-Text</label>
        <textarea id="text" name="text" maxlength="2000" required rows="5"
                  class="w-full rounded-xl border border-stone-700 bg-stone-950 px-4 py-3 text-stone-100 focus:border-amber-600 focus:ring-1 focus:ring-amber-600 outline-none resize-y"><?= Html::e($quote['text'] ?? '') ?></textarea>
    </div>
    <div>
        <label for="speaker" class="block text-sm font-medium text-stone-300 mb-1">Sprecher</label>
        <input type="text" id="speaker" name="speaker" maxlength="100" required
               value="<?= Html::e($quote['speaker'] ?? '') ?>"
               class="w-full rounded-lg border border-stone-700 bg-stone-950 px-3 py-2 text-stone-100 focus:border-amber-600 focus:ring-1 focus:ring-amber-600 outline-none">
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="season" class="block text-sm font-medium text-stone-300 mb-1">Staffel (optional)</label>
            <input type="number" id="season" name="season" min="1" max="255"
                   value="<?= Html::e((string) ($quote['season'] ?? '')) ?>"
                   class="w-full rounded-lg border border-stone-700 bg-stone-950 px-3 py-2 text-stone-100 focus:border-amber-600 outline-none">
        </div>
        <div>
            <label for="episode" class="block text-sm font-medium text-stone-300 mb-1">Episode (optional)</label>
            <input type="number" id="episode" name="episode" min="1" max="255"
                   value="<?= Html::e((string) ($quote['episode'] ?? '')) ?>"
                   class="w-full rounded-lg border border-stone-700 bg-stone-950 px-3 py-2 text-stone-100 focus:border-amber-600 outline-none">
        </div>
    </div>
    <div>
        <label for="image" class="block text-sm font-medium text-stone-300 mb-1">Beitragsbild (optional)</label>
        <?php if (!empty($quote['image_path'])): ?>
            <img src="<?= Html::e($quote['image_path']) ?>" alt="" class="mb-2 h-24 rounded-lg object-cover">
            <label class="flex items-center gap-2 text-sm text-stone-400 mb-2">
                <input type="checkbox" name="remove_image" value="1" class="rounded border-stone-600">
                Bild entfernen
            </label>
        <?php endif; ?>
        <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp"
               class="w-full text-sm text-stone-400 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-stone-800 file:text-stone-200">
    </div>
    <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-stone-950 font-semibold transition-colors cursor-pointer">
        <?= $isEdit ? 'Speichern' : 'Anlegen' ?>
    </button>
</form>
