<?php

use App\Csrf;
use App\Html;

/** @var array<string, mixed> $quote */
$isEdit = isset($quote['id']);
$action = $isEdit
    ? '/admin/quotes/' . (int) $quote['id'] . '/edit'
    : '/admin/quotes/create';
?>
<form method="post" action="<?= Html::e($action) ?>">
    <?= Csrf::field() ?>
    <div class="form-group">
        <label for="text">Zitat-Text</label>
        <textarea id="text" name="text" class="form-control" maxlength="2000" required><?= Html::e($quote['text'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
        <label for="speaker">Sprecher</label>
        <input type="text" id="speaker" name="speaker" class="form-control"
               value="<?= Html::e($quote['speaker'] ?? '') ?>" maxlength="100" required>
    </div>
    <div class="form-group">
        <label for="season">Staffel (optional)</label>
        <input type="number" id="season" name="season" class="form-control" min="1" max="255"
               value="<?= Html::e((string) ($quote['season'] ?? '')) ?>">
    </div>
    <div class="form-group">
        <label for="episode">Episode (optional)</label>
        <input type="number" id="episode" name="episode" class="form-control" min="1" max="255"
               value="<?= Html::e((string) ($quote['episode'] ?? '')) ?>">
    </div>
    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Speichern' : 'Anlegen' ?></button>
</form>
