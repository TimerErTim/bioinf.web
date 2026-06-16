<?php

declare(strict_types=1);

namespace App\Controller;

use App\Database;
use App\Flash;
use App\Model\Comment;
use App\Model\Quote;
use App\Response;
use App\Service\AuthService;
use App\Service\ValidationService;
use App\View;

/**
 * Comment CRUD for authenticated users.
 */
final class CommentController
{
    private Quote $quotes;
    private Comment $comments;

    public function __construct(array $config)
    {
        $pdo = Database::connection($config['db']);
        $this->quotes = new Quote($pdo);
        $this->comments = new Comment($pdo);
    }

    public function store(string $id): void
    {
        Response::requirePost();
        Response::requireCsrf();
        AuthService::requireLogin();

        $quoteId = (int) $id;
        $quote = $this->quotes->findById($quoteId);
        if ($quote === null) {
            Response::notFound();
        }

        $content = trim($_POST['content'] ?? '');
        $errors = ValidationService::commentContent($content);

        if ($errors !== []) {
            View::render('quotes/show', [
                'title' => 'Quote by ' . $quote['speaker'],
                'quote' => $quote,
                'comments' => $this->comments->findByQuoteId($quoteId),
                'commentErrors' => $errors,
                'oldComment' => $content,
            ]);
            return;
        }

        $userId = AuthService::userId();
        $this->comments->create($quoteId, $userId, $content);
        Flash::success('Comment added.');
        View::redirect('/quotes/' . $quoteId);
    }

    public function edit(string $id): void
    {
        AuthService::requireLogin();

        $comment = $this->loadOwnedComment((int) $id);
        View::render('comments/edit', [
            'title' => 'Edit Comment',
            'comment' => $comment,
            'errors' => [],
        ]);
    }

    public function update(string $id): void
    {
        Response::requirePost();
        Response::requireCsrf();
        AuthService::requireLogin();

        $comment = $this->loadOwnedComment((int) $id);
        $content = trim($_POST['content'] ?? '');
        $errors = ValidationService::commentContent($content);

        if ($errors !== []) {
            View::render('comments/edit', [
                'title' => 'Edit Comment',
                'comment' => array_merge($comment, ['content' => $content]),
                'errors' => $errors,
            ]);
            return;
        }

        $this->comments->update((int) $comment['id'], $content);
        Flash::success('Comment updated.');
        View::redirect('/quotes/' . $comment['quote_id']);
    }

    public function delete(string $id): void
    {
        Response::requirePost();
        Response::requireCsrf();
        AuthService::requireLogin();

        $comment = $this->comments->findById((int) $id);
        if ($comment === null) {
            Response::notFound();
        }

        $userId = AuthService::userId();
        $isOwner = $comment['user_id'] !== null && (int) $comment['user_id'] === $userId;
        $isAdmin = AuthService::isAdmin();

        if (!$isOwner && !$isAdmin) {
            Response::forbidden();
        }

        $this->comments->delete((int) $comment['id']);
        Flash::success('Comment deleted.');
        View::redirect('/quotes/' . $comment['quote_id']);
    }

    /** @return array<string, mixed> */
    private function loadOwnedComment(int $commentId): array
    {
        $comment = $this->comments->findById($commentId);
        if ($comment === null) {
            Response::notFound();
        }

        if ($comment['user_id'] === null || (int) $comment['user_id'] !== AuthService::userId()) {
            Response::forbidden();
        }

        return $comment;
    }
}
