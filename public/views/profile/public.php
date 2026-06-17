<?php

use App\Html;

require_once __DIR__ . '/../partials/avatar.php';
require_once __DIR__ . '/../partials/centered-page.php';

/** @var string $title */
/** @var array<string, mixed> $user */
/** @var int $commentCount */
/** @var bool $isOwnProfile */
?>
<?php openCenteredPage('lg'); ?>
<div class="rounded-2xl border border-stone-800 bg-stone-900/50 p-8 sm:p-10 shadow-xl shadow-black/20 text-center">
    <div class="flex justify-center mb-4">
        <?php renderAvatar($user, 'lg'); ?>
    </div>
    <h1 class="text-2xl font-bold text-stone-100"><?= Html::e($user['username']) ?></h1>
    <?php if ((bool) $user['is_admin']): ?>
        <span class="inline-flex mt-2 px-2.5 py-0.5 rounded-full text-xs bg-amber-950 text-amber-400 border border-amber-800/50">Admin</span>
    <?php endif; ?>
    <p class="mt-3 text-sm text-stone-500">Mitglied seit <?= Html::e(date('d.m.Y', strtotime($user['created_at']))) ?></p>
    <p class="mt-1 text-sm text-stone-400"><?= (int) $commentCount ?> <?= (int) $commentCount === 1 ? 'Kommentar' : 'Kommentare' ?></p>

    <?php if ($isOwnProfile): ?>
        <a href="/profile" class="inline-flex mt-6 px-4 py-2 rounded-lg bg-amber-600 hover:bg-amber-500 text-stone-950 font-medium text-sm transition-colors">
            Profil bearbeiten
        </a>
    <?php endif; ?>
</div>

<p class="mt-6 text-center">
    <a href="/" class="text-sm text-stone-500 hover:text-amber-400">← Zurück zum Feed</a>
</p>
<?php closeCenteredPage(); ?>
