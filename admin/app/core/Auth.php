<?php

declare(strict_types=1);

namespace Revita\Crm\Core;

final class Auth
{
    private const SESSION_USER = 'auth_user';

    public const LEVEL_ADMIN = 1;

    public const LEVEL_EDITOR = 2;

    /** @return array{id:int, login:string, level:int, email:string}|null */
    public static function user(): ?array
    {
        $u = Session::get(self::SESSION_USER);
        return is_array($u) && isset($u['id'], $u['login'], $u['level']) ? $u : null;
    }

    /** @param array{id:int, login:string, level:int, email?:string} $user */
    public static function login(array $user): void
    {
        Session::regenerate();
        Session::set(self::SESSION_USER, [
            'id' => (int) $user['id'],
            'login' => (string) $user['login'],
            'level' => (int) $user['level'],
            'email' => isset($user['email']) ? (string) $user['email'] : '',
        ]);
    }

    public static function logout(): void
    {
        Session::remove(self::SESSION_USER);
        Session::regenerate();
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function isAdmin(): bool
    {
        $u = self::user();
        return $u !== null && (int) $u['level'] === self::LEVEL_ADMIN;
    }

    public static function requireAuth(): void
    {
        if (!self::check()) {
            \Revita\Crm\Helpers\Url::redirect('/login');
        }
    }

    /** Apenas nível 1 (administrador). */
    public static function requireAdmin(): void
    {
        self::requireAuth();
        if (!self::isAdmin()) {
            Session::flash('error', 'Você não tem permissão para acessar esta área.');
            \Revita\Crm\Helpers\Url::redirect('/dashboard');
        }
    }
}
