<?php

declare(strict_types=1);

namespace App\Controller;

use App\Database;
use App\Flash;
use App\Model\Comment;
use App\Model\Quote;
use App\Response;
use App\View;

final class QuoteController
{
    private const PAGE_SIZE = 20;

    private Quote $quotes;
    private Comment $comments;

    public function __construct(array $config)
    {
        $pdo = Database::connection($config['db']);
        $this->quotes = new Quote($pdo);
        $this->comments = new Comment($pdo);
    }

    public function index(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $total = $this->quotes->countAll();
        $totalPages = max(1, (int) ceil($total / self::PAGE_SIZE));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * self::PAGE_SIZE;

        View::render('quotes/index', [
            'title' => 'Zitate-Forum',
            'quotes' => $this->quotes->findAll(self::PAGE_SIZE, $offset),
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
        ]);
    }

    public function show(string $id): void
    {
        $quoteId = (int) $id;
        $quote = $this->quotes->findById($quoteId);

        if ($quote === null) {
            Response::notFound();
        }

        View::render('quotes/show', [
            'title' => 'Zitat von ' . $quote['speaker'],
            'quote' => $quote,
            'commentTree' => $this->comments->buildTree($quoteId),
            'commentCount' => $this->comments->countByQuoteId($quoteId),
            'commentErrors' => [],
            'oldComment' => '',
            'replyToId' => null,
        ]);
    }
}
