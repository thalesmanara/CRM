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

    public function findByEmail(string $email): ?array
    {
        $email = trim(strtolower($email));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id, login, email, password_hash, level, is_active
             FROM revita_crm_users WHERE LOWER(email) = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    public function findById(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id, login, email, password_hash, level, is_active, created_at
             FROM revita_crm_users WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    /** @return list<array<string, mixed>> */
    public function allOrdered(): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->query(
            'SELECT id, login, email, level, is_active, created_at
             FROM revita_crm_users
             ORDER BY created_at DESC, id DESC'
        );
        return $stmt !== false ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function loginExists(string $login, ?int $excludeId = null): bool
    {
        $login = trim($login);
        if ($login === '') {
            return false;
        }
        $pdo = Database::pdo();
        $sql = 'SELECT 1 FROM revita_crm_users WHERE login = :login';
        $params = ['login' => $login];
        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude';
            $params['exclude'] = $excludeId;
        }
        $sql .= ' LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $email = trim(strtolower($email));
        if ($email === '') {
            return false;
        }
        $pdo = Database::pdo();
        $sql = 'SELECT 1 FROM revita_crm_users WHERE LOWER(email) = :email';
        $params = ['email' => $email];
        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude';
            $params['exclude'] = $excludeId;
        }
        $sql .= ' LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    public function countActiveAdmins(): int
    {
        $pdo = Database::pdo();
        $stmt = $pdo->query(
            "SELECT COUNT(*) FROM revita_crm_users WHERE level = 1 AND is_active = 1"
        );
        return (int) $stmt->fetchColumn();
    }

    public function insert(string $login, string $email, string $passwordHash, int $level, bool $active): int
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO revita_crm_users (login, email, password_hash, level, is_active)
             VALUES (:login, :email, :hash, :level, :active)'
        );
        $stmt->execute([
            'login' => $login,
            'email' => $email,
            'hash' => $passwordHash,
            'level' => $level,
            'active' => $active ? 1 : 0,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public function update(
        int $id,
        string $login,
        string $email,
        ?string $passwordHash,
        int $level,
        bool $active
    ): void {
        $pdo = Database::pdo();
        if ($passwordHash !== null) {
            $stmt = $pdo->prepare(
                'UPDATE revita_crm_users SET login = :login, email = :email, password_hash = :hash,
                 level = :level, is_active = :active WHERE id = :id'
            );
            $stmt->execute([
                'login' => $login,
                'email' => $email,
                'hash' => $passwordHash,
                'level' => $level,
                'active' => $active ? 1 : 0,
                'id' => $id,
            ]);
            return;
        }
        $stmt = $pdo->prepare(
            'UPDATE revita_crm_users SET login = :login, email = :email,
             level = :level, is_active = :active WHERE id = :id'
        );
        $stmt->execute([
            'login' => $login,
            'email' => $email,
            'level' => $level,
            'active' => $active ? 1 : 0,
            'id' => $id,
        ]);
    }

    public function updatePassword(int $id, string $passwordHash): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('UPDATE revita_crm_users SET password_hash = :hash WHERE id = :id');
        $stmt->execute(['hash' => $passwordHash, 'id' => $id]);
    }

    public function deleteById(int $id): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('DELETE FROM revita_crm_users WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
