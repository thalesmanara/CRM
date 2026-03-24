<?php

declare(strict_types=1);

namespace Revita\Crm\Helpers;

final class Url
{
    public static function scriptBasePath(): string
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $script = str_replace('\\', '/', (string) $script);
        $base = rtrim(dirname($script), '/');
        return $base === '' ? '' : $base;
    }

    public static function to(string $path): string
    {
        $base = self::scriptBasePath();
        $path = '/' . ltrim($path, '/');
        if ($base === '') {
            return $path;
        }
        return $base . $path;
    }

    public static function redirect(string $path, int $code = 302): never
    {
        $url = self::to($path);
        if ($code === 301) {
            header('Location: ' . $url, true, 301);
        } else {
            header('Location: ' . $url, true, 302);
        }
        exit;
    }

    /** URL absoluta do site até /admin (útil para links de mídia na API depois). */
    public static function adminRootAbsolute(): string
    {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (string) $_SERVER['SERVER_PORT'] === '443');
        $scheme = $https ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return rtrim($scheme . '://' . $host . self::scriptBasePath(), '/');
    }

    /**
     * URL absoluta para uma rota dentro do admin (ex.: reset-password?token=...).
     * Não duplica o prefixo /admin.
     */
    public static function adminAbsolute(string $pathOrQuery): string
    {
        $pathOrQuery = ltrim($pathOrQuery, '/');
        return self::adminRootAbsolute() . '/' . $pathOrQuery;
    }
}
