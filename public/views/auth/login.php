<?php

use App\Csrf;
use App\Html;

/** @var string $title */
/** @var string $username */
/** @var list<string> $errors */
?>
<h1>Login</h1>

<?php if ($errors !== []): ?>
    <ul class="error-list">
        <?php foreach ($errors as $error): ?>
            <li><?= Html::e($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<div class="card">
    <form method="post" action="/login">
        <?= Csrf::field() ?>
        <div class="form-group">
            <label for="username">Benutzername</label>
            <input type="text" id="username" name="username" class="form-control"
                   value="<?= Html::e($username) ?>" required autocomplete="username">
        </div>
        <div class="form-group">
            <label for="password">Passwort</label>
            <input type="password" id="password" name="password" class="form-control"
                   required autocomplete="current-password">
        </div>
        <button type="submit" class="btn btn-primary">Einloggen</button>
    </form>
</div>

<p>Noch kein Konto? <a href="/register">Registrieren</a></p>

<p class="form-hint">Demo-Admin: <code>admin</code> / <code>admin</code></p>
