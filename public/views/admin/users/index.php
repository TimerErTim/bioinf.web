<?php

use App\Csrf;
use App\Html;

/** @var string $title */
/** @var list<array<string, mixed>> $users */
/** @var int|null $currentUserId */
?>
<h1>Benutzerverwaltung</h1>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Benutzername</th>
                <th>Rolle</th>
                <th>Registriert</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= (int) $user['id'] ?></td>
                    <td><?= Html::e($user['username']) ?></td>
                    <td>
                        <?php if ((bool) $user['is_admin']): ?>
                            <span class="badge badge-admin">Admin</span>
                        <?php else: ?>
                            <span class="badge badge-user">User</span>
                        <?php endif; ?>
                    </td>
                    <td><?= Html::e(date('d.m.Y', strtotime($user['created_at']))) ?></td>
                    <td>
                        <?php if ((int) $user['id'] !== $currentUserId): ?>
                            <form class="inline-form" method="post"
                                  action="/admin/users/<?= (int) $user['id'] ?>/toggle-admin">
                                <?= Csrf::field() ?>
                                <button type="submit" class="btn btn-secondary btn-sm">
                                    <?= (bool) $user['is_admin'] ? 'Admin entziehen' : 'Zum Admin machen' ?>
                                </button>
                            </form>
                            <form class="inline-form" method="post"
                                  action="/admin/users/<?= (int) $user['id'] ?>/delete"
                                  onsubmit="return confirm('Benutzer wirklich löschen?');">
                                <?= Csrf::field() ?>
                                <button type="submit" class="btn btn-danger btn-sm">Löschen</button>
                            </form>
                        <?php else: ?>
                            <span class="quote-meta">(Du)</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<p><a href="/">← Zurück zur Startseite</a></p>
