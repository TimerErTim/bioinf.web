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
            'likedQuotes' => $this->enrichQuotesForViewer($this->likes->findQuotesByUserId($userId)),
            'isOwnProfile' => AuthService::userId() === $userId,
        ]);
    }

    /** @param list<array<string, mixed>> $quotes */
    private function enrichQuotesForViewer(array $quotes): array
    {
        $viewerId = AuthService::userId();
        if ($viewerId === null) {
            foreach ($quotes as &$quote) {
                $quote['user_liked'] = 0;
            }
            return $quotes;
        }

        foreach ($quotes as &$quote) {
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

        $upload = UploadService::storeImage($_FILES['avatar'] ?? [], 'avatars');
        if ($upload['errors'] !== []) {
            View::render('profile/show', [
                'title' => 'Profil',
                'user' => $user,
                'errors' => $upload['errors'],
            ]);
            return;
        }

        if ($upload['path'] === null) {
            Flash::error('Bitte ein Bild auswählen.');
            View::redirect('/profile');
        }

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

        UploadService::deleteFile($user['avatar_path'] ?? null);
        $this->users->updateAvatar($userId, null);
        AuthService::refreshAvatar(null);
        Flash::success('Profilbild entfernt.');
        View::redirect('/profile');
    }
}
