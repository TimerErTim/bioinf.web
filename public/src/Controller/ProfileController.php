<?php

declare(strict_types=1);

namespace App\Controller;

use App\Database;
use App\Flash;
use App\Model\User;
use App\Response;
use App\Service\AuthService;
use App\Service\UploadService;
use App\View;

final class ProfileController
{
    private User $users;

    public function __construct(array $config)
    {
        $this->users = new User(Database::connection($config['db']));
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
