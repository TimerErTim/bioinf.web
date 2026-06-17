<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Database;
use App\Flash;
use App\Model\Quote;
use App\Response;
use App\Service\AuthService;
use App\Service\UploadService;
use App\Service\ValidationService;
use App\View;

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

        View::render('admin/quotes/create', [
            'title' => 'Neues Zitat',
            'quote' => $this->emptyQuote(),
            'errors' => [],
        ]);
    }

    public function store(): void
    {
        Response::requirePost();
        Response::requireCsrf();
        AuthService::requireAdmin();

        $this->handleSave(null);
    }

    public function edit(string $id): void
    {
        AuthService::requireAdmin();

        $quoteId = (int) $id;
        $quote = $this->quotes->findById($quoteId);
        if ($quote === null) {
            Response::notFound();
        }

        View::render('admin/quotes/edit', [
            'title' => 'Zitat bearbeiten',
            'quote' => $quote,
            'errors' => [],
        ]);
    }

    public function update(string $id): void
    {
        Response::requirePost();
        Response::requireCsrf();
        AuthService::requireAdmin();

        $this->handleSave((int) $id);
    }

    public function destroy(string $id): void
    {
        Response::requireMethod(['DELETE']);
        Response::requireCsrf();
        AuthService::requireAdmin();

        $quoteId = (int) $id;
        $quote = $this->quotes->findById($quoteId);
        if ($quote === null) {
            Response::notFound();
        }

        UploadService::deleteFile($quote['image_path'] ?? null);
        $this->quotes->delete($quoteId);
        Flash::success('Zitat gelöscht.');
        View::redirect('/admin/quotes');
    }

    private function handleSave(?int $quoteId): void
    {
        $existing = $quoteId !== null ? $this->quotes->findById($quoteId) : null;
        if ($quoteId !== null && $existing === null) {
            Response::notFound();
        }

        $data = [
            'text' => trim($_POST['text'] ?? ''),
            'speaker' => trim($_POST['speaker'] ?? ''),
            'season' => trim($_POST['season'] ?? '') !== '' ? (int) $_POST['season'] : null,
            'episode' => trim($_POST['episode'] ?? '') !== '' ? (int) $_POST['episode'] : null,
            'image_path' => $existing['image_path'] ?? null,
        ];

        $errors = array_merge(
            ValidationService::quoteText($data['text']),
            ValidationService::speaker($data['speaker']),
            ValidationService::optionalUint($_POST['season'] ?? null, 'Staffel'),
            ValidationService::optionalUint($_POST['episode'] ?? null, 'Episode'),
        );

        if (isset($_FILES['image'])) {
            $upload = UploadService::storeImage($_FILES['image'], 'quotes');
            $errors = array_merge($errors, $upload['errors']);
            if ($upload['path'] !== null) {
                if ($existing !== null && !empty($existing['image_path'])) {
                    UploadService::deleteFile($existing['image_path']);
                }
                $data['image_path'] = $upload['path'];
            }
        }

        if (!empty($_POST['remove_image']) && $existing !== null) {
            UploadService::deleteFile($existing['image_path'] ?? null);
            $data['image_path'] = null;
        }

        $view = $quoteId === null ? 'admin/quotes/create' : 'admin/quotes/edit';
        $title = $quoteId === null ? 'Neues Zitat' : 'Zitat bearbeiten';

        if ($errors !== []) {
            View::render($view, [
                'title' => $title,
                'quote' => $quoteId === null ? $data : array_merge($existing, $data),
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
            'image_path' => null,
        ];
    }
}
