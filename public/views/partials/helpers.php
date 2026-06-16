<?php

use App\Html;

/**
 * Renders comment author — shows gray &lt;deleted&gt; when user was removed.
 */
function renderCommentAuthor(array $comment): void
{
    if ($comment['user_id'] === null || $comment['username'] === null) {
        echo '<span class="comment-author-deleted">&lt;deleted&gt;</span>';
        return;
    }

    echo Html::e($comment['username']);
}

function formatDateTime(?string $datetime): string
{
    if ($datetime === null) {
        return '';
    }

    $timestamp = strtotime($datetime);
    if ($timestamp === false) {
        return Html::e($datetime);
    }

    return date('d.m.Y H:i', $timestamp);
}
