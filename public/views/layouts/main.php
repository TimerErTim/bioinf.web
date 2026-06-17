<?php

use App\Csrf;
use App\Html;
use App\Service\AuthService;

?>
<!DOCTYPE html>
<html lang="de" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= Html::e(Csrf::token()) ?>">
    <title><?= Html::e($title) ?> · GoT Quotes Forum</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,600;1,400&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @theme {
            --font-sans: "Inter", ui-sans-serif, system-ui, sans-serif;
            --font-serif: "Crimson Pro", ui-serif, Georgia, serif;
            --color-forum-bg: #0c0a09;
            --color-forum-surface: #1c1917;
            --color-forum-border: #292524;
            --color-forum-gold: #d97706;
            --color-forum-gold-light: #fbbf24;
        }
        body { font-family: var(--font-sans); }
        .quote-serif { font-family: var(--font-serif); }
    </style>
</head>
<body class="min-h-full bg-stone-950 text-stone-100 antialiased flex flex-col">
    <header class="border-b border-stone-800 bg-stone-950/90 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-4 flex flex-wrap items-center justify-between gap-4">
            <a href="/" class="group flex items-center gap-3 no-underline">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-amber-600 to-amber-800 text-lg font-bold text-stone-950 shadow-lg shadow-amber-900/30">Q</span>
                <span class="text-lg font-semibold text-stone-100 group-hover:text-amber-400 transition-colors">GoT Quotes</span>
                <span class="hidden sm:inline text-xs uppercase tracking-widest text-stone-500 font-medium">Forum</span>
            </a>
            <nav>
                <ul class="flex flex-wrap items-center gap-1 sm:gap-2 text-sm">
                    <li><a href="/" class="px-3 py-2 rounded-lg text-stone-300 hover:text-amber-400 hover:bg-stone-800/80 transition-colors">Feed</a></li>
                    <?php if (AuthService::check()): ?>
                        <li><a href="/profile" class="px-3 py-2 rounded-lg text-stone-300 hover:text-amber-400 hover:bg-stone-800/80 transition-colors">Profil</a></li>
                        <?php if (AuthService::isAdmin()): ?>
                            <li><a href="/admin/users" class="px-3 py-2 rounded-lg text-stone-400 hover:text-amber-400 hover:bg-stone-800/80 transition-colors">Benutzer</a></li>
                            <li><a href="/admin/quotes" class="px-3 py-2 rounded-lg text-stone-400 hover:text-amber-400 hover:bg-stone-800/80 transition-colors">Zitate</a></li>
                        <?php endif; ?>
                        <li class="flex items-center gap-2 pl-2 border-l border-stone-800">
                            <?php
                            require_once __DIR__ . '/../partials/avatar.php';
                            renderAvatar(
                                ['user_id' => AuthService::userId(), 'username' => AuthService::username(), 'avatar_path' => AuthService::avatarPath()],
                                'sm',
                                true,
                            );
                            ?>
                            <a href="/users/<?= (int) AuthService::userId() ?>" class="text-stone-400 hidden sm:inline hover:text-amber-400 transition-colors"><?= Html::e(AuthService::username()) ?></a>
                        </li>
                        <li>
                            <form method="post" action="/logout" class="inline">
                                <?= Csrf::field() ?>
                                <button type="submit" class="px-3 py-2 rounded-lg text-stone-400 hover:text-red-400 hover:bg-stone-800/80 transition-colors cursor-pointer bg-transparent border-0 text-sm">Logout</button>
                            </form>
                        </li>
                    <?php else: ?>
                        <li><a href="/login" class="px-3 py-2 rounded-lg text-stone-300 hover:text-amber-400 hover:bg-stone-800/80 transition-colors">Login</a></li>
                        <li><a href="/register" class="px-4 py-2 rounded-lg bg-amber-600 hover:bg-amber-500 text-stone-950 font-medium transition-colors shadow-md shadow-amber-900/40">Registrieren</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="flex-1 max-w-5xl w-full mx-auto px-4 sm:px-6 py-8">
        <?php if (!empty($flashMessages)): ?>
            <ul class="mb-6 space-y-2">
                <?php foreach ($flashMessages as $flash): ?>
                    <?php
                    $flashClass = match ($flash['type']) {
                        'success' => 'border-emerald-700/50 bg-emerald-950/50 text-emerald-200',
                        'error' => 'border-red-800/50 bg-red-950/50 text-red-200',
                        default => 'border-stone-700 bg-stone-900 text-stone-200',
                    };
                    ?>
                    <li class="rounded-xl border px-4 py-3 text-sm <?= $flashClass ?>"><?= Html::e($flash['message']) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?= $content ?>
    </main>

    <footer class="border-t border-stone-800 py-6 mt-auto">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 text-center text-sm text-stone-500">
            WEB4 PHP · Game of Thrones Quotes Forum · <em class="text-stone-600">Words are wind — but some linger forever.</em>
        </div>
    </footer>
    <script src="/assets/js/app.js" defer></script>
</body>
</html>
