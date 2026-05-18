<?php

declare(strict_types=1);

namespace Revita\Crm\Helpers;

use Revita\Crm\Core\Config;
use Revita\Crm\Core\Session;

final class Url
{
    private const SESSION_BASE_KEY = '_cms_base_path';

    /** Caminho público da pasta do CMS (ex.: /adminnexa), sem barra final. */
    public static function scriptBasePath(): string
    {
        if (class_exists(Config::class) && Config::isInstalled()) {
            $cfg = Config::load();
            $fromConfig = isset($cfg['base_path']) ? trim((string) $cfg['base_path']) : '';
            if ($fromConfig !== '') {
                return self::normalizeBasePath($fromConfig);
            }
        }

        if (class_exists(Session::class) && session_status() === PHP_SESSION_ACTIVE) {
            $fromSession = Session::get(self::SESSION_BASE_KEY);
            if (is_string($fromSession) && $fromSession !== '') {
                return self::normalizeBasePath($fromSession);
            }
        }

        return self::detectBasePathFromServer();
    }

    public static function rememberBasePathInSession(): void
    {
        if (!class_exists(Session::class) || session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }
        $bp = self::detectBasePathFromFilesystem() ?: self::detectBasePathFromServer();
        if ($bp !== '') {
            Session::set(self::SESSION_BASE_KEY, $bp);
        }
    }

    public static function detectBasePathFromServer(): string
    {
        $fromFs = self::detectBasePathFromFilesystem();
        if ($fromFs !== '') {
            return $fromFs;
        }

        $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $script = str_replace('\\', '/', (string) $script);
        $base = rtrim(dirname($script), '/');
        if ($base !== '' && $base !== '.' && $base !== '/') {
            return $base;
        }

        foreach (['REDIRECT_URL', 'PATH_INFO'] as $key) {
            if (empty($_SERVER[$key]) || !is_string($_SERVER[$key])) {
                continue;
            }
            $redirect = str_replace('\\', '/', $_SERVER[$key]);
            if (str_starts_with($redirect, '/')) {
                $pos = strrpos($redirect, '/');
                if ($pos !== false && $pos > 0) {
                    return substr($redirect, 0, $pos);
                }
            }
        }

        return '';
    }

    public static function detectBasePathFromFilesystem(): string
    {
        if (!defined('REVITA_CRM_ROOT')) {
            return '';
        }
        $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
        if ($docRoot === '') {
            return '';
        }
        $docReal = realpath($docRoot);
        $rootReal = realpath(REVITA_CRM_ROOT);
        if ($docReal === false || $rootReal === false) {
            return '';
        }
        $docReal = str_replace('\\', '/', $docReal);
        $rootReal = str_replace('\\', '/', $rootReal);
        if (!str_starts_with($rootReal, $docReal)) {
            return '';
        }
        $rel = substr($rootReal, strlen($docReal));
        return self::normalizeBasePath($rel);
    }

    private static function normalizeBasePath(string $path): string
    {
        $path = '/' . trim(str_replace('\\', '/', $path), '/');
        return $path === '/' ? '' : $path;
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
        $path = ltrim($path, '/');
        $url = self::adminAbsolute($path);
        header('Location: ' . $url, true, $code === 301 ? 301 : 302);
        exit;
    }

    /** URL absoluta até a raiz do painel (útil para links de mídia na API). */
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
     * Usa o prefixo detectado da pasta do CMS (não assume /admin).
     */
    public static function adminAbsolute(string $pathOrQuery): string
    {
        $pathOrQuery = ltrim($pathOrQuery, '/');
        return self::adminRootAbsolute() . '/' . $pathOrQuery;
    }
}
