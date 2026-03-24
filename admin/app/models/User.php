<?php

declare(strict_types=1);

namespace Revita\Crm\Models;

use PDO;
use Revita\Crm\Core\Database;

final class User
{
    public function findByLogin(string $login): ?array
    {
        $login = trim($login);
        if ($login === '') {
            return null;
        }
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id, login, email, password_hash, level, is_active
             FROM revita_crm_users WHERE login = :login LIMIT 1'
        );
        $stmt->execute(['login' => $login]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }
}
