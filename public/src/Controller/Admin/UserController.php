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

        // Fail if user not found (could be a bad id or deleted user)
        if ($target === null) {
            Response::notFound();
        }

        // Do not allow users to change their own admin status (prevents accidental lockout)
        if ($targetId === AuthService::userId()) {
            Flash::error('Eigene Admin-Rolle kannst du nicht ändern.');
            View::redirect('/admin/users');
        }

        // If this is the last admin and they're being demoted, disallow
        $willDemote = (bool) $target['is_admin'];
        if ($willDemote && $this->users->countAdmins() <= 1) {
            Flash::error('Letzter Admin. Rolle kann nicht entzogen werden.');
            View::redirect('/admin/users');
        }

        // Toggle is_admin status
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

        // Block if user not found (wrong/invalid id)
        if ($target === null) {
            Response::notFound();
        }

        // Prevent admin from deleting themselves to avoid losing the last admin
        if ($targetId === AuthService::userId()) {
            Flash::error('Du kannst dich nicht selbst löschen.');
            View::redirect('/admin/users');
        }

        // Edge case: block removing the last admin account completely
        if ((bool) $target['is_admin'] && $this->users->countAdmins() <= 1) {
            Flash::error('Letzter Admin kann nicht gelöscht werden.');
            View::redirect('/admin/users');
        }

        // Delete the user's avatar file if present (no-op if null)
        UploadService::deleteFile($target['avatar_path'] ?? null);

        // Actually remove the user record; related comments will render as "<deleted>"
        $this->users->delete($targetId);

        Flash::success('User gelöscht. Kommentare zeigen jetzt <deleted>.');
        View::redirect('/admin/users');
    }
}
