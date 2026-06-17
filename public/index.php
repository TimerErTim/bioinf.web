<?php

declare(strict_types=1);

/*
 * Front controller (single entry point for all pages).
 *
 * Apache sends every request here (.htaccess). We map the URL to a controller
 * method. This is our small MVC router, not a framework like Spring or Laravel.
 *
 * "use ..." imports classes (like Java import). Namespaces avoid name clashes.
 */

use App\Controller\Admin\QuoteController as AdminQuoteController;
use App\Controller\Admin\UserController as AdminUserController;
use App\Controller\AuthController;
use App\Controller\CommentController;
use App\Controller\QuoteController;
use App\Router;

$config = require __DIR__ . '/src/bootstrap.php';

$router = new Router();

// One controller instance per area. $config holds DB settings for models.
$auth = new AuthController($config);
$quotes = new QuoteController($config);
$comments = new CommentController($config);
$adminUsers = new AdminUserController($config);
$adminQuotes = new AdminQuoteController($config);

/*
 * Register routes. {id} is a URL parameter, e.g. /quotes/3 -> id = "3".
 * fn () => ... is a short anonymous function (arrow function), like Java lambda.
 */
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

// $_SERVER is a PHP superglobal with request info from the web server.
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';

$router->dispatch($method, $uri);
