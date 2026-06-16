<?php

declare(strict_types=1);

namespace App;

// Load views and wrap them in the layout
final class View
{
    public static function render(string $view, array $data = [], ?string $layout = 'layouts/main'): void
    {
        extract($data, EXTR_SKIP);
        $content = self::capture($view, $data);

        if ($layout === null) {
            echo $content;
            return;
        }

        $flashMessages = Flash::consume();
        require __DIR__ . '/../views/' . $layout . '.php';
    }

    public static function capture(string $view, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require __DIR__ . '/../views/' . $view . '.php';

        return (string) ob_get_clean();
    }

    public static function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}
