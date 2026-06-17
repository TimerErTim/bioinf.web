<?php

declare(strict_types=1);

/*
 * Application startup. Included once from index.php.
 *
 * declare(strict_types=1) means PHP checks parameter and return types strictly.
 * Without it, PHP might silently convert types (e.g. string "5" to int 5).
 */

// require loads config.php and returns its array (like reading a settings file).
$config = require __DIR__ . '/config.php';

// Optional local overrides (e.g. different MySQL port on your machine).
$localConfig = __DIR__ . '/config.local.php';
if (is_file($localConfig)) {
    $overrides = require $localConfig;
    // Merge arrays. Values from $overrides replace matching keys in $config.
    $config = array_replace_recursive($config, $overrides);
}

/*
 * Simple autoloader: no Composer. When code uses "App\Model\User",
 * PHP loads src/Model/User.php automatically.
 * Similar idea to Java classpaths, but path is derived from the namespace.
 */
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($prefix)));
    $file = __DIR__ . DIRECTORY_SEPARATOR . $relative . '.php';
    if (is_file($file)) {
        require $file;
    }
});

/*
 * Sessions store data per browser on the server (e.g. who is logged in).
 * session_start() must run before reading or writing $_SESSION.
 * The browser only gets a session ID cookie, not the actual session data.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

return $config;
