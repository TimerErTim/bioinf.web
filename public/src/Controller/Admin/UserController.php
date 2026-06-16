<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Database;
use App\Flash;
use App\Model\User;
use App\Response;
use App\Service\AuthService;
use App\View;

/**
 * Admin user management.
 */
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
            'title' => 'Manage Users',
            'users' => $this->users->findAll(),
            'currentUserId' => AuthService::userId(),
        ]);
    }

    public function toggleAdmin(string $id): void
    {
        Response::requirePost();
        Response::requireCsrf();
        AuthService::requireAdmin();

        $targetId = (int) $id;
        $target = $this->users->findById($targetId);

        if ($target === null) {
            Response::notFound();
        }

        if ($targetId === AuthService::userId()) {
            Flash::error('You cannot change your own admin role.');
            View::redirect('/admin/users');
        }

        $willDemote = (bool) $target['is_admin'];
        if ($willDemote && $this->users->countAdmins() <= 1) {
            Flash::error('Cannot demote the last administrator.');
            View::redirect('/admin/users');
        }

        $this->users->setAdmin($targetId, !$willDemote);
        Flash::success('User role updated.');
        View::redirect('/admin/users');
    }

    public function delete(string $id): void
    {
        Response::requirePost();
        Response::requireCsrf();
        AuthService::requireAdmin();

        $targetId = (int) $id;
        $target = $this->users->findById($targetId);

        if ($target === null) {
            Response::notFound();
        }

        if ((bool) $target['is_admin'] && $this->users->countAdmins() <= 1) {
            Flash::error('Cannot delete the last administrator.');
            View::redirect('/admin/users');
        }

        $this->users->delete($targetId);
        Flash::success('User deleted. Their comments now show as deleted.');
        View::redirect('/admin/users');
    }
}
