<?php

use App\Csrf;
use App\Html;

/** @var string $title */
/** @var list<array<string, mixed>> $quotes */
?>
<h1>Zitat-Verwaltung</h1>

<p class="page-actions">
    <a href="/admin/quotes/create" class="btn btn-primary">Neues Zitat</a>
    <a href="/" class="btn btn-secondary">Zur Startseite</a>
</p>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Zitat</th>
                <th>Sprecher</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($quotes as $quote): ?>
                <tr>
                    <td><?= (int) $quote['id'] ?></td>
                    <td><?= Html::e(mb_strlen($quote['text']) > 60 ? mb_substr($quote['text'], 0, 60) . '…' : $quote['text']) ?></td>
                    <td><?= Html::e($quote['speaker']) ?></td>
                    <td>
                        <a href="/quotes/<?= (int) $quote['id'] ?>" class="btn btn-secondary btn-sm">Ansehen</a>
                        <a href="/admin/quotes/<?= (int) $quote['id'] ?>/edit" class="btn btn-secondary btn-sm">Bearbeiten</a>
                        <form class="inline-form" method="post"
                              action="/admin/quotes/<?= (int) $quote['id'] ?>/delete"
                              onsubmit="return confirm('Zitat und alle Kommentare löschen?');">
                            <?= Csrf::field() ?>
                            <button type="submit" class="btn btn-danger btn-sm">Löschen</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
