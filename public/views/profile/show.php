<?php

use App\Csrf;
use App\Html;

require_once __DIR__ . '/../partials/avatar.php';
require_once __DIR__ . '/../partials/delete-button.php';
require_once __DIR__ . '/../partials/centered-page.php';

/** @var string $title */
/** @var array<string, mixed> $user */
/** @var list<string> $errors */
?>
<?php openCenteredPage('lg'); ?>
<h1 class="text-2xl font-bold text-stone-100 mb-6 text-center">Profil</h1>

<?php if ($errors !== []): ?>
    <ul class="mb-4 space-y-1 text-sm text-red-400 rounded-xl border border-red-900/50 bg-red-950/30 px-4 py-3">
        <?php foreach ($errors as $error): ?>
            <li><?= Html::e($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<div class="rounded-2xl border border-stone-800 bg-stone-900/50 p-6 sm:p-8 shadow-xl shadow-black/20">
    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4 mb-6 text-center sm:text-left">
        <?php renderAvatar($user, 'lg'); ?>
        <div>
            <p class="text-lg font-semibold text-stone-100"><?= Html::e($user['username']) ?></p>
            <p class="text-sm text-stone-500">Mitglied seit <?= Html::e(date('d.m.Y', strtotime($user['created_at']))) ?></p>
            <a href="/users/<?= (int) $user['id'] ?>" class="inline-block mt-2 text-sm text-amber-500 hover:text-amber-400">Öffentliches Profil ansehen →</a>
        </div>
    </div>

    <form method="post" action="/profile/avatar" enctype="multipart/form-data" class="space-y-4 mb-4">
        <?= Csrf::field() ?>
        <div>
            <label for="avatar" class="block text-sm font-medium text-stone-300 mb-1">Profilbild hochladen</label>
            <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp" required
                   class="w-full text-sm text-stone-400 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-stone-800 file:text-stone-200">
            <p class="mt-1 text-xs text-stone-500">JPEG, PNG oder WebP · max. 2 MB</p>
        </div>
        <button type="submit" class="px-4 py-2 rounded-lg bg-amber-600 hover:bg-amber-500 text-stone-950 font-medium text-sm transition-colors cursor-pointer">Hochladen</button>
    </form>

    <?php if (!empty($user['avatar_path'])): ?>
        <?php renderDeleteButton('/profile/avatar', 'Profilbild entfernen', 'Profilbild wirklich entfernen?'); ?>
    <?php endif; ?>
</div>
<?php closeCenteredPage(); ?>
