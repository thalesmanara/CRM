<?php

declare(strict_types=1);

namespace Revita\Crm\Models;

use PDO;
use Revita\Crm\Core\Database;

final class FieldDefinition
{
    public const OWNER_PAGE = 'page';

    /** @return list<array<string, mixed>> */
    public function listByPageId(int $pageId): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id, owner_type, owner_id, field_key, label_name, field_type, order_index
             FROM revita_crm_field_definitions
             WHERE owner_type = :ot AND owner_id = :oid
             ORDER BY order_index ASC, id ASC'
        );
        $stmt->execute(['ot' => self::OWNER_PAGE, 'oid' => $pageId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id, owner_type, owner_id, field_key, label_name, field_type, order_index
             FROM revita_crm_field_definitions WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    public function fieldKeyExistsOnPage(int $pageId, string $key, ?int $excludeId = null): bool
    {
        $key = trim($key);
        if ($key === '') {
            return false;
        }
        $pdo = Database::pdo();
        $sql = 'SELECT 1 FROM revita_crm_field_definitions
                WHERE owner_type = :ot AND owner_id = :oid AND field_key = :fk';
        $p = ['ot' => self::OWNER_PAGE, 'oid' => $pageId, 'fk' => $key];
        if ($excludeId !== null) {
            $sql .= ' AND id <> :ex';
            $p['ex'] = $excludeId;
        }
        $sql .= ' LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($p);
        return (bool) $stmt->fetchColumn();
    }

    public function nextOrderIndex(int $pageId): int
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT COALESCE(MAX(order_index), 0) + 1 AS n
             FROM revita_crm_field_definitions
             WHERE owner_type = :ot AND owner_id = :oid'
        );
        $stmt->execute(['ot' => self::OWNER_PAGE, 'oid' => $pageId]);
        return (int) $stmt->fetchColumn();
    }

    public function insert(
        int $pageId,
        string $fieldKey,
        string $label,
        string $fieldType,
        int $orderIndex
      ): int {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO revita_crm_field_definitions
             (owner_type, owner_id, field_key, label_name, field_type, order_index, updated_at)
             VALUES (:ot, :oid, :fk, :lb, :ft, :ord, NOW())'
        );
        $stmt->execute([
            'ot' => self::OWNER_PAGE,
            'oid' => $pageId,
            'fk' => $fieldKey,
            'lb' => $label,
            'ft' => $fieldType,
            'ord' => $orderIndex,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /** @param list<int> $orderedIds */
    public function reorderOnPage(int $pageId, array $orderedIds): void
    {
        $pdo = Database::pdo();
        $ord = 0;
        foreach ($orderedIds as $fid) {
            $fid = (int) $fid;
            if ($fid < 1) {
                continue;
            }
            $stmt = $pdo->prepare(
                'UPDATE revita_crm_field_definitions SET order_index = :ord, updated_at = NOW()
                 WHERE id = :id AND owner_type = :ot AND owner_id = :pid'
            );
            $stmt->execute(['ord' => $ord++, 'id' => $fid, 'ot' => self::OWNER_PAGE, 'pid' => $pageId]);
        }
    }

    public function deleteRow(int $id): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('DELETE FROM revita_crm_field_definitions WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
