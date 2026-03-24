<?php

declare(strict_types=1);

namespace Revita\Crm\Models;

use PDO;
use Revita\Crm\Core\Database;

final class Page
{
    /** @return list<array<string, mixed>> */
    public function all(): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->query(
            'SELECT id, title, slug, status, created_at, updated_at
             FROM revita_crm_pages
             ORDER BY updated_at DESC, id DESC'
        );
        return $stmt !== false ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /** @return list<array<string, mixed>> */
    public function allPublished(): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->query(
            "SELECT id, title, slug, status, created_at, updated_at
             FROM revita_crm_pages
             WHERE status = 'published'
             ORDER BY title ASC, id ASC"
        );
        return $stmt !== false ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function findById(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id, title, slug, status, created_at, updated_at
             FROM revita_crm_pages WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    public function findBySlug(string $slug): ?array
    {
        $slug = trim($slug);
        if ($slug === '') {
            return null;
        }
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id, title, slug, status, created_at, updated_at
             FROM revita_crm_pages WHERE slug = :s LIMIT 1'
        );
        $stmt->execute(['s' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $slug = trim($slug);
        if ($slug === '') {
            return false;
        }
        $pdo = Database::pdo();
        $sql = 'SELECT 1 FROM revita_crm_pages WHERE slug = :s';
        $p = ['s' => $slug];
        if ($excludeId !== null) {
            $sql .= ' AND id <> :ex';
            $p['ex'] = $excludeId;
        }
        $sql .= ' LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($p);
        return (bool) $stmt->fetchColumn();
    }

    public function insert(string $title, string $slug, string $status): int
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO revita_crm_pages (title, slug, status, updated_at)
             VALUES (:t, :s, :st, NOW())'
        );
        $stmt->execute(['t' => $title, 's' => $slug, 'st' => $status]);
        return (int) $pdo->lastInsertId();
    }

    public function update(int $id, string $title, string $slug, string $status): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'UPDATE revita_crm_pages SET title = :t, slug = :s, status = :st, updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute(['id' => $id, 't' => $title, 's' => $slug, 'st' => $status]);
    }

    public function delete(int $id): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('DELETE FROM revita_crm_pages WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
