<?php

use App\Csrf;
use App\Html;
use App\Service\AuthService;

require __DIR__ . '/../partials/helpers.php';

/** @var string $title */
/** @var array<string, mixed> $quote */
/** @var list<array<string, mixed>> $comments */
/** @var list<string> $commentErrors */
/** @var string $oldComment */

$userId = AuthService::userId();
$isAdmin = AuthService::isAdmin();
?>
<p><a href="/">← Zurück zur Übersicht</a></p>

<article class="card quote-detail">
    <blockquote>„<?= Html::e($quote['text']) ?>"</blockquote>
    <p class="quote-meta">
        von <?= Html::e($quote['speaker']) ?>
        <?php if (!empty($quote['season']) && !empty($quote['episode'])): ?>
            (S<?= str_pad((string) $quote['season'], 2, '0', STR_PAD_LEFT) ?>E<?= str_pad((string) $quote['episode'], 2, '0', STR_PAD_LEFT) ?>)
        <?php endif; ?>
    </p>
</article>

<section>
    <h2>Kommentare (<?= count($comments) ?>)</h2>

    <?php if ($comments === []): ?>
        <p class="empty-state">
            <?php if (AuthService::check()): ?>
                Noch keine Kommentare. Sei der Erste!
            <?php else: ?>
                Noch keine Kommentare.
            <?php endif; ?>
        </p>
    <?php else: ?>
        <?php foreach ($comments as $comment): ?>
            <?php
            $isOwner = $comment['user_id'] !== null && $userId !== null && (int) $comment['user_id'] === $userId;
            $canDelete = $isOwner || $isAdmin;
            $canEdit = $isOwner;
            ?>
            <div class="comment">
                <div class="comment-header">
                    <span>
                        <?php renderCommentAuthor($comment); ?>
                        · <?= formatDateTime($comment['created_at']) ?>
                        <?php if (!empty($comment['updated_at'])): ?>
                            <em>(bearbeitet <?= formatDateTime($comment['updated_at']) ?>)</em>
                        <?php endif; ?>
                    </span>
                    <?php if ($canEdit || $canDelete): ?>
                        <div class="comment-actions">
                            <?php if ($canEdit): ?>
                                <a href="/comments/<?= (int) $comment['id'] ?>/edit" class="btn btn-secondary btn-sm">Bearbeiten</a>
                            <?php endif; ?>
                            <?php if ($canDelete): ?>
                                <form method="post" action="/comments/<?= (int) $comment['id'] ?>/delete"
                                      onsubmit="return confirm('Kommentar wirklich löschen?');">
                                    <?= Csrf::field() ?>
                                    <button type="submit" class="btn btn-danger btn-sm">Löschen</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <p><?= nl2br(Html::e($comment['content'])) ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<?php if (AuthService::check()): ?>
    <section>
        <h2>Neuer Kommentar</h2>
        <?php if ($commentErrors !== []): ?>
            <ul class="error-list">
                <?php foreach ($commentErrors as $error): ?>
                    <li><?= Html::e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <div class="card">
            <form method="post" action="/quotes/<?= (int) $quote['id'] ?>/comments">
                <?= Csrf::field() ?>
                <div class="form-group">
                    <label for="content">Dein Kommentar</label>
                    <textarea id="content" name="content" class="form-control" maxlength="1000" required><?= Html::e($oldComment) ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Absenden</button>
            </form>
        </div>
    </section>
<?php else: ?>
    <p class="quote-meta"><a href="/login">Einloggen</a>, um einen Kommentar zu schreiben.</p>
<?php endif; ?>
