<?php

declare(strict_types=1);

namespace Revita\Crm\Core;

final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (string) $_SERVER['SERVER_PORT'] === '443');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => \Revita\Crm\Helpers\Url::scriptBasePath() . '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_name('REVITA_CRMSESSID');
        session_start();
    }

    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, ?string $message = null): ?string
    {
        if ($message !== null) {
            if (!isset($_SESSION['_flash']) || !is_array($_SESSION['_flash'])) {
                $_SESSION['_flash'] = [];
            }
            $_SESSION['_flash'][$key] = $message;
            return null;
        }
        $bag = isset($_SESSION['_flash']) && is_array($_SESSION['_flash']) ? $_SESSION['_flash'] : [];
        $msg = $bag[$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $msg !== null ? (string) $msg : null;
    }
}
