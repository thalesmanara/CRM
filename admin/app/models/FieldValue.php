<?php

declare(strict_types=1);

namespace Revita\Crm\Models;

use PDO;
use Revita\Crm\Core\Database;

final class FieldValue
{
    public function get(int $fieldDefinitionId): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT field_definition_id, value_text, value_url, value_media_ids_json, value_mixed_json
             FROM revita_crm_field_values WHERE field_definition_id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $fieldDefinitionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    public function upsert(
        int $fieldDefinitionId,
        ?string $text,
        ?string $url,
        ?string $mediaIdsJson,
        ?string $mixedJson
    ): void {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO revita_crm_field_values
             (field_definition_id, value_text, value_url, value_media_ids_json, value_mixed_json)
             VALUES (:id, :vt, :vu, :vm, :vx)
             ON DUPLICATE KEY UPDATE
               value_text = VALUES(value_text),
               value_url = VALUES(value_url),
               value_media_ids_json = VALUES(value_media_ids_json),
               value_mixed_json = VALUES(value_mixed_json),
               updated_at = CURRENT_TIMESTAMP'
        );
        $stmt->execute([
            'id' => $fieldDefinitionId,
            'vt' => $text,
            'vu' => $url,
            'vm' => $mediaIdsJson,
            'vx' => $mixedJson,
        ]);
    }

    public function ensureRowExists(int $fieldDefinitionId): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT IGNORE INTO revita_crm_field_values (field_definition_id) VALUES (:id)'
        );
        $stmt->execute(['id' => $fieldDefinitionId]);
    }

    public function deleteForField(int $fieldDefinitionId): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('DELETE FROM revita_crm_field_values WHERE field_definition_id = :id');
        $stmt->execute(['id' => $fieldDefinitionId]);
    }
}
