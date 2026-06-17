<?php

declare(strict_types=1);

use App\Controller\Admin\QuoteController as AdminQuoteController;
use App\Controller\Admin\UserController as AdminUserController;
use App\Controller\AuthController;
use App\Controller\CommentController;
use App\Controller\ProfileController;
use App\Controller\QuoteController;
use App\Router;

$config = require __DIR__ . '/src/bootstrap.php';

$router = new Router();

$auth = new AuthController($config);
$profile = new ProfileController($config);
$quotes = new QuoteController($config);
$comments = new CommentController($config);
$adminUsers = new AdminUserController($config);
$adminQuotes = new AdminQuoteController($config);

$router->get('/', fn () => $quotes->index());
$router->get('/quotes', fn () => $quotes->index());
$router->get('/quotes/{id}', fn (string $id) => $quotes->show($id));

$router->get('/register', fn () => $auth->showRegister());
$router->post('/register', fn () => $auth->register());
$router->get('/login', fn () => $auth->showLogin());
$router->post('/login', fn () => $auth->login());
$router->post('/logout', fn () => $auth->logout());

$router->get('/profile', fn () => $profile->show());
$router->post('/profile/avatar', fn () => $profile->uploadAvatar());
$router->delete('/profile/avatar', fn () => $profile->deleteAvatar());

$router->post('/quotes/{quoteId}/comments', fn (string $quoteId) => $comments->store($quoteId));
$router->post('/comments/{parentId}/replies', fn (string $parentId) => $comments->reply($parentId));
$router->get('/comments/{id}/edit', fn (string $id) => $comments->edit($id));
$router->put('/comments/{id}', fn (string $id) => $comments->update($id));
$router->post('/comments/{id}', fn (string $id) => $comments->update($id));
$router->delete('/comments/{id}', fn (string $id) => $comments->destroy($id));

$router->get('/admin/users', fn () => $adminUsers->index());
$router->patch('/admin/users/{id}/admin', fn (string $id) => $adminUsers->toggleAdmin($id));
$router->delete('/admin/users/{id}', fn (string $id) => $adminUsers->destroy($id));

$router->get('/admin/quotes', fn () => $adminQuotes->index());
$router->get('/admin/quotes/new', fn () => $adminQuotes->create());
$router->post('/admin/quotes', fn () => $adminQuotes->store());
$router->get('/admin/quotes/{id}/edit', fn (string $id) => $adminQuotes->edit($id));
$router->post('/admin/quotes/{id}', fn (string $id) => $adminQuotes->update($id));
$router->delete('/admin/quotes/{id}', fn (string $id) => $adminQuotes->destroy($id));

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST' && isset($_POST['_method'])) {
    $method = strtoupper((string) $_POST['_method']);
}

$uri = $_SERVER['REQUEST_URI'] ?? '/';

$router->dispatch($method, $uri);
