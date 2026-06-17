<?php

use App\Csrf;
use App\Html;

require_once __DIR__ . '/../partials/centered-page.php';

/** @var string $title */
/** @var string $username */
/** @var list<string> $errors */
?>
<?php openCenteredPage('lg'); ?>
<h1 class="text-2xl font-bold text-stone-100 mb-6 text-center">Registrierung</h1>

<?php if ($errors !== []): ?>
    <ul class="mb-4 space-y-1 text-sm text-red-400 rounded-xl border border-red-900/50 bg-red-950/30 px-4 py-3">
        <?php foreach ($errors as $error): ?>
            <li><?= Html::e($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<div class="rounded-2xl border border-stone-800 bg-stone-900/50 p-6 sm:p-8 shadow-xl shadow-black/20">
    <form method="post" action="/register" enctype="multipart/form-data" class="space-y-4">
        <?= Csrf::field() ?>
        <div>
            <label for="username" class="block text-sm font-medium text-stone-300 mb-1">Benutzername</label>
            <input type="text" id="username" name="username" required maxlength="50" pattern="[a-zA-Z0-9_]+" autocomplete="username"
                   value="<?= Html::e($username) ?>"
                   class="w-full rounded-lg border border-stone-700 bg-stone-950 px-3 py-2 text-stone-100 focus:border-amber-600 focus:ring-1 focus:ring-amber-600 outline-none">
            <p class="mt-1 text-xs text-stone-500">3–50 Zeichen, nur Buchstaben, Zahlen und Unterstriche.</p>
        </div>
        <div>
            <label for="password" class="block text-sm font-medium text-stone-300 mb-1">Passwort</label>
            <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password"
                   class="w-full rounded-lg border border-stone-700 bg-stone-950 px-3 py-2 text-stone-100 focus:border-amber-600 focus:ring-1 focus:ring-amber-600 outline-none">
        </div>
        <div>
            <label for="password_confirm" class="block text-sm font-medium text-stone-300 mb-1">Passwort bestätigen</label>
            <input type="password" id="password_confirm" name="password_confirm" required minlength="8" autocomplete="new-password"
                   class="w-full rounded-lg border border-stone-700 bg-stone-950 px-3 py-2 text-stone-100 focus:border-amber-600 focus:ring-1 focus:ring-amber-600 outline-none">
        </div>
        <div>
            <label for="avatar" class="block text-sm font-medium text-stone-300 mb-1">Profilbild (optional)</label>
            <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp"
                   class="w-full text-sm text-stone-400 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-stone-800 file:text-stone-200 hover:file:bg-stone-700">
        </div>
        <button type="submit" class="w-full py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-stone-950 font-semibold transition-colors cursor-pointer">Registrieren</button>
    </form>
</div>

<p class="mt-6 text-sm text-stone-500 text-center">Bereits registriert? <a href="/login" class="text-amber-500 hover:text-amber-400">Zum Login</a></p>
<?php closeCenteredPage(); ?>
