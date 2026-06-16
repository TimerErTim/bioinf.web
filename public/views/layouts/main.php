<?php

use App\Csrf;
use App\Html;
use App\Service\AuthService;

/** @var string $title */
/** @var string $content */
/** @var list<array{type: string, message: string}> $flashMessages */
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Html::e($title) ?> — GoT Quotes</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <a href="/" class="site-title">GoT Quotes</a>
            <nav>
                <ul class="site-nav">
                    <li><a href="/">Start</a></li>
                    <?php if (AuthService::check()): ?>
                        <li><span class="user-badge"><?= Html::e(AuthService::username()) ?></span></li>
                        <?php if (AuthService::isAdmin()): ?>
                            <li><a href="/admin/users">Benutzer</a></li>
                            <li><a href="/admin/quotes">Zitate</a></li>
                        <?php endif; ?>
                        <li>
                            <form class="inline-form" method="post" action="/logout">
                                <?= Csrf::field() ?>
                                <button type="submit" class="link-btn">Logout</button>
                            </form>
                        </li>
                    <?php else: ?>
                        <li><a href="/login">Login</a></li>
                        <li><a href="/register">Registrierung</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <?php if (!empty($flashMessages)): ?>
            <ul class="flash-list">
                <?php foreach ($flashMessages as $flash): ?>
                    <li class="flash flash-<?= Html::e($flash['type']) ?>">
                        <?= Html::e($flash['message']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?= $content ?>
    </main>

    <footer class="site-footer">
        <div class="container">
            WEB4 PHP — Game of Thrones Quotes
        </div>
    </footer>
</body>
</html>
