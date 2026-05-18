<?php

declare(strict_types=1);

namespace Revita\Crm\Core;

final class Request
{
    /** @var array<string, string> */
    private array $routeParams = [];

    /** @param array<string, string> $params */
    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function routeParam(string $key, string $default = ''): string
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $base = \Revita\Crm\Helpers\Url::scriptBasePath();
        $candidates = [];

        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $fromUri = parse_url($uri, PHP_URL_PATH);
        if (is_string($fromUri) && $fromUri !== '') {
            $candidates[] = str_replace('\\', '/', $fromUri);
        }
        foreach (['REDIRECT_URL', 'PATH_INFO', 'SCRIPT_NAME'] as $key) {
            if (!empty($_SERVER[$key]) && is_string($_SERVER[$key])) {
                $candidates[] = str_replace('\\', '/', $_SERVER[$key]);
            }
        }

        foreach ($candidates as $raw) {
            $normalized = self::normalizePathUnderBase($raw, $base);
            if ($normalized !== null) {
                return $normalized;
            }
        }

        return '/';
    }

    private static function normalizePathUnderBase(string $path, string $base): ?string
    {
        $path = str_replace('\\', '/', $path);

        if ($base !== '') {
            if ($path === $base || $path === $base . '/') {
                return '/';
            }
            if (str_starts_with($path, $base . '/')) {
                $path = substr($path, strlen($base)) ?: '/';
            }
        }

        if (str_ends_with($path, '/index.php')) {
            $path = rtrim(dirname($path), '/');
            if ($path === '' || $path === '.') {
                return '/';
            }
        }
        if ($path === '/index.php') {
            return '/';
        }

        if ($path === '' || $path[0] !== '/') {
            $path = '/' . $path;
        }
        $path = rtrim($path, '/') ?: '/';

        // Rota já relativa (ex.: /login vindo do REDIRECT_URL sem prefixo da pasta).
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base)) ?: '/';
            $path = rtrim($path, '/') ?: '/';
        }

        if ($path === '/' || $path === '/index.php') {
            return '/';
        }

        return $path;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public function postFlag(string $key): bool
    {
        return isset($_POST[$key]) && (string) $_POST[$key] === '1';
    }
}
