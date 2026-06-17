<?php

declare(strict_types=1);

namespace App;

final class View
{
    public static function render(string $view, array $data = [], ?string $layout = 'layouts/main'): void
    {
        // Populate local variables from the $data array, but don't overwrite existing ones
        extract($data, EXTR_SKIP);

        // Render the view file contents into $content
        $content = self::capture($view, $data);

        if ($layout === null) {
            echo $content;
            return;
        }

        // Consume and clear any flash messages from the session
        $flashMessages = Flash::consume();

        // Include the layout, which will probably make use of $content and $flashMessages
        require __DIR__ . '/../views/' . $layout . '.php';
    }

    public static function capture(string $view, array $data = []): string
    {
        // Populate local variables from $data for use in the view file
        extract($data, EXTR_SKIP);

        ob_start();
        require __DIR__ . '/../views/' . $view . '.php';
        // Get the output buffer contents as the rendered view
        return (string) ob_get_clean();
    }

    public static function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}
