<?php

declare(strict_types=1);

namespace Revita\Crm\Core;

final class Config
{
    /** @var array<string, mixed>|null */
    private static ?array $cache = null;

    public static function path(): string
    {
        return REVITA_CRM_ROOT . '/config/app.php';
    }

    /** @return array<string, mixed> */
    public static function load(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }
        $path = self::path();
        if (!is_file($path)) {
            self::$cache = ['installed' => false];
            return self::$cache;
        }
        /** @var array<string, mixed> $data */
        $data = require $path;
        self::$cache = $data;
        return self::$cache;
    }

    public static function isInstalled(): bool
    {
        $c = self::load();
        return !empty($c['installed']);
    }

    /** @return array<string, string> */
    public static function db(): array
    {
        $c = self::load();
        /** @var array<string, string> $db */
        $db = $c['db'] ?? [];
        return $db;
    }
}
