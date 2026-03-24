<?php

declare(strict_types=1);

namespace Revita\Crm\Core;

final class View
{
    /**
     * @param array<string, mixed> $data
     */
    public static function render(string $view, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        $path = REVITA_CRM_ROOT . '/app/views/' . $view . '.php';
        if (!is_file($path)) {
            throw new \RuntimeException('View não encontrada: ' . $view);
        }
        ob_start();
        require $path;
        return (string) ob_get_clean();
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function layout(string $layout, string $view, array $data = []): string
    {
        $data['content'] = self::render($view, $data);
        return self::render('layouts/' . $layout, $data);
    }
}
