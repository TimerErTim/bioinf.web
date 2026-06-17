<?php

declare(strict_types=1);

namespace App\Controller;

use App\Database;
use App\Flash;
use App\Model\Comment;
use App\Model\CommentVote;
use App\Model\Quote;
use App\Response;
use App\Service\AuthService;
use App\Service\ValidationService;
use App\View;

final class CommentController
{
    private Quote $quotes;
    private Comment $comments;
    private CommentVote $votes;

    public function __construct(array $config)
    {
        $pdo = Database::connection($config['db']);
        $this->quotes = new Quote($pdo);
        $this->comments = new Comment($pdo);
        $this->votes = new CommentVote($pdo);
    }

    public function store(string $quoteId): void
    {
        Response::requirePost();
        Response::requireCsrf();
        AuthService::requireLogin();

        $qid = (int) $quoteId;
        $quote = $this->quotes->findById($qid);
        if ($quote === null) {
            Response::notFound();
        }

        $this->saveComment($qid, null);
    }

    public function reply(string $parentId): void
    {
        Response::requirePost();
        Response::requireCsrf();
        AuthService::requireLogin();

        $parent = $this->comments->findById((int) $parentId);
        if ($parent === null) {
            Response::notFound();
        }

        $this->saveComment((int) $parent['quote_id'], (int) $parent['id']);
    }

    public function setVote(string $id): void
    {
        Response::requirePost();
        Response::requireCsrf();
        AuthService::requireLogin();

        $commentId = (int) $id;
        $comment = $this->comments->findById($commentId);
        if ($comment === null) {
            Response::notFound();
        }

        $vote = (int) ($_POST['vote'] ?? 0);
        if ($vote !== 1 && $vote !== -1) {
            Flash::error('Ungültige Bewertung.');
            $this->redirectBack('/quotes/' . $comment['quote_id']);
        }

        $this->votes->setVote(AuthService::userId(), $commentId, $vote);
        Flash::success('Stimme gespeichert.');
        $this->redirectBack('/quotes/' . $comment['quote_id']);
    }

    public function removeVote(string $id): void
    {
        Response::requireMethod(['DELETE']);
        Response::requireCsrf();
        AuthService::requireLogin();

        $commentId = (int) $id;
        $comment = $this->comments->findById($commentId);
        if ($comment === null) {
            Response::notFound();
        }

        $this->votes->removeVote(AuthService::userId(), $commentId);
        Flash::success('Stimme entfernt.');
        $this->redirectBack('/quotes/' . $comment['quote_id']);
    }

    public function edit(string $id): void
    {
        AuthService::requireLogin();

        $comment = $this->loadOwnedComment((int) $id);
        View::render('comments/edit', [
            'title' => 'Kommentar bearbeiten',
            'comment' => $comment,
            'errors' => [],
        ]);
    }

    public function update(string $id): void
    {
        Response::requireMethod(['PUT', 'POST']);
        Response::requireCsrf();
        AuthService::requireLogin();

        $comment = $this->loadOwnedComment((int) $id);
        $content = trim($_POST['content'] ?? '');
        $errors = ValidationService::commentContent($content);

        if ($errors !== []) {
            View::render('comments/edit', [
                'title' => 'Kommentar bearbeiten',
                'comment' => array_merge($comment, ['content' => $content]),
                'errors' => $errors,
            ]);
            return;
        }

        $this->comments->update((int) $comment['id'], $content);
        Flash::success('Kommentar aktualisiert.');
        View::redirect('/quotes/' . $comment['quote_id']);
    }

    public function destroy(string $id): void
    {
        Response::requireMethod(['DELETE']);
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

        $quoteId = (int) $comment['quote_id'];
        $this->comments->delete((int) $comment['id']);
        Flash::success('Kommentar gelöscht.');
        View::redirect('/quotes/' . $quoteId);
    }

    private function saveComment(int $quoteId, ?int $parentId): void
    {
        $quote = $this->quotes->findById($quoteId, AuthService::userId());
        if ($quote === null) {
            Response::notFound();
        }

        $commentSort = Comment::normalizeSort($_GET['csort'] ?? null);
        $content = trim($_POST['content'] ?? '');
        $errors = ValidationService::commentContent($content);

        if ($errors !== []) {
            View::render('quotes/show', [
                'title' => 'Zitat von ' . $quote['speaker'],
                'quote' => $quote,
                'commentTree' => $this->comments->buildTree($quoteId, $commentSort, AuthService::userId()),
                'commentCount' => $this->comments->countByQuoteId($quoteId),
                'commentSort' => $commentSort,
                'commentErrors' => $errors,
                'oldComment' => $content,
                'replyToId' => $parentId,
            ]);
            return;
        }

        $this->comments->create($quoteId, AuthService::userId(), $content, $parentId);
        Flash::success($parentId === null ? 'Kommentar gespeichert.' : 'Antwort gespeichert.');
        View::redirect('/quotes/' . $quoteId . ($parentId !== null ? '#comment-' . $parentId : ''));
    }

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

    private function redirectBack(string $fallback): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (is_string($referer) && $referer !== '' && str_contains($referer, $_SERVER['HTTP_HOST'] ?? '')) {
            header('Location: ' . $referer);
            exit;
        }

        View::redirect($fallback);
    }
}
