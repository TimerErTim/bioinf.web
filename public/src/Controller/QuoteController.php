<?php

declare(strict_types=1);

namespace App\Controller;

use App\Database;
use App\Flash;
use App\Model\Comment;
use App\Model\Quote;
use App\Model\QuoteLike;
use App\Response;
use App\Service\AuthService;
use App\View;

final class QuoteController
{
    private const PAGE_SIZE = 20;

    private Quote $quotes;
    private Comment $comments;
    private QuoteLike $likes;

    public function __construct(array $config)
    {
        $pdo = Database::connection($config['db']);
        $this->quotes = new Quote($pdo);
        $this->comments = new Comment($pdo);
        $this->likes = new QuoteLike($pdo);
    }

    public function index(): void
    {
        $sort = Quote::normalizeSort($_GET['sort'] ?? null);
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $total = $this->quotes->countAll();
        $totalPages = max(1, (int) ceil($total / self::PAGE_SIZE));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * self::PAGE_SIZE;
        $viewerId = AuthService::userId();

        View::render('quotes/index', [
            'title' => 'Zitate-Forum',
            'quotes' => $this->quotes->findAll(self::PAGE_SIZE, $offset, $sort, $viewerId),
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'sort' => $sort,
        ]);
    }

    public function show(string $id): void
    {
        $quoteId = (int) $id;
        $quote = $this->quotes->findById($quoteId, AuthService::userId());

        if ($quote === null) {
            Response::notFound();
        }

        $commentSort = Comment::normalizeSort($_GET['csort'] ?? null);

        View::render('quotes/show', [
            'title' => 'Zitat von ' . $quote['speaker'],
            'quote' => $quote,
            'commentTree' => $this->comments->buildTree($quoteId, $commentSort, AuthService::userId()),
            'commentCount' => (int) $quote['comment_count'],
            'commentSort' => $commentSort,
            'commentErrors' => [],
            'oldComment' => '',
            'replyToId' => null,
        ]);
    }

    public function like(string $id): void
    {
        Response::requireMethod(['POST']);
        Response::requireCsrf();
        AuthService::requireLogin();

        $quoteId = (int) $id;
        if ($this->quotes->findById($quoteId) === null) {
            Response::notFound();
        }

        $this->likes->like(AuthService::userId(), $quoteId);
        Flash::success('Zitat gefällt dir.');
        Response::redirectBack('/quotes/' . $quoteId);
    }

    public function unlike(string $id): void
    {
        Response::requireMethod(['DELETE']);
        Response::requireCsrf();
        AuthService::requireLogin();

        $quoteId = (int) $id;
        if ($this->quotes->findById($quoteId) === null) {
            Response::notFound();
        }

        $this->likes->unlike(AuthService::userId(), $quoteId);
        Flash::success('Like entfernt.');
        Response::redirectBack('/quotes/' . $quoteId);
    }
}
