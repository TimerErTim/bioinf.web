<?php

use App\Csrf;
use App\Html;

/** @var string $title */
/** @var array<string, mixed> $comment */
/** @var list<string> $errors */
?>
<h1>Kommentar bearbeiten</h1>

<?php if ($errors !== []): ?>
    <ul class="error-list">
        <?php foreach ($errors as $error): ?>
            <li><?= Html::e($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<div class="card">
    <form method="post" action="/comments/<?= (int) $comment['id'] ?>/edit">
        <?= Csrf::field() ?>
        <div class="form-group">
            <label for="content">Kommentar</label>
            <textarea id="content" name="content" class="form-control" maxlength="1000" required><?= Html::e($comment['content']) ?></textarea>
        </div>
        <div class="page-actions">
            <button type="submit" class="btn btn-primary">Speichern</button>
            <a href="/quotes/<?= (int) $comment['quote_id'] ?>" class="btn btn-secondary">Abbrechen</a>
        </div>
    </form>
</div>
