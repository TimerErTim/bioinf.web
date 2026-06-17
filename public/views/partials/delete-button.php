<?php

use App\Html;

if (!function_exists('renderDeleteButton')) {
    function renderDeleteButton(string $url, string $label = 'Löschen', string $confirm = 'Wirklich löschen?'): void
    {
        ?>
        <button type="button"
                data-delete="<?= Html::e($url) ?>"
                data-confirm="<?= Html::e($confirm) ?>"
                class="px-2.5 py-1 text-xs rounded-md text-red-400 hover:text-red-300 hover:bg-red-950/50 border border-transparent hover:border-red-900/50 transition-colors cursor-pointer">
            <?= Html::e($label) ?>
        </button>
        <?php
    }
}

if (!function_exists('renderPatchButton')) {
    function renderPatchButton(string $url, string $label, ?string $confirm = null): void
    {
        ?>
        <button type="button"
                data-patch="<?= Html::e($url) ?>"
                <?php if ($confirm !== null): ?>data-confirm="<?= Html::e($confirm) ?>"<?php endif; ?>
                class="px-2.5 py-1 text-xs rounded-md text-stone-300 hover:text-amber-400 hover:bg-stone-800 border border-stone-700 transition-colors cursor-pointer">
            <?= Html::e($label) ?>
        </button>
        <?php
    }
}
