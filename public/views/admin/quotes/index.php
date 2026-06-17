<?php

use App\Html;

require __DIR__ . '/../../partials/delete-button.php';

/** @var string $title */
/** @var list<array<string, mixed>> $quotes */
?>
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <h1 class="text-2xl font-bold text-stone-100">Zitat-Verwaltung</h1>
    <div class="flex gap-2">
        <a href="/admin/quotes/new" class="px-4 py-2 rounded-lg bg-amber-600 hover:bg-amber-500 text-stone-950 font-medium text-sm transition-colors">Neues Zitat</a>
        <a href="/" class="px-4 py-2 rounded-lg border border-stone-700 text-stone-300 hover:border-stone-600 text-sm transition-colors">Zur Startseite</a>
    </div>
</div>

<div class="overflow-x-auto rounded-2xl border border-stone-800">
    <table class="w-full text-sm text-left">
        <thead class="bg-stone-900 text-stone-400 uppercase text-xs tracking-wider">
            <tr>
                <th class="px-4 py-3">ID</th>
                <th class="px-4 py-3">Zitat</th>
                <th class="px-4 py-3">Sprecher</th>
                <th class="px-4 py-3">Bild</th>
                <th class="px-4 py-3">Aktionen</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-800">
            <?php foreach ($quotes as $quote): ?>
                <tr class="hover:bg-stone-900/50">
                    <td class="px-4 py-3 text-stone-500"><?= (int) $quote['id'] ?></td>
                    <td class="px-4 py-3 text-stone-300 max-w-xs truncate"><?= Html::e(mb_strlen($quote['text']) > 60 ? mb_substr($quote['text'], 0, 60) . '…' : $quote['text']) ?></td>
                    <td class="px-4 py-3 text-amber-500/90"><?= Html::e($quote['speaker']) ?></td>
                    <td class="px-4 py-3 text-stone-500"><?= !empty($quote['image_path']) ? '✓' : '—' ?></td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-2">
                            <a href="/quotes/<?= (int) $quote['id'] ?>" class="px-2 py-1 text-xs rounded-md border border-stone-700 text-stone-400 hover:text-amber-400">Ansehen</a>
                            <a href="/admin/quotes/<?= (int) $quote['id'] ?>/edit" class="px-2 py-1 text-xs rounded-md border border-stone-700 text-stone-400 hover:text-amber-400">Bearbeiten</a>
                            <?php renderDeleteButton('/admin/quotes/' . (int) $quote['id'], 'Löschen', 'Zitat und alle Kommentare löschen?'); ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
