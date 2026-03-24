<?php

declare(strict_types=1);

namespace Revita\Crm\Core;

use PDO;

final class Database
{
    private static ?PDO $pdo = null;

    public static function fromConfig(array $db, bool $multiStatements = false): PDO
    {
        $host = $db['host'] ?? 'localhost';
        $name = $db['name'] ?? '';
        $user = $db['user'] ?? '';
        $pass = $db['password'] ?? '';
        $charset = $db['charset'] ?? 'utf8mb4';

        $dsn = 'mysql:host=' . $host . ';dbname=' . $name . ';charset=' . $charset;
        $opts = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        if ($multiStatements) {
            $opts[PDO::MYSQL_ATTR_MULTI_STATEMENTS] = true;
        }
        return new PDO($dsn, $user, $pass, $opts);
    }

    public static function pdo(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }
        if (!Config::isInstalled()) {
            throw new \RuntimeException('Banco não configurado.');
        }
        self::$pdo = self::fromConfig(Config::db(), false);
        return self::$pdo;
    }

    public static function reset(): void
    {
        self::$pdo = null;
    }
}
