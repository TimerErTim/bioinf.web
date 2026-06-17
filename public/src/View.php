<?php

declare(strict_types=1);

namespace App;

/*
 * Renders HTML templates (views) and optional layout wrapper.
 *
 * Views are plain PHP files in views/. They are not a separate template
 * language like JSP or Thymeleaf.
 */
final class View
{
    public static function render(string $view, array $data = [], ?string $layout = 'layouts/main'): void
    {
        /*
         * extract() creates variables from array keys in the current scope.
         * ['title' => 'Login'] becomes $title = 'Login' inside the view file.
         * EXTR_SKIP avoids overwriting variables that already exist.
         */
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

        /*
         * Output buffering: view output goes into a buffer instead of the browser.
         * ob_get_clean() returns the buffer as a string (used to wrap content in layout).
         */
        ob_start();
        require __DIR__ . '/../views/' . $view . '.php';

        return (string) ob_get_clean();
    }

    /** Send Location header and stop script (Post/Redirect/Get pattern). */
    public static function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}
