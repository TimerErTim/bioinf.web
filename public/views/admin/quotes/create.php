<?php

use App\Csrf;
use App\Html;

/** @var string $title */
/** @var array<string, mixed> $quote */
/** @var list<string> $errors */
?>
<h1>Neues Zitat</h1>

<?php if ($errors !== []): ?>
    <ul class="error-list">
        <?php foreach ($errors as $error): ?>
            <li><?= Html::e($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<div class="card">
    <?php require __DIR__ . '/_form.php'; ?>
</div>

<p><a href="/admin/quotes">← Zurück zur Liste</a></p>
