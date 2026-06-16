<?php

use App\Csrf;
use App\Html;

/** @var string $title */
/** @var string $username */
/** @var list<string> $errors */
?>
<h1>Registrierung</h1>

<?php if ($errors !== []): ?>
    <ul class="error-list">
        <?php foreach ($errors as $error): ?>
            <li><?= Html::e($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<div class="card">
    <form method="post" action="/register">
        <?= Csrf::field() ?>
        <div class="form-group">
            <label for="username">Benutzername</label>
            <input type="text" id="username" name="username" class="form-control"
                   value="<?= Html::e($username) ?>" maxlength="50" required
                   pattern="[a-zA-Z0-9_]+" autocomplete="username">
            <p class="form-hint">3–50 Zeichen, nur Buchstaben, Zahlen und Unterstriche.</p>
        </div>
        <div class="form-group">
            <label for="password">Passwort</label>
            <input type="password" id="password" name="password" class="form-control"
                   minlength="8" required autocomplete="new-password">
            <p class="form-hint">Mindestens 8 Zeichen.</p>
        </div>
        <div class="form-group">
            <label for="password_confirm">Passwort bestätigen</label>
            <input type="password" id="password_confirm" name="password_confirm" class="form-control"
                   minlength="8" required autocomplete="new-password">
        </div>
        <button type="submit" class="btn btn-primary">Registrieren</button>
    </form>
</div>

<p>Bereits registriert? <a href="/login">Zum Login</a></p>
