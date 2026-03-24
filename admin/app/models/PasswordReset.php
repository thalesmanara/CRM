<?php

declare(strict_types=1);

namespace Revita\Crm\Models;

use PDO;
use Revita\Crm\Core\Database;

final class PasswordReset
{
    private const TTL_HOURS = 2;

    public static function hashToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    /** Insere novo token e retorna o token em texto puro (para o link). */
    public function createForUser(int $userId): string
    {
        $plain = bin2hex(random_bytes(32));
        $hash = self::hashToken($plain);
        $expires = date('Y-m-d H:i:s', time() + self::TTL_HOURS * 3600);

        $pdo = Database::pdo();
        $pdo->prepare('DELETE FROM revita_crm_password_resets WHERE user_id = :uid AND used_at IS NULL')
            ->execute(['uid' => $userId]);

        $stmt = $pdo->prepare(
            'INSERT INTO revita_crm_password_resets (user_id, token_hash, token_expires_at)
             VALUES (:uid, :th, :exp)'
        );
        $stmt->execute(['uid' => $userId, 'th' => $hash, 'exp' => $expires]);

        return $plain;
    }

    /** @return array{id:int,user_id:int,token_expires_at:string}|null */
    public function findValidByPlainToken(string $plainToken): ?array
    {
        $plainToken = trim($plainToken);
        if ($plainToken === '') {
            return null;
        }
        $hash = self::hashToken($plainToken);
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id, user_id, token_expires_at
             FROM revita_crm_password_resets
             WHERE token_hash = :h AND used_at IS NULL AND token_expires_at > NOW()
             LIMIT 1'
        );
        $stmt->execute(['h' => $hash]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    public function markUsed(int $resetId): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'UPDATE revita_crm_password_resets SET used_at = NOW() WHERE id = :id'
        );
        $stmt->execute(['id' => $resetId]);
    }
}
