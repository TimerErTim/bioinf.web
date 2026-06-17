<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Database;
use App\Flash;
use App\Model\Quote;
use App\Response;
use App\Service\AuthService;
use App\Service\ValidationService;
use App\View;

// Admin quote CRUD. requireAdmin() runs at start of each action.
final class QuoteController
{
    private Quote $quotes;

    public function __construct(array $config)
    {
        $this->quotes = new Quote(Database::connection($config['db']));
    }

    public function index(): void
    {
        AuthService::requireAdmin();

        View::render('admin/quotes/index', [
            'title' => 'Zitate verwalten',
            'quotes' => $this->quotes->findAll(),
        ]);
    }

    public function create(): void
    {
        AuthService::requireAdmin();

        // Same URL for GET (show form) and POST (save). Check method inside.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleSave(null);
            return;
        }

        View::render('admin/quotes/create', [
            'title' => 'Neues Zitat',
            'quote' => $this->emptyQuote(),
            'errors' => [],
        ]);
    }

    public function update(string $id): void
    {
        AuthService::requireAdmin();

        $quoteId = (int) $id;
        $quote = $this->quotes->findById($quoteId);
        if ($quote === null) {
            Response::notFound();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleSave($quoteId);
            return;
        }

        View::render('admin/quotes/edit', [
            'title' => 'Zitat bearbeiten',
            'quote' => $quote,
            'errors' => [],
        ]);
    }

    public function delete(string $id): void
    {
        Response::requirePost();
        Response::requireCsrf();
        AuthService::requireAdmin();

        $quoteId = (int) $id;
        if ($this->quotes->findById($quoteId) === null) {
            Response::notFound();
        }

        $this->quotes->delete($quoteId);
        Flash::success('Zitat gelöscht.');
        View::redirect('/admin/quotes');
    }

    private function handleSave(?int $quoteId): void
    {
        Response::requirePost();
        Response::requireCsrf();

        $data = [
            'text' => trim($_POST['text'] ?? ''),
            'speaker' => trim($_POST['speaker'] ?? ''),
            'season' => trim($_POST['season'] ?? '') !== '' ? (int) $_POST['season'] : null,
            'episode' => trim($_POST['episode'] ?? '') !== '' ? (int) $_POST['episode'] : null,
        ];

        $errors = array_merge(
            ValidationService::quoteText($data['text']),
            ValidationService::speaker($data['speaker']),
            ValidationService::optionalUint($_POST['season'] ?? null, 'Staffel'),
            ValidationService::optionalUint($_POST['episode'] ?? null, 'Episode'),
        );

        $view = $quoteId === null ? 'admin/quotes/create' : 'admin/quotes/edit';
        $title = $quoteId === null ? 'Neues Zitat' : 'Zitat bearbeiten';

        if ($errors !== []) {
            View::render($view, [
                'title' => $title,
                'quote' => $quoteId === null ? $data : array_merge(['id' => $quoteId], $data),
                'errors' => $errors,
            ]);
            return;
        }

        if ($quoteId === null) {
            $this->quotes->create($data);
            Flash::success('Zitat angelegt.');
        } else {
            $this->quotes->update($quoteId, $data);
            Flash::success('Zitat gespeichert.');
        }

        View::redirect('/admin/quotes');
    }

    private function emptyQuote(): array
    {
        return [
            'text' => '',
            'speaker' => '',
            'season' => '',
            'episode' => '',
        ];
    }
}
