<?php

declare(strict_types=1);

/**
 * Einstiegspunkt — alle Anfragen laufen hier durch.
 */

use App\Controller\Admin\QuoteController as AdminQuoteController;
use App\Controller\Admin\UserController as AdminUserController;
use App\Controller\AuthController;
use App\Controller\CommentController;
use App\Controller\QuoteController;
use App\Router;

$config = require __DIR__ . '/src/bootstrap.php';

$router = new Router();

$auth = new AuthController($config);
$quotes = new QuoteController($config);
$comments = new CommentController($config);
$adminUsers = new AdminUserController($config);
$adminQuotes = new AdminQuoteController($config);

$router->get('/', fn () => $quotes->index());
$router->get('/quotes/{id}', fn (string $id) => $quotes->show($id));

$router->get('/register', fn () => $auth->showRegister());
$router->post('/register', fn () => $auth->register());
$router->get('/login', fn () => $auth->showLogin());
$router->post('/login', fn () => $auth->login());
$router->post('/logout', fn () => $auth->logout());

$router->post('/quotes/{id}/comments', fn (string $id) => $comments->store($id));
$router->get('/comments/{id}/edit', fn (string $id) => $comments->edit($id));
$router->post('/comments/{id}/edit', fn (string $id) => $comments->update($id));
$router->post('/comments/{id}/delete', fn (string $id) => $comments->delete($id));

$router->get('/admin/users', fn () => $adminUsers->index());
$router->post('/admin/users/{id}/toggle-admin', fn (string $id) => $adminUsers->toggleAdmin($id));
$router->post('/admin/users/{id}/delete', fn (string $id) => $adminUsers->delete($id));

$router->get('/admin/quotes', fn () => $adminQuotes->index());
$router->get('/admin/quotes/create', fn () => $adminQuotes->create());
$router->post('/admin/quotes/create', fn () => $adminQuotes->create());
$router->get('/admin/quotes/{id}/edit', fn (string $id) => $adminQuotes->update($id));
$router->post('/admin/quotes/{id}/edit', fn (string $id) => $adminQuotes->update($id));
$router->post('/admin/quotes/{id}/delete', fn (string $id) => $adminQuotes->delete($id));

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';

$router->dispatch($method, $uri);
