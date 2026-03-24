<?php

declare(strict_types=1);

namespace Revita\Crm\Models;

use PDO;
use Revita\Crm\Core\Database;

final class Repeater
{
    public function createDefinitionForField(int $fieldDefinitionId): int
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO revita_crm_repeater_definitions (field_definition_id)
             VALUES (:fid)'
        );
        $stmt->execute(['fid' => $fieldDefinitionId]);
        return (int) $pdo->lastInsertId();
    }

    public function findDefinitionByFieldDefId(int $fieldDefinitionId): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id, field_definition_id FROM revita_crm_repeater_definitions
             WHERE field_definition_id = :fid LIMIT 1'
        );
        $stmt->execute(['fid' => $fieldDefinitionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    /** Remove repetidor completo vinculado ao campo */
    public function deleteCascadeByFieldDefinitionId(int $fieldDefinitionId): void
    {
        $def = $this->findDefinitionByFieldDefId($fieldDefinitionId);
        if ($def === null) {
            return;
        }
        $repId = (int) $def['id'];
        $pdo = Database::pdo();
        $items = $this->listItemIds($repId);
        if ($items !== []) {
            $in = implode(',', array_map('intval', $items));
            $pdo->exec("DELETE FROM revita_crm_repeater_item_values WHERE repeater_item_id IN ($in)");
        }
        $st = $pdo->prepare('DELETE FROM revita_crm_repeater_items WHERE repeater_definition_id = :r');
        $st->execute(['r' => $repId]);
        $st = $pdo->prepare('DELETE FROM revita_crm_repeater_subfield_definitions WHERE repeater_definition_id = :r');
        $st->execute(['r' => $repId]);
        $st = $pdo->prepare('DELETE FROM revita_crm_repeater_definitions WHERE id = :r');
        $st->execute(['r' => $repId]);
    }

    /** @return list<int> */
    private function listItemIds(int $repeaterDefinitionId): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id FROM revita_crm_repeater_items WHERE repeater_definition_id = :r'
        );
        $stmt->execute(['r' => $repeaterDefinitionId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /** @return list<array<string,mixed>> */
    public function listSubfields(int $repeaterDefinitionId): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id, repeater_definition_id, field_key, label_name, field_type, order_index
             FROM revita_crm_repeater_subfield_definitions
             WHERE repeater_definition_id = :r
             ORDER BY order_index ASC, id ASC'
        );
        $stmt->execute(['r' => $repeaterDefinitionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function nextSubfieldOrder(int $repeaterDefinitionId): int
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT COALESCE(MAX(order_index), 0) + 1 FROM revita_crm_repeater_subfield_definitions
             WHERE repeater_definition_id = :r'
        );
        $stmt->execute(['r' => $repeaterDefinitionId]);
        return (int) $stmt->fetchColumn();
    }

    public function subfieldKeyExists(int $repeaterDefinitionId, string $key, ?int $excludeId = null): bool
    {
        $key = trim($key);
        if ($key === '') {
            return false;
        }
        $pdo = Database::pdo();
        $sql = 'SELECT 1 FROM revita_crm_repeater_subfield_definitions
                WHERE repeater_definition_id = :r AND field_key = :k';
        $p = ['r' => $repeaterDefinitionId, 'k' => $key];
        if ($excludeId !== null) {
            $sql .= ' AND id <> :ex';
            $p['ex'] = $excludeId;
        }
        $sql .= ' LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($p);
        return (bool) $stmt->fetchColumn();
    }

    public function insertSubfield(
        int $repeaterDefinitionId,
        string $key,
        string $label,
        string $type,
        int $order
    ): int {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO revita_crm_repeater_subfield_definitions
             (repeater_definition_id, field_key, label_name, field_type, order_index, updated_at)
             VALUES (:r, :k, :lb, :t, :o, NOW())'
        );
        $stmt->execute(['r' => $repeaterDefinitionId, 'k' => $key, 'lb' => $label, 't' => $type, 'o' => $order]);
        return (int) $pdo->lastInsertId();
    }

    public function deleteSubfield(int $subfieldId): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('DELETE FROM revita_crm_repeater_item_values WHERE repeater_subfield_definition_id = :s');
        $stmt->execute(['s' => $subfieldId]);
        $stmt = $pdo->prepare('DELETE FROM revita_crm_repeater_subfield_definitions WHERE id = :s');
        $stmt->execute(['s' => $subfieldId]);
    }

    public function findSubfieldById(int $id): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT * FROM revita_crm_repeater_subfield_definitions WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    /** @return list<array<string,mixed>> */
    public function listItems(int $repeaterDefinitionId): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id, repeater_definition_id, item_order
             FROM revita_crm_repeater_items
             WHERE repeater_definition_id = :r
             ORDER BY item_order ASC, id ASC'
        );
        $stmt->execute(['r' => $repeaterDefinitionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addItem(int $repeaterDefinitionId): int
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT COALESCE(MAX(item_order), 0) + 1 FROM revita_crm_repeater_items
             WHERE repeater_definition_id = :r'
        );
        $stmt->execute(['r' => $repeaterDefinitionId]);
        $next = (int) $stmt->fetchColumn();
        $stmt = $pdo->prepare(
            'INSERT INTO revita_crm_repeater_items (repeater_definition_id, item_order)
             VALUES (:r, :o)'
        );
        $stmt->execute(['r' => $repeaterDefinitionId, 'o' => $next]);
        return (int) $pdo->lastInsertId();
    }

    public function findItemById(int $itemId): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id, repeater_definition_id, item_order FROM revita_crm_repeater_items WHERE id = :i LIMIT 1'
        );
        $stmt->execute(['i' => $itemId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    public function deleteItem(int $itemId): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('DELETE FROM revita_crm_repeater_item_values WHERE repeater_item_id = :i');

        $stmt->execute(['i' => $itemId]);
        $stmt = $pdo->prepare('DELETE FROM revita_crm_repeater_items WHERE id = :i');
        $stmt->execute(['i' => $itemId]);
    }

    /** @param list<int> $orderedItemIds */
    public function reorderItems(int $repeaterDefinitionId, array $orderedItemIds): void
    {
        $pdo = Database::pdo();
        $ord = 0;
        foreach ($orderedItemIds as $iid) {
            $iid = (int) $iid;
            if ($iid < 1) {
                continue;
            }
            $stmt = $pdo->prepare(
                'UPDATE revita_crm_repeater_items SET item_order = :o
                 WHERE id = :id AND repeater_definition_id = :r'
            );
            $stmt->execute(['o' => $ord++, 'id' => $iid, 'r' => $repeaterDefinitionId]);
        }
    }

    public function upsertItemValue(
        int $itemId,
        int $subfieldDefinitionId,
        ?string $text,
        ?string $url,
        ?string $mediaJson,
        ?string $mixedJson
    ): void {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO revita_crm_repeater_item_values
             (repeater_item_id, repeater_subfield_definition_id, value_text, value_url, value_media_ids_json, value_mixed_json)
             VALUES (:i, :s, :vt, :vu, :vm, :vx)
             ON DUPLICATE KEY UPDATE
               value_text = VALUES(value_text),
               value_url = VALUES(value_url),
               value_media_ids_json = VALUES(value_media_ids_json),
               value_mixed_json = VALUES(value_mixed_json),
               updated_at = CURRENT_TIMESTAMP'
        );
        $stmt->execute([
            'i' => $itemId,
            's' => $subfieldDefinitionId,
            'vt' => $text,
            'vu' => $url,
            'vm' => $mediaJson,
            'vx' => $mixedJson,
        ]);
    }

    /** @return array<int, array<string,mixed>> map subfieldId => row */
    public function valuesMapForItem(int $itemId): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT repeater_subfield_definition_id, value_text, value_url, value_media_ids_json, value_mixed_json
             FROM revita_crm_repeater_item_values WHERE repeater_item_id = :i'
        );
        $stmt->execute(['i' => $itemId]);
        $out = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $out[(int) $row['repeater_subfield_definition_id']] = $row;
        }
        return $out;
    }
}
