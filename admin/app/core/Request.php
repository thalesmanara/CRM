<?php

declare(strict_types=1);

namespace Revita\Crm\Core;

final class Request
{
    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);
        $path = $path !== false ? (string) $path : '/';
        $path = str_replace('\\', '/', $path);

        $base = \Revita\Crm\Helpers\Url::scriptBasePath();
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base)) ?: '/';
        }
        if ($path === '' || $path[0] !== '/') {
            $path = '/' . $path;
        }
        $path = rtrim($path, '/') ?: '/';
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
}
