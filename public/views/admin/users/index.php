<?php

use App\Html;

require_once __DIR__ . '/../../partials/avatar.php';
require_once __DIR__ . '/../../partials/delete-button.php';

/** @var string $title */
/** @var list<array<string, mixed>> $users */
/** @var int|null $currentUserId */
?>
<h1 class="text-2xl font-bold text-stone-100 mb-6">Benutzerverwaltung</h1>

<div class="overflow-x-auto rounded-2xl border border-stone-800">
    <table class="w-full text-sm text-left">
        <thead class="bg-stone-900 text-stone-400 uppercase text-xs tracking-wider">
            <tr>
                <th class="px-4 py-3">ID</th>
                <th class="px-4 py-3">Benutzer</th>
                <th class="px-4 py-3">Rolle</th>
                <th class="px-4 py-3">Registriert</th>
                <th class="px-4 py-3">Aktionen</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-800">
            <?php foreach ($users as $user): ?>
                <tr class="hover:bg-stone-900/50">
                    <td class="px-4 py-3 text-stone-500"><?= (int) $user['id'] ?></td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <?php renderAvatar($user, 'sm'); ?>
                            <span class="text-stone-200"><?= Html::e($user['username']) ?></span>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <?php if ((bool) $user['is_admin']): ?>
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-amber-950 text-amber-400 border border-amber-800/50">Admin</span>
                        <?php else: ?>
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-stone-800 text-stone-400">User</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-stone-500"><?= Html::e(date('d.m.Y', strtotime($user['created_at']))) ?></td>
                    <td class="px-4 py-3">
                        <?php if ((int) $user['id'] !== $currentUserId): ?>
                            <div class="flex flex-wrap gap-2">
                                <?php renderPatchButton(
                                    '/admin/users/' . (int) $user['id'] . '/admin',
                                    (bool) $user['is_admin'] ? 'Admin entziehen' : 'Zum Admin machen',
                                    'Rolle wirklich ändern?',
                                ); ?>
                                <?php renderDeleteButton('/admin/users/' . (int) $user['id'], 'Löschen', 'Benutzer wirklich löschen?'); ?>
                            </div>
                        <?php else: ?>
                            <span class="text-stone-600 text-xs">(Du)</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<p class="mt-4"><a href="/" class="text-sm text-stone-500 hover:text-amber-400">← Zurück zur Startseite</a></p>
