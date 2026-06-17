<?php

use App\Html;
use App\Service\AuthService;

if (!function_exists('renderCommentVote')) {
    /**
     * @param array{ id: int|string, score?: int|string, user_vote?: int|string|null } $comment
     */
    function renderCommentVote(array $comment): void
    {
        $commentId = (int) $comment['id'];
        $score = (int) ($comment['score'] ?? 0);
        $userVote = isset($comment['user_vote']) ? (int) $comment['user_vote'] : 0;
        $url = '/comments/' . $commentId . '/votes';

        $scoreClass = match (true) {
            $score > 0 => 'text-amber-400',
            $score < 0 => 'text-red-400',
            default => 'text-stone-500',
        };

        if (!AuthService::check()) {
            ?>
            <div class="flex flex-col items-center gap-0.5 shrink-0" title="Einloggen zum Voten">
                <span class="text-stone-600 text-xs leading-none">▲</span>
                <span class="text-xs font-semibold tabular-nums <?= $scoreClass ?>"><?= $score ?></span>
                <span class="text-stone-600 text-xs leading-none">▼</span>
            </div>
            <?php
            return;
        }

        $upActive = $userVote === 1;
        $downActive = $userVote === -1;
        ?>
        <div class="flex flex-col items-center gap-0.5 shrink-0">
            <button type="button"
                    data-comment-vote="<?= Html::e($url) ?>"
                    data-vote="1"
                    data-active="<?= $upActive ? '1' : '0' ?>"
                    class="text-xs leading-none px-1 rounded transition-colors cursor-pointer
                           <?= $upActive
                               ? 'text-amber-400 hover:text-amber-300'
                               : 'text-stone-500 hover:text-amber-400' ?>"
                    title="Upvote"
                    aria-label="Upvote">▲</button>
            <span class="text-xs font-semibold tabular-nums min-w-[1.25rem] text-center <?= $scoreClass ?>"
                  data-comment-score><?= $score ?></span>
            <button type="button"
                    data-comment-vote="<?= Html::e($url) ?>"
                    data-vote="-1"
                    data-active="<?= $downActive ? '1' : '0' ?>"
                    class="text-xs leading-none px-1 rounded transition-colors cursor-pointer
                           <?= $downActive
                               ? 'text-red-400 hover:text-red-300'
                               : 'text-stone-500 hover:text-red-400' ?>"
                    title="Downvote"
                    aria-label="Downvote">▼</button>
        </div>
        <?php
    }
}
