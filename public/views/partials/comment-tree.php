<?php

use App\Csrf;
use App\Html;
use App\Service\AuthService;

require_once __DIR__ . '/avatar.php';
require_once __DIR__ . '/delete-button.php';
require_once __DIR__ . '/comment-vote.php';
require_once __DIR__ . '/format.php';

if (!function_exists('renderCommentAuthor')) {
    function renderCommentAuthor(array $comment): void
    {
        if ($comment['user_id'] === null || $comment['username'] === null) {
            echo '<span class="text-stone-500 italic">&lt;deleted&gt;</span>';
            return;
        }

        $url = userProfileUrl((int) $comment['user_id']);
        echo '<a href="' . Html::e($url) . '" class="font-medium text-stone-200 hover:text-amber-400 transition-colors">' . Html::e($comment['username']) . '</a>';
    }
}

if (!function_exists('renderCommentTree')) {
    /**
     * @param list<array<string, mixed>> $comments
     */
    function renderCommentTree(array $comments, int $depth = 0): void
    {
        $userId = AuthService::userId();
        $isAdmin = AuthService::isAdmin();
        $maxVisualDepth = 5;

        foreach ($comments as $comment) {
            $isOwner = $comment['user_id'] !== null && $userId !== null && (int) $comment['user_id'] === $userId;
            $canDelete = $isOwner || $isAdmin;
            $canEdit = $isOwner;
            $indent = min($depth, $maxVisualDepth) * 24;
            ?>
            <article id="comment-<?= (int) $comment['id'] ?>"
                     class="relative border-l-2 border-stone-800 pl-4 py-4"
                     style="margin-left: <?= $indent ?>px">
                <div class="flex gap-3">
                    <?php renderCommentVote($comment); ?>
                    <?php renderAvatar($comment, 'md', true); ?>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
                            <div class="text-sm">
                                <?php renderCommentAuthor($comment); ?>
                                <span class="text-stone-500"> · <?= formatDateTime($comment['created_at']) ?></span>
                                <?php if (!empty($comment['updated_at'])): ?>
                                    <span class="text-stone-600 text-xs italic">(bearbeitet)</span>
                                <?php endif; ?>
                            </div>
                            <div class="flex flex-wrap items-center gap-1">
                                <?php if (AuthService::check()): ?>
                                    <button type="button" data-reply-toggle="<?= (int) $comment['id'] ?>"
                                            class="px-2.5 py-1 text-xs rounded-md text-amber-500/90 hover:text-amber-400 hover:bg-amber-950/30 transition-colors cursor-pointer">
                                        Antworten
                                    </button>
                                <?php endif; ?>
                                <?php if ($canEdit): ?>
                                    <a href="/comments/<?= (int) $comment['id'] ?>/edit"
                                       class="px-2.5 py-1 text-xs rounded-md text-stone-400 hover:text-amber-400 hover:bg-stone-800 transition-colors">Bearbeiten</a>
                                <?php endif; ?>
                                <?php if ($canDelete): ?>
                                    <?php renderDeleteButton('/comments/' . (int) $comment['id'], 'Löschen', 'Kommentar wirklich löschen?'); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p class="text-stone-300 leading-relaxed whitespace-pre-wrap break-words"><?= nl2br(Html::e($comment['content'])) ?></p>

                        <?php if (AuthService::check()): ?>
                            <div id="reply-form-<?= (int) $comment['id'] ?>" class="hidden mt-4">
                                <form method="post" action="/comments/<?= (int) $comment['id'] ?>/replies" class="space-y-2">
                                    <?= Csrf::field() ?>
                                    <textarea name="content" rows="2" maxlength="1000" required
                                              placeholder="Deine Antwort…"
                                              class="w-full rounded-lg border border-stone-700 bg-stone-900/80 px-3 py-2 text-sm text-stone-100 placeholder-stone-600 focus:border-amber-600 focus:ring-1 focus:ring-amber-600 outline-none resize-y"></textarea>
                                    <button type="submit" class="px-3 py-1.5 text-xs rounded-lg bg-amber-600 hover:bg-amber-500 text-stone-950 font-medium transition-colors cursor-pointer">Antwort senden</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!empty($comment['children'])): ?>
                    <div class="mt-2">
                        <?php renderCommentTree($comment['children'], $depth + 1); ?>
                    </div>
                <?php endif; ?>
            </article>
            <?php
        }
    }
}
