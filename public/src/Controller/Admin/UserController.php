<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Database;
use App\Flash;
use App\Model\User;
use App\Response;
use App\Service\AuthService;
use App\Service\UploadService;
use App\View;

final class UserController
{
    private User $users;

    public function __construct(array $config)
    {
        $this->users = new User(Database::connection($config['db']));
    }

    public function index(): void
    {
        AuthService::requireAdmin();

        View::render('admin/users/index', [
            'title' => 'Benutzerverwaltung',
            'users' => $this->users->findAll(),
            'currentUserId' => AuthService::userId(),
        ]);
    }

    public function toggleAdmin(string $id): void
    {
        Response::requireMethod(['PATCH']);
        Response::requireCsrf();
        AuthService::requireAdmin();

        $targetId = (int) $id;
        $target = $this->users->findById($targetId);

        if ($target === null) {
            Response::notFound();
        }

        if ($targetId === AuthService::userId()) {
            Flash::error('Eigene Admin-Rolle kannst du nicht ändern.');
            View::redirect('/admin/users');
        }

        $willDemote = (bool) $target['is_admin'];
        if ($willDemote && $this->users->countAdmins() <= 1) {
            Flash::error('Letzter Admin. Rolle kann nicht entzogen werden.');
            View::redirect('/admin/users');
        }

        $this->users->setAdmin($targetId, !$willDemote);
        Flash::success('Rolle geändert.');
        View::redirect('/admin/users');
    }

    public function destroy(string $id): void
    {
        Response::requireMethod(['DELETE']);
        Response::requireCsrf();
        AuthService::requireAdmin();

        $targetId = (int) $id;
        $target = $this->users->findById($targetId);

        if ($target === null) {
            Response::notFound();
        }

        if ($targetId === AuthService::userId()) {
            Flash::error('Du kannst dich nicht selbst löschen.');
            View::redirect('/admin/users');
        }

        if ((bool) $target['is_admin'] && $this->users->countAdmins() <= 1) {
            Flash::error('Letzter Admin kann nicht gelöscht werden.');
            View::redirect('/admin/users');
        }

        UploadService::deleteFile($target['avatar_path'] ?? null);
        $this->users->delete($targetId);
        Flash::success('User gelöscht. Kommentare zeigen jetzt <deleted>.');
        View::redirect('/admin/users');
    }
}
