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

/**
 * Authentication: registration, login, logout.
 */
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
            'title' => 'Register',
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
            $errors[] = 'Passwords do not match.';
        }

        if ($errors === [] && $this->users->findByUsername($username) !== null) {
            $errors[] = 'This username is already taken.';
        }

        if ($errors !== []) {
            View::render('auth/register', [
                'title' => 'Register',
                'username' => $username,
                'errors' => $errors,
            ]);
            return;
        }

        $this->users->create($username, password_hash($password, PASSWORD_DEFAULT));
        Flash::success('Registration successful. Please log in.');
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
                'errors' => ['Invalid username or password.'],
            ]);
            return;
        }

        AuthService::login((int) $user['id'], $user['username'], (bool) $user['is_admin']);
        Flash::success('Welcome back, ' . $user['username'] . '!');
        View::redirect('/');
    }

    public function logout(): void
    {
        Response::requirePost();
        Response::requireCsrf();
        AuthService::logout();
        Flash::success('You have been logged out.');
        View::redirect('/');
    }
}
