<?php

declare(strict_types=1);

namespace Revita\Crm\Models;

use PDO;
use Revita\Crm\Core\Database;

final class Media
{
    public function findById(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id, media_type, relative_path, original_name, stored_name, mime_type, size_bytes
             FROM revita_crm_media WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    /** @param list<int> $ids preserva ordem */
    public function findByIdsOrdered(array $ids): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids), static fn (int $x) => $x > 0));
        if ($ids === []) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            "SELECT id, media_type, relative_path FROM revita_crm_media WHERE id IN ($placeholders)"
        );
        $stmt->execute($ids);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $map = [];
        foreach ($rows as $r) {
            $map[(int) $r['id']] = $r;
        }
        $out = [];
        foreach ($ids as $id) {
            if (isset($map[$id])) {
                $out[] = $map[$id];
            }
        }
        return $out;
    }

    public function insert(
        string $type,
        string $relativePath,
        string $originalName,
        string $storedName,
        ?string $mime,
        ?int $sizeBytes,
        ?int $uploadedBy
    ): int {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO revita_crm_media (media_type, relative_path, original_name, stored_name, mime_type, size_bytes, uploaded_by)
             VALUES (:t, :rp, :on, :sn, :mime, :sz, :ub)'
        );
        $stmt->execute([
            't' => $type,
            'rp' => $relativePath,
            'on' => $originalName,
            'sn' => $storedName,
            'mime' => $mime,
            'sz' => $sizeBytes,
            'ub' => $uploadedBy,
        ]);
        return (int) $pdo->lastInsertId();
    }
}
