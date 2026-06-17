<?php

declare(strict_types=1);

namespace App\Controller;

use App\Database;
use App\Flash;
use App\Model\User;
use App\Response;
use App\Service\AuthService;
use App\Service\ValidationService;
use App\View;

// Register, login, logout. Handles $_POST form data from auth views.
final class AuthController
{
    private User $users;

    public function __construct(array $config)
    {
        $this->users = new User(Database::connection($config['db']));
    }

    public function showRegister(): void
    {
        if (AuthService::check()) {
            View::redirect('/');
        }

        View::render('auth/register', [
            'title' => 'Registrierung',
            'username' => '',
            'errors' => [],
        ]);
    }

    public function register(): void
    {
        Response::requirePost();
        Response::requireCsrf();

        if (AuthService::check()) {
            View::redirect('/');
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        $errors = ValidationService::username($username);
        $errors = array_merge($errors, ValidationService::password($password));

        if ($password !== $passwordConfirm) {
            $errors[] = 'Passwörter stimmen nicht überein.';
        }

        if ($errors === [] && $this->users->findByUsername($username) !== null) {
            $errors[] = 'Benutzername ist schon vergeben.';
        }

        if ($errors !== []) {
            View::render('auth/register', [
                'title' => 'Registrierung',
                'username' => $username,
                'errors' => $errors,
            ]);
            return;
        }

        /*
         * password_hash creates a one-way bcrypt hash for storage.
         * Never save plain passwords. PASSWORD_DEFAULT picks a secure algorithm.
         */
        $this->users->create($username, password_hash($password, PASSWORD_DEFAULT));
        Flash::success('Registrierung erfolgreich. Bitte einloggen.');
        View::redirect('/login');
    }

    public function showLogin(): void
    {
        if (AuthService::check()) {
            View::redirect('/');
        }

        View::render('auth/login', [
            'title' => 'Login',
            'username' => '',
            'errors' => [],
        ]);
    }

    public function login(): void
    {
        Response::requirePost();
        Response::requireCsrf();

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = $this->users->findByUsername($username);
        if ($user === null || !$this->users->verifyPassword($user, $password)) {
            View::render('auth/login', [
                'title' => 'Login',
                'username' => $username,
                'errors' => ['Benutzername oder Passwort ungültig.'],
            ]);
            return;
        }

        AuthService::login((int) $user['id'], $user['username'], (bool) $user['is_admin']);
        Flash::success('Willkommen, ' . $user['username'] . '!');
        View::redirect('/');
    }

    public function logout(): void
    {
        Response::requirePost();
        Response::requireCsrf();
        AuthService::logout();
        Flash::success('Ausgeloggt.');
        View::redirect('/');
    }
}
