<?php

declare(strict_types=1);

namespace App\Controller;

use App\Database;
use App\Flash;
use App\Model\Comment;
use App\Model\QuoteLike;
use App\Model\User;
use App\Response;
use App\Service\AuthService;
use App\Service\UploadService;
use App\View;

final class ProfileController
{
    private User $users;
    private Comment $comments;
    private QuoteLike $likes;

    public function __construct(array $config)
    {
        // Get DB connection and initialize dependent models
        $pdo = Database::connection($config['db']);
        $this->users = new User($pdo);
        $this->comments = new Comment($pdo);
        $this->likes = new QuoteLike($pdo);
    }

    public function show(): void
    {
        AuthService::requireLogin();

        $user = $this->users->findById(AuthService::userId());
        if ($user === null) {
            // Edge case: Session claims logged in but user not found (account deleted?). Log out forcibly.
            AuthService::logout();
            View::redirect('/login');
        }

        View::render('profile/show', [
            'title' => 'Profil',
            'user' => $user,
            'errors' => [],
        ]);
    }

    public function showPublic(string $id): void
    {
        $userId = (int) $id;
        $user = $this->users->findById($userId);
        if ($user === null) {
            Response::notFound();
        }

        View::render('profile/public', [
            'title' => $user['username'],
            'user' => $user,
            'commentCount' => $this->users->countComments($userId),
            'commentScore' => $this->comments->totalScoreByUserId($userId),
            'likeCount' => $this->likes->countByUserId($userId),
            'comments' => $this->comments->findByUserId($userId),
            // Pass liked quotes with per-viewer like info
            'likedQuotes' => $this->enrichQuotesForViewer($this->likes->findQuotesByUserId($userId)),
            'isOwnProfile' => AuthService::userId() === $userId,
        ]);
    }

    /**
     * Takes a list of quote arrays, adds the current viewer's "liked" info.
     * If viewer not logged in, always 0.
     * @param list<array<string, mixed>> $quotes
     */
    private function enrichQuotesForViewer(array $quotes): array
    {
        $viewerId = AuthService::userId();
        if ($viewerId === null) {
            // If not logged in, mark all quotes as not liked by viewer
            foreach ($quotes as &$quote) {
                $quote['user_liked'] = 0;
            }
            return $quotes;
        }

        foreach ($quotes as &$quote) {
            // Detect if the logged-in viewer has liked this quote
            $quote['user_liked'] = $this->likes->hasLiked($viewerId, (int) $quote['id']) ? 1 : 0;
        }

        return $quotes;
    }

    public function uploadAvatar(): void
    {
        Response::requirePost();
        Response::requireCsrf();
        AuthService::requireLogin();

        $userId = AuthService::userId();
        $user = $this->users->findById($userId);
        if ($user === null) {
            Response::notFound();
        }

        // Handles file upload and image storage, returns array with path or upload errors
        $upload = UploadService::storeImage($_FILES['avatar'] ?? [], 'avatars');
        if ($upload['errors'] !== []) { // return on error, show errors
            View::render('profile/show', [
                'title' => 'Profil',
                'user' => $user,
                'errors' => $upload['errors'],
            ]);
            return;
        }

        if ($upload['path'] === null) {
            // File upload array was present, but no file chosen
            Flash::error('Bitte ein Bild auswählen.');
            View::redirect('/profile');
        }

        // Remove old avatar if present, swap to newly uploaded one
        UploadService::deleteFile($user['avatar_path'] ?? null);
        $this->users->updateAvatar($userId, $upload['path']);
        AuthService::refreshAvatar($upload['path']);
        Flash::success('Profilbild aktualisiert.');
        View::redirect('/profile');
    }

    public function deleteAvatar(): void
    {
        Response::requireMethod(['DELETE']);
        Response::requireCsrf();
        AuthService::requireLogin();

        $userId = AuthService::userId();
        $user = $this->users->findById($userId);
        if ($user === null) {
            Response::notFound();
        }

        // Remove avatar file and its record
        UploadService::deleteFile($user['avatar_path'] ?? null);
        $this->users->updateAvatar($userId, null);
        AuthService::refreshAvatar(null);
        Flash::success('Profilbild entfernt.');
        View::redirect('/profile');
    }
}
