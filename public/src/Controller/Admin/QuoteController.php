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
        // Initialize Quote model with DB connection from config
        $this->quotes = new Quote(Database::connection($config['db']));
    }

    public function index(): void
    {
        AuthService::requireAdmin();

        View::render('admin/quotes/index', [
            'title' => 'Zitate verwalten',
            'quotes' => $this->quotes->findAll(500, 0),
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

        // Handles creation of a new quote (quoteId null means create mode)
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

        // Handles update of an existing quote
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

        // Delete associated image, if any. No-op if image_path is null
        UploadService::deleteFile($quote['image_path'] ?? null);
        $this->quotes->delete($quoteId);
        Flash::success('Zitat gelöscht.');
        View::redirect('/admin/quotes');
    }

    /**
     * Handle create or update of a quote.
     * If $quoteId is null, create; if int, update.
     */
    private function handleSave(?int $quoteId): void
    {
        // If updating, get current quote and fail if not found
        $existing = $quoteId !== null ? $this->quotes->findById($quoteId) : null;
        if ($quoteId !== null && $existing === null) {
            Response::notFound();
        }

        // Form input: build $data array, fallback to existing image if present
        $data = [
            'text' => trim($_POST['text'] ?? ''),
            'speaker' => trim($_POST['speaker'] ?? ''),
            'season' => trim($_POST['season'] ?? '') !== '' ? (int) $_POST['season'] : null,
            'episode' => trim($_POST['episode'] ?? '') !== '' ? (int) $_POST['episode'] : null,
            // For updates, prefill value from existing
            'image_path' => $existing['image_path'] ?? null,
        ];

        // Aggregate all validation errors
        $errors = array_merge(
            ValidationService::quoteText($data['text']),
            ValidationService::speaker($data['speaker']),
            ValidationService::optionalUint($_POST['season'] ?? null, 'Staffel'),
            ValidationService::optionalUint($_POST['episode'] ?? null, 'Episode'),
        );

        // File upload handling: only proceed if an image file is uploaded
        if (isset($_FILES['image'])) {
            $upload = UploadService::storeImage($_FILES['image'], 'quotes');
            $errors = array_merge($errors, $upload['errors']);
            if ($upload['path'] !== null) {
                // If updating and old image exists, remove from server
                if ($existing !== null && !empty($existing['image_path'])) {
                    UploadService::deleteFile($existing['image_path']);
                }
                $data['image_path'] = $upload['path'];
            }
        }

        // Image removal requested: user asked to remove existing image
        if (!empty($_POST['remove_image']) && $existing !== null) {
            UploadService::deleteFile($existing['image_path'] ?? null);
            $data['image_path'] = null;
        }

        // Choose view and title based on create/update mode
        $view = $quoteId === null ? 'admin/quotes/create' : 'admin/quotes/edit';
        $title = $quoteId === null ? 'Neues Zitat' : 'Zitat bearbeiten';

        // If there are validation or upload errors, re-render the form
        if ($errors !== []) {
            View::render($view, [
                'title' => $title,
                'quote' => $quoteId === null ? $data : array_merge($existing, $data),
                'errors' => $errors,
            ]);
            return;
        }

        // Commit the create or update, depending on whether it's a new quote or editing
        if ($quoteId === null) {
            $this->quotes->create($data);
            Flash::success('Zitat angelegt.');
        } else {
            $this->quotes->update($quoteId, $data);
            Flash::success('Zitat gespeichert.');
        }

        View::redirect('/admin/quotes');
    }

    /**
     * Returns an empty quote structure for new quote form.
     */
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
