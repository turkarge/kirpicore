<?php

function kirpi_ai_schema_registry_ready(): bool
{
    return db_table_exists('ai_schema_entities')
        && db_table_exists('ai_schema_fields');
}

function kirpi_ai_audit_table_ready(): bool
{
    return db_table_exists('ai_audit_logs');
}

function kirpi_ai_models_table_ready(): bool
{
    return db_table_exists('ai_model_adapters');
}

function kirpi_ai_schema_index_ready(): bool
{
    return db_table_exists('ai_schema_index');
}

function kirpi_ai_schema_count(): int
{
    if (!kirpi_ai_schema_registry_ready()) {
        return 0;
    }

    try {
        $stmt = db()->query('SELECT COUNT(*) FROM ai_schema_entities WHERE is_active = 1');
        return (int) $stmt->fetchColumn();
    } catch (Throwable $e) {
        error_log('ai schema count error: ' . $e->getMessage());
        return 0;
    }
}

function kirpi_ai_field_count(): int
{
    if (!kirpi_ai_schema_registry_ready()) {
        return 0;
    }

    try {
        $stmt = db()->query('SELECT COUNT(*) FROM ai_schema_fields WHERE is_active = 1');
        return (int) $stmt->fetchColumn();
    } catch (Throwable $e) {
        error_log('ai field count error: ' . $e->getMessage());
        return 0;
    }
}

function kirpi_ai_schema_index_count(): int
{
    if (!kirpi_ai_schema_index_ready()) {
        return 0;
    }

    try {
        $stmt = db()->query('SELECT COUNT(*) FROM ai_schema_index');
        return (int) $stmt->fetchColumn();
    } catch (Throwable $e) {
        error_log('ai schema index count error: ' . $e->getMessage());
        return 0;
    }
}

function kirpi_ai_schema_manifest_files(): array
{
    $files = [];
    $moduleDirs = glob(BASE_PATH . '/modules/*', GLOB_ONLYDIR) ?: [];
    sort($moduleDirs);

    foreach ($moduleDirs as $moduleDir) {
        $manifestPath = rtrim($moduleDir, '/\\') . '/ai/schema.json';
        if (is_file($manifestPath)) {
            $files[] = $manifestPath;
        }
    }

    return $files;
}

function kirpi_ai_schema_manifest_count(): int
{
    return count(kirpi_ai_schema_manifest_files());
}

function kirpi_ai_normalize_schema_manifest(string $filePath): array
{
    $raw = (string) file_get_contents($filePath);
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return [
            'status' => 'error',
            'message' => 'invalid_json',
            'entities' => [],
        ];
    }

    $moduleKey = trim((string) ($decoded['module'] ?? basename(dirname(dirname($filePath)))));
    $entities = [];

    foreach ((array) ($decoded['entities'] ?? []) as $entity) {
        if (!is_array($entity)) {
            continue;
        }

        $entityKey = trim((string) ($entity['entity'] ?? $entity['entity_key'] ?? ''));
        $tableName = trim((string) ($entity['table'] ?? $entity['table_name'] ?? ''));
        if ($moduleKey === '' || $entityKey === '' || $tableName === '') {
            continue;
        }

        $fields = [];
        foreach ((array) ($entity['fields'] ?? []) as $field) {
            if (!is_array($field)) {
                continue;
            }

            $fieldName = trim((string) ($field['name'] ?? $field['field_name'] ?? ''));
            if ($fieldName === '') {
                continue;
            }

            $fields[] = [
                'name' => $fieldName,
                'type' => trim((string) ($field['type'] ?? $field['field_type'] ?? '')),
                'description' => trim((string) ($field['description'] ?? '')),
                'is_sensitive' => !empty($field['is_sensitive']) ? 1 : 0,
                'is_filterable' => array_key_exists('is_filterable', $field) ? (!empty($field['is_filterable']) ? 1 : 0) : 1,
                'metadata' => is_array($field['metadata'] ?? null) ? (array) $field['metadata'] : [],
            ];
        }

        $entities[] = [
            'module' => $moduleKey,
            'entity' => $entityKey,
            'table' => $tableName,
            'description' => trim((string) ($entity['description'] ?? '')),
            'permission' => trim((string) ($entity['permission'] ?? $entity['permission_slug'] ?? '')),
            'metadata' => is_array($entity['metadata'] ?? null) ? (array) $entity['metadata'] : [],
            'fields' => $fields,
        ];
    }

    return [
        'status' => 'success',
        'message' => null,
        'entities' => $entities,
    ];
}

function kirpi_ai_publish_schema_entity(array $entity): array
{
    if (!kirpi_ai_schema_registry_ready()) {
        return [
            'status' => 'error',
            'message' => 'schema_registry_not_ready',
        ];
    }

    $moduleKey = trim((string) ($entity['module'] ?? ''));
    $entityKey = trim((string) ($entity['entity'] ?? ''));
    $tableName = trim((string) ($entity['table'] ?? ''));

    if ($moduleKey === '' || $entityKey === '' || $tableName === '') {
        return [
            'status' => 'error',
            'message' => 'invalid_entity',
        ];
    }

    $user = current_user();
    $userId = (int) ($user['id'] ?? 0);
    $metadataJson = null;
    if (!empty($entity['metadata']) && is_array($entity['metadata'])) {
        $encoded = json_encode($entity['metadata'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $metadataJson = $encoded === false ? null : $encoded;
    }

    db()->beginTransaction();

    try {
        $entityStmt = db()->prepare("
            INSERT INTO ai_schema_entities (
                module_key,
                entity_key,
                table_name,
                description,
                permission_slug,
                metadata_json,
                is_active,
                created_by,
                updated_by
            ) VALUES (
                :module_key,
                :entity_key,
                :table_name,
                :description,
                :permission_slug,
                :metadata_json,
                1,
                :created_by,
                :updated_by
            )
            ON DUPLICATE KEY UPDATE
                table_name = VALUES(table_name),
                description = VALUES(description),
                permission_slug = VALUES(permission_slug),
                metadata_json = VALUES(metadata_json),
                is_active = 1,
                updated_by = VALUES(updated_by),
                updated_at = CURRENT_TIMESTAMP
        ");

        $permission = trim((string) ($entity['permission'] ?? ''));
        $entityStmt->execute([
            ':module_key' => mb_substr($moduleKey, 0, 80),
            ':entity_key' => mb_substr($entityKey, 0, 120),
            ':table_name' => mb_substr($tableName, 0, 120),
            ':description' => mb_substr(trim((string) ($entity['description'] ?? '')), 0, 500) ?: null,
            ':permission_slug' => $permission !== '' ? mb_substr($permission, 0, 150) : null,
            ':metadata_json' => $metadataJson,
            ':created_by' => $userId > 0 ? $userId : null,
            ':updated_by' => $userId > 0 ? $userId : null,
        ]);

        $lookupStmt = db()->prepare("
            SELECT id
            FROM ai_schema_entities
            WHERE module_key = :module_key
              AND entity_key = :entity_key
            LIMIT 1
        ");
        $lookupStmt->execute([
            ':module_key' => $moduleKey,
            ':entity_key' => $entityKey,
        ]);
        $entityId = (int) $lookupStmt->fetchColumn();

        if ($entityId <= 0) {
            throw new RuntimeException('schema entity lookup failed');
        }

        $publishedFields = [];
        $fieldStmt = db()->prepare("
            INSERT INTO ai_schema_fields (
                entity_id,
                field_name,
                field_type,
                description,
                is_sensitive,
                is_filterable,
                is_active,
                metadata_json
            ) VALUES (
                :entity_id,
                :field_name,
                :field_type,
                :description,
                :is_sensitive,
                :is_filterable,
                1,
                :metadata_json
            )
            ON DUPLICATE KEY UPDATE
                field_type = VALUES(field_type),
                description = VALUES(description),
                is_sensitive = VALUES(is_sensitive),
                is_filterable = VALUES(is_filterable),
                is_active = 1,
                metadata_json = VALUES(metadata_json),
                updated_at = CURRENT_TIMESTAMP
        ");

        foreach ((array) ($entity['fields'] ?? []) as $field) {
            if (!is_array($field)) {
                continue;
            }

            $fieldName = trim((string) ($field['name'] ?? ''));
            if ($fieldName === '') {
                continue;
            }

            $fieldMetadataJson = null;
            if (!empty($field['metadata']) && is_array($field['metadata'])) {
                $encoded = json_encode($field['metadata'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $fieldMetadataJson = $encoded === false ? null : $encoded;
            }

            $fieldStmt->execute([
                ':entity_id' => $entityId,
                ':field_name' => mb_substr($fieldName, 0, 120),
                ':field_type' => mb_substr(trim((string) ($field['type'] ?? '')), 0, 80) ?: null,
                ':description' => mb_substr(trim((string) ($field['description'] ?? '')), 0, 500) ?: null,
                ':is_sensitive' => !empty($field['is_sensitive']) ? 1 : 0,
                ':is_filterable' => !empty($field['is_filterable']) ? 1 : 0,
                ':metadata_json' => $fieldMetadataJson,
            ]);

            $publishedFields[] = $fieldName;
        }

        if (!empty($publishedFields)) {
            $placeholders = implode(', ', array_fill(0, count($publishedFields), '?'));
            $deactivateStmt = db()->prepare("
                UPDATE ai_schema_fields
                SET is_active = 0,
                    updated_at = CURRENT_TIMESTAMP
                WHERE entity_id = ?
                  AND field_name NOT IN ({$placeholders})
            ");
            $deactivateStmt->execute(array_merge([$entityId], $publishedFields));
        }

        db()->commit();

        return [
            'status' => 'success',
            'entity_id' => $entityId,
            'field_count' => count($publishedFields),
        ];
    } catch (Throwable $e) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }

        error_log('ai schema publish error: ' . $e->getMessage());

        return [
            'status' => 'error',
            'message' => $e->getMessage(),
        ];
    }
}

function kirpi_ai_sync_schema_registry_from_manifests(): array
{
    if (!kirpi_ai_schema_registry_ready()) {
        return [
            'status' => 'error',
            'message' => 'schema_registry_not_ready',
            'files' => [],
            'entity_count' => 0,
            'field_count' => 0,
            'errors' => [],
        ];
    }

    $files = kirpi_ai_schema_manifest_files();
    $entityCount = 0;
    $fieldCount = 0;
    $errors = [];
    $installedFiles = [];

    foreach ($files as $filePath) {
        $relative = str_replace(BASE_PATH . '/', '', str_replace('\\', '/', $filePath));
        $manifest = kirpi_ai_normalize_schema_manifest($filePath);
        if (($manifest['status'] ?? '') !== 'success') {
            $errors[] = [
                'file' => $relative,
                'message' => (string) ($manifest['message'] ?? 'manifest_error'),
            ];
            continue;
        }

        $fileEntities = 0;
        $fileFields = 0;
        foreach ((array) ($manifest['entities'] ?? []) as $entity) {
            $result = kirpi_ai_publish_schema_entity((array) $entity);
            if (($result['status'] ?? '') !== 'success') {
                $errors[] = [
                    'file' => $relative,
                    'entity' => (string) ($entity['entity'] ?? ''),
                    'message' => (string) ($result['message'] ?? 'publish_error'),
                ];
                continue;
            }

            $fileEntities++;
            $fileFields += (int) ($result['field_count'] ?? 0);
        }

        $entityCount += $fileEntities;
        $fieldCount += $fileFields;
        $installedFiles[] = [
            'file' => $relative,
            'entities' => $fileEntities,
            'fields' => $fileFields,
        ];
    }

    $status = empty($errors) ? 'success' : 'partial';
    $indexResult = kirpi_ai_rebuild_schema_index();

    kirpi_ai_log_operation('schema_sync', $status === 'success' ? 'success' : 'failed', [
        'files' => $installedFiles,
        'errors' => $errors,
        'entity_count' => $entityCount,
        'field_count' => $fieldCount,
        'index_status' => (string) ($indexResult['status'] ?? 'skipped'),
        'index_count' => (int) ($indexResult['index_count'] ?? 0),
    ], null, 'schema_registry', null);

    return [
        'status' => $status,
        'message' => null,
        'files' => $installedFiles,
        'entity_count' => $entityCount,
        'field_count' => $fieldCount,
        'index_status' => (string) ($indexResult['status'] ?? 'skipped'),
        'index_count' => (int) ($indexResult['index_count'] ?? 0),
        'errors' => $errors,
    ];
}

function kirpi_ai_index_text_entries(string $text, string $sourceType, int $weight): array
{
    $tokens = kirpi_ai_tokenize_search($text);
    $entries = [];

    foreach ($tokens as $token) {
        $entries[] = [
            'token' => $token,
            'source_type' => $sourceType,
            'source_text' => mb_substr(trim($text), 0, 500),
            'weight' => $weight,
        ];
    }

    return $entries;
}

function kirpi_ai_index_metadata_entries(array $metadata, string $sourceType, int $weight): array
{
    $entries = [];

    foreach ((array) ($metadata['aliases'] ?? []) as $alias) {
        $entries = array_merge($entries, kirpi_ai_index_text_entries((string) $alias, $sourceType, $weight));
    }

    foreach ((array) ($metadata['keywords'] ?? []) as $keyword) {
        $entries = array_merge($entries, kirpi_ai_index_text_entries((string) $keyword, $sourceType, $weight));
    }

    return $entries;
}

function kirpi_ai_rebuild_schema_index(): array
{
    if (!kirpi_ai_schema_registry_ready()) {
        return [
            'status' => 'error',
            'message' => 'schema_registry_not_ready',
            'index_count' => 0,
        ];
    }

    if (!kirpi_ai_schema_index_ready()) {
        return [
            'status' => 'skipped',
            'message' => 'schema_index_not_ready',
            'index_count' => 0,
        ];
    }

    try {
        $stmt = db()->query("
            SELECT
                e.id AS entity_id,
                e.module_key,
                e.entity_key,
                e.table_name,
                e.description AS entity_description,
                e.permission_slug,
                e.metadata_json AS entity_metadata_json,
                f.id AS field_id,
                f.field_name,
                f.field_type,
                f.description AS field_description,
                f.is_sensitive,
                f.is_filterable,
                f.metadata_json AS field_metadata_json
            FROM ai_schema_entities e
            LEFT JOIN ai_schema_fields f ON f.entity_id = e.id AND f.is_active = 1
            WHERE e.is_active = 1
            ORDER BY e.module_key ASC, e.entity_key ASC, f.field_name ASC
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        db()->beginTransaction();
        db()->exec('DELETE FROM ai_schema_index');

        $insert = db()->prepare("
            INSERT INTO ai_schema_index (
                entity_id,
                field_id,
                module_key,
                entity_key,
                table_name,
                field_name,
                token,
                source_type,
                source_text,
                weight
            ) VALUES (
                :entity_id,
                :field_id,
                :module_key,
                :entity_key,
                :table_name,
                :field_name,
                :token,
                :source_type,
                :source_text,
                :weight
            )
        ");

        $seen = [];
        $indexCount = 0;
        foreach ($rows as $row) {
            $entityId = (int) ($row['entity_id'] ?? 0);
            if ($entityId <= 0) {
                continue;
            }

            $fieldId = (int) ($row['field_id'] ?? 0);
            $fieldName = trim((string) ($row['field_name'] ?? ''));
            $isSensitive = (int) ($row['is_sensitive'] ?? 0) === 1;
            $entityMetadata = json_decode((string) ($row['entity_metadata_json'] ?? ''), true) ?: [];
            $fieldMetadata = json_decode((string) ($row['field_metadata_json'] ?? ''), true) ?: [];

            $entries = [];
            $entityKey = (string) ($row['entity_key'] ?? '');
            $moduleKey = (string) ($row['module_key'] ?? '');
            $tableName = (string) ($row['table_name'] ?? '');

            $entries = array_merge($entries, kirpi_ai_index_text_entries($moduleKey, 'module', 8));
            $entries = array_merge($entries, kirpi_ai_index_text_entries($entityKey, 'entity', 14));
            $entries = array_merge($entries, kirpi_ai_index_text_entries($tableName, 'table', 10));
            $entries = array_merge($entries, kirpi_ai_index_text_entries((string) ($row['entity_description'] ?? ''), 'entity_description', 6));
            $entries = array_merge($entries, kirpi_ai_index_metadata_entries($entityMetadata, 'entity_alias', 16));

            if ($fieldId > 0 && $fieldName !== '' && !$isSensitive) {
                $entries = array_merge($entries, kirpi_ai_index_text_entries($fieldName, 'field', 12));
                $entries = array_merge($entries, kirpi_ai_index_text_entries((string) ($row['field_type'] ?? ''), 'field_type', 3));
                $entries = array_merge($entries, kirpi_ai_index_text_entries((string) ($row['field_description'] ?? ''), 'field_description', 5));
                $entries = array_merge($entries, kirpi_ai_index_metadata_entries($fieldMetadata, 'field_alias', 14));
            }

            foreach ($entries as $entry) {
                $token = trim((string) ($entry['token'] ?? ''));
                $sourceType = trim((string) ($entry['source_type'] ?? ''));
                if ($token === '' || $sourceType === '') {
                    continue;
                }

                $dedupeKey = implode('|', [
                    $entityId,
                    $fieldId > 0 && !$isSensitive ? $fieldId : 0,
                    $token,
                    $sourceType,
                ]);
                if (isset($seen[$dedupeKey])) {
                    continue;
                }
                $seen[$dedupeKey] = true;

                $insert->execute([
                    ':entity_id' => $entityId,
                    ':field_id' => $fieldId > 0 && !$isSensitive ? $fieldId : null,
                    ':module_key' => mb_substr($moduleKey, 0, 80),
                    ':entity_key' => mb_substr($entityKey, 0, 120),
                    ':table_name' => mb_substr($tableName, 0, 120),
                    ':field_name' => $fieldId > 0 && !$isSensitive ? mb_substr($fieldName, 0, 120) : null,
                    ':token' => mb_substr($token, 0, 120),
                    ':source_type' => mb_substr($sourceType, 0, 40),
                    ':source_text' => mb_substr((string) ($entry['source_text'] ?? ''), 0, 500) ?: null,
                    ':weight' => max(1, min(65535, (int) ($entry['weight'] ?? 1))),
                ]);
                $indexCount++;
            }
        }

        db()->commit();

        kirpi_ai_log_operation('schema_index_rebuild', 'success', [
            'index_count' => $indexCount,
        ], null, 'schema_registry', null);

        return [
            'status' => 'success',
            'message' => null,
            'index_count' => $indexCount,
        ];
    } catch (Throwable $e) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }

        error_log('ai schema index rebuild error: ' . $e->getMessage());

        return [
            'status' => 'error',
            'message' => $e->getMessage(),
            'index_count' => 0,
        ];
    }
}

function kirpi_ai_audit_count(): int
{
    if (!kirpi_ai_audit_table_ready()) {
        return 0;
    }

    try {
        $stmt = db()->query('SELECT COUNT(*) FROM ai_audit_logs');
        return (int) $stmt->fetchColumn();
    } catch (Throwable $e) {
        error_log('ai audit count error: ' . $e->getMessage());
        return 0;
    }
}

function kirpi_ai_list_audit_logs(array $filters = [], int $page = 1, int $limit = 25): array
{
    if (!kirpi_ai_audit_table_ready()) {
        return [
            'records' => [],
            'total' => 0,
            'page' => 1,
            'limit' => $limit,
            'total_pages' => 0,
        ];
    }

    $page = max(1, $page);
    $limit = max(1, min(100, $limit));
    $offset = ($page - 1) * $limit;
    $where = [];
    $params = [];

    $status = trim((string) ($filters['status'] ?? ''));
    if ($status !== '') {
        $where[] = 'l.status = :status';
        $params[':status'] = $status;
    }

    $action = trim((string) ($filters['action'] ?? ''));
    if ($action !== '') {
        $where[] = 'l.action_key LIKE :action_key';
        $params[':action_key'] = '%' . $action . '%';
    }

    $modelAdapter = trim((string) ($filters['model_adapter'] ?? ''));
    if ($modelAdapter !== '') {
        $where[] = 'l.model_adapter LIKE :model_adapter';
        $params[':model_adapter'] = '%' . $modelAdapter . '%';
    }

    $entityType = trim((string) ($filters['entity_type'] ?? ''));
    if ($entityType !== '') {
        $where[] = 'l.entity_type LIKE :entity_type';
        $params[':entity_type'] = '%' . $entityType . '%';
    }

    $userId = (int) ($filters['user_id'] ?? 0);
    if ($userId > 0) {
        $where[] = 'l.user_id = :user_id';
        $params[':user_id'] = $userId;
    }

    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    try {
        $countStmt = db()->prepare("
            SELECT COUNT(l.id)
            FROM ai_audit_logs l
            {$whereSql}
        ");
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        $stmt = db()->prepare("
            SELECT
                l.id,
                l.user_id,
                l.action_key,
                l.status,
                l.model_adapter,
                l.entity_type,
                l.entity_id,
                l.route_path,
                l.ip_address,
                l.details_json,
                l.created_at,
                u.name AS user_name
            FROM ai_audit_logs l
            LEFT JOIN users u ON u.id = l.user_id
            {$whereSql}
            ORDER BY l.id DESC
            LIMIT :limit_rows OFFSET :offset_rows
        ");
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit_rows', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset_rows', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'records' => $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [],
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => (int) ceil($total / $limit),
        ];
    } catch (Throwable $e) {
        error_log('ai audit list error: ' . $e->getMessage());

        return [
            'records' => [],
            'total' => 0,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => 0,
        ];
    }
}

function kirpi_ai_model_adapters(): array
{
    if (!kirpi_ai_models_table_ready()) {
        return [];
    }

    try {
        $stmt = db()->query("
            SELECT adapter_key, provider, model_name, adapter_type, is_enabled, is_external
            FROM ai_model_adapters
            ORDER BY is_enabled DESC, adapter_key ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        error_log('ai model adapter list error: ' . $e->getMessage());
        return [];
    }
}

function kirpi_ai_list_schema_entities(int $limit = 50): array
{
    if (!kirpi_ai_schema_registry_ready()) {
        return [];
    }

    $limit = max(1, min(200, $limit));

    try {
        $stmt = db()->prepare("
            SELECT
                e.id,
                e.module_key,
                e.entity_key,
                e.table_name,
                e.description,
                e.permission_slug,
                e.is_active,
                COUNT(f.id) AS field_count,
                e.updated_at
            FROM ai_schema_entities e
            LEFT JOIN ai_schema_fields f ON f.entity_id = e.id AND f.is_active = 1
            GROUP BY e.id
            ORDER BY e.module_key ASC, e.entity_key ASC
            LIMIT :limit_rows
        ");
        $stmt->bindValue(':limit_rows', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        error_log('ai schema entity list error: ' . $e->getMessage());
        return [];
    }
}

function kirpi_ai_schema_filter_options(): array
{
    if (!kirpi_ai_schema_registry_ready()) {
        return [
            'modules' => [],
            'entities' => [],
            'tables' => [],
            'permissions' => [],
        ];
    }

    try {
        $readDistinct = static function (string $column): array {
            $allowed = ['module_key', 'entity_key', 'table_name', 'permission_slug'];
            if (!in_array($column, $allowed, true)) {
                return [];
            }

            $stmt = db()->query("
                SELECT DISTINCT {$column} AS value
                FROM ai_schema_entities
                WHERE is_active = 1
                  AND {$column} IS NOT NULL
                  AND {$column} <> ''
                ORDER BY {$column} ASC
            ");

            return array_values(array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []));
        };

        return [
            'modules' => $readDistinct('module_key'),
            'entities' => $readDistinct('entity_key'),
            'tables' => $readDistinct('table_name'),
            'permissions' => $readDistinct('permission_slug'),
        ];
    } catch (Throwable $e) {
        error_log('ai schema filter options error: ' . $e->getMessage());

        return [
            'modules' => [],
            'entities' => [],
            'tables' => [],
            'permissions' => [],
        ];
    }
}

function kirpi_ai_latest_schema_sync(): ?array
{
    if (!kirpi_ai_audit_table_ready()) {
        return null;
    }

    try {
        $stmt = db()->prepare("
            SELECT action_key, status, details_json, created_at
            FROM ai_audit_logs
            WHERE action_key = :action_key
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([':action_key' => 'schema_sync']);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$record) {
            return null;
        }

        $details = json_decode((string) ($record['details_json'] ?? ''), true);
        $record['details'] = is_array($details) ? $details : [];

        return $record;
    } catch (Throwable $e) {
        error_log('ai latest schema sync error: ' . $e->getMessage());
        return null;
    }
}

function kirpi_ai_schema_quality_report(int $limit = 100): array
{
    $limit = max(1, min(500, $limit));

    if (!kirpi_ai_schema_registry_ready()) {
        return [
            'status' => 'error',
            'message' => 'schema_registry_not_ready',
            'warnings' => [],
            'meta' => [
                'warning_count' => 0,
                'error_count' => 0,
                'by_module' => [],
            ],
        ];
    }

    try {
        $stmt = db()->query("
            SELECT
                e.id AS entity_id,
                e.module_key,
                e.entity_key,
                e.table_name,
                e.description AS entity_description,
                e.permission_slug,
                f.field_name,
                f.field_type,
                f.description AS field_description,
                f.is_sensitive,
                f.is_filterable
            FROM ai_schema_entities e
            LEFT JOIN ai_schema_fields f ON f.entity_id = e.id AND f.is_active = 1
            WHERE e.is_active = 1
            ORDER BY e.module_key ASC, e.entity_key ASC, f.field_name ASC
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        error_log('ai schema quality error: ' . $e->getMessage());

        return [
            'status' => 'error',
            'message' => 'schema_quality_failed',
            'warnings' => [],
            'meta' => [
                'warning_count' => 0,
                'error_count' => 0,
                'by_module' => [],
            ],
        ];
    }

    $entities = [];
    foreach ($rows as $row) {
        $entityId = (int) ($row['entity_id'] ?? 0);
        if ($entityId <= 0) {
            continue;
        }

        if (!isset($entities[$entityId])) {
            $entities[$entityId] = [
                'module' => (string) ($row['module_key'] ?? ''),
                'entity' => (string) ($row['entity_key'] ?? ''),
                'table' => (string) ($row['table_name'] ?? ''),
                'description' => trim((string) ($row['entity_description'] ?? '')),
                'permission' => trim((string) ($row['permission_slug'] ?? '')),
                'fields' => [],
            ];
        }

        $fieldName = trim((string) ($row['field_name'] ?? ''));
        if ($fieldName === '') {
            continue;
        }

        $entities[$entityId]['fields'][] = [
            'name' => $fieldName,
            'type' => trim((string) ($row['field_type'] ?? '')),
            'description' => trim((string) ($row['field_description'] ?? '')),
            'is_sensitive' => (int) ($row['is_sensitive'] ?? 0) === 1,
            'is_filterable' => (int) ($row['is_filterable'] ?? 0) === 1,
        ];
    }

    $warnings = [];
    $byModule = [];
    $errorCount = 0;
    $sensitiveFieldPatterns = [
        '/(^|_)password($|_)/',
        '/(^|_)passwd($|_)/',
        '/(^|_)token_hash($|_)/',
        '/(^|_)(access|refresh|secret|private|api)_token($|_)/',
        '/(^|_)(secret|private|api|access)_key($|_)/',
        '/(^|_)secret_value($|_)/',
        '/(^|_)email$/',
        '/_email$/',
        '/(^|_)email_address$/',
        '/(^|_)ip_address($|_)/',
        '/(^|_)(file|storage|absolute)_path($|_)/',
        '/(^|_)(payload|details|data)_json($|_)/',
        '/(^|_)(request|response|html)?_?body($|_)/',
        '/(^|_)user_agent($|_)/',
        '/(^|_)(password|token|secret)_hash($|_)/',
    ];

    $addWarning = static function (
        string $severity,
        string $code,
        array $entity,
        ?array $field,
        string $message
    ) use (&$warnings, &$byModule, &$errorCount): void {
        $module = (string) ($entity['module'] ?? '');
        $warnings[] = [
            'severity' => $severity,
            'code' => $code,
            'module' => $module,
            'entity' => (string) ($entity['entity'] ?? ''),
            'table' => (string) ($entity['table'] ?? ''),
            'field' => $field !== null ? (string) ($field['name'] ?? '') : null,
            'message' => $message,
        ];

        if (!isset($byModule[$module])) {
            $byModule[$module] = [
                'warning_count' => 0,
                'error_count' => 0,
            ];
        }

        $byModule[$module]['warning_count']++;
        if ($severity === 'error') {
            $byModule[$module]['error_count']++;
            $errorCount++;
        }
    };

    foreach ($entities as $entity) {
        if ((string) ($entity['description'] ?? '') === '') {
            $addWarning('warning', 'missing_entity_description', $entity, null, 'Entity description is missing.');
        }

        if ((string) ($entity['permission'] ?? '') === '') {
            $addWarning('error', 'missing_permission', $entity, null, 'Permission slug is missing.');
        }

        $fields = (array) ($entity['fields'] ?? []);
        if (empty($fields)) {
            $addWarning('error', 'missing_fields', $entity, null, 'Entity has no active fields.');
            continue;
        }

        foreach ($fields as $field) {
            if ((string) ($field['description'] ?? '') === '') {
                $addWarning('warning', 'missing_field_description', $entity, $field, 'Field description is missing.');
            }

            if ((string) ($field['type'] ?? '') === '') {
                $addWarning('warning', 'missing_field_type', $entity, $field, 'Field type is missing.');
            }

            $fieldName = mb_strtolower((string) ($field['name'] ?? ''));
            if (empty($field['is_sensitive'])) {
                foreach ($sensitiveFieldPatterns as $pattern) {
                    if (preg_match($pattern, $fieldName) === 1) {
                        $addWarning('warning', 'possible_sensitive_field', $entity, $field, 'Field name suggests sensitive data but is_sensitive is not set.');
                        break;
                    }
                }
            }
        }
    }

    ksort($byModule);

    return [
        'status' => 'success',
        'message' => null,
        'warnings' => array_slice($warnings, 0, $limit),
        'meta' => [
            'warning_count' => count($warnings),
            'error_count' => $errorCount,
            'by_module' => $byModule,
            'limit' => $limit,
        ],
    ];
}

function kirpi_ai_user_has_permission(?array $user, ?string $permissionSlug): bool
{
    $permissionSlug = trim((string) $permissionSlug);
    if ($permissionSlug === '') {
        return true;
    }

    if (!$user) {
        return false;
    }

    if (($user['role_name'] ?? null) === 'Super Admin') {
        return true;
    }

    return in_array($permissionSlug, array_map('strval', (array) ($user['permissions'] ?? [])), true);
}

function kirpi_ai_discover_schema(array $options = [], ?array $user = null): array
{
    if (!kirpi_ai_schema_registry_ready()) {
        return [
            'status' => 'error',
            'message' => 'schema_registry_not_ready',
            'entities' => [],
            'meta' => [
                'entity_count' => 0,
                'field_count' => 0,
                'sensitive_field_count' => 0,
            ],
        ];
    }

    $user = $user ?? current_user();
    $includeSensitive = !empty($options['include_sensitive']) && kirpi_ai_user_has_permission($user, 'ai.schema.manage');
    $filterableOnly = !empty($options['filterable_only']);
    $search = mb_strtolower(trim((string) ($options['search'] ?? '')));
    $moduleFilter = trim((string) ($options['module'] ?? ''));
    $entityFilter = trim((string) ($options['entity'] ?? ''));
    $tableFilter = trim((string) ($options['table'] ?? ''));
    $permissionFilter = trim((string) ($options['permission'] ?? ''));
    $limit = max(1, min(200, (int) ($options['limit'] ?? 50)));

    try {
        $stmt = db()->prepare("
            SELECT
                e.id AS entity_id,
                e.module_key,
                e.entity_key,
                e.table_name,
                e.description AS entity_description,
                e.permission_slug,
                e.metadata_json AS entity_metadata_json,
                f.field_name,
                f.field_type,
                f.description AS field_description,
                f.is_sensitive,
                f.is_filterable,
                f.metadata_json AS field_metadata_json
            FROM ai_schema_entities e
            LEFT JOIN ai_schema_fields f ON f.entity_id = e.id AND f.is_active = 1
            WHERE e.is_active = 1
            ORDER BY e.module_key ASC, e.entity_key ASC, f.field_name ASC
        ");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        error_log('ai schema discovery error: ' . $e->getMessage());

        return [
            'status' => 'error',
            'message' => 'schema_discovery_failed',
            'entities' => [],
            'meta' => [
                'entity_count' => 0,
                'field_count' => 0,
                'sensitive_field_count' => 0,
            ],
        ];
    }

    $entities = [];
    $sensitiveFieldCount = 0;

    foreach ($rows as $row) {
        $permissionSlug = trim((string) ($row['permission_slug'] ?? ''));
        if (!kirpi_ai_user_has_permission($user, $permissionSlug)) {
            continue;
        }

        if ($moduleFilter !== '' && (string) ($row['module_key'] ?? '') !== $moduleFilter) {
            continue;
        }

        if ($entityFilter !== '' && (string) ($row['entity_key'] ?? '') !== $entityFilter) {
            continue;
        }

        if ($tableFilter !== '' && (string) ($row['table_name'] ?? '') !== $tableFilter) {
            continue;
        }

        if ($permissionFilter !== '' && $permissionSlug !== $permissionFilter) {
            continue;
        }

        $haystack = mb_strtolower(implode(' ', [
            (string) ($row['module_key'] ?? ''),
            (string) ($row['entity_key'] ?? ''),
            (string) ($row['table_name'] ?? ''),
            (string) ($row['entity_description'] ?? ''),
            (string) ($row['entity_metadata_json'] ?? ''),
            (string) ($row['field_name'] ?? ''),
            (string) ($row['field_description'] ?? ''),
            (string) ($row['field_metadata_json'] ?? ''),
        ]));

        if ($search !== '' && !str_contains($haystack, $search)) {
            continue;
        }

        $entityId = (int) ($row['entity_id'] ?? 0);
        if ($entityId <= 0) {
            continue;
        }

        if (!isset($entities[$entityId])) {
            if (count($entities) >= $limit) {
                continue;
            }

            $entities[$entityId] = [
                'id' => $entityId,
                'module' => (string) ($row['module_key'] ?? ''),
                'entity' => (string) ($row['entity_key'] ?? ''),
                'table' => (string) ($row['table_name'] ?? ''),
                'description' => (string) ($row['entity_description'] ?? ''),
                'permission' => $permissionSlug !== '' ? $permissionSlug : null,
                'metadata' => json_decode((string) ($row['entity_metadata_json'] ?? ''), true) ?: [],
                'fields' => [],
            ];
        }

        $fieldName = trim((string) ($row['field_name'] ?? ''));
        if ($fieldName === '') {
            continue;
        }

        $isSensitive = (int) ($row['is_sensitive'] ?? 0) === 1;
        $isFilterable = (int) ($row['is_filterable'] ?? 0) === 1;

        if ($isSensitive) {
            $sensitiveFieldCount++;
        }

        if ($isSensitive && !$includeSensitive) {
            continue;
        }

        if ($filterableOnly && !$isFilterable) {
            continue;
        }

        $entities[$entityId]['fields'][] = [
            'name' => $fieldName,
            'type' => (string) ($row['field_type'] ?? ''),
            'description' => (string) ($row['field_description'] ?? ''),
            'is_sensitive' => $isSensitive,
            'is_filterable' => $isFilterable,
            'metadata' => json_decode((string) ($row['field_metadata_json'] ?? ''), true) ?: [],
        ];
    }

    $entityList = array_values($entities);
    $fieldCount = 0;
    foreach ($entityList as $entity) {
        $fieldCount += count((array) ($entity['fields'] ?? []));
    }

    kirpi_ai_log_operation('schema_discovery', 'success', [
        'entity_count' => count($entityList),
        'field_count' => $fieldCount,
        'include_sensitive' => $includeSensitive,
        'filterable_only' => $filterableOnly,
        'search' => $search,
    ], null, 'schema_registry', null);

    return [
        'status' => 'success',
        'message' => null,
        'entities' => $entityList,
        'meta' => [
            'entity_count' => count($entityList),
            'field_count' => $fieldCount,
            'sensitive_field_count' => $sensitiveFieldCount,
            'include_sensitive' => $includeSensitive,
            'filterable_only' => $filterableOnly,
            'search' => $search,
            'module' => $moduleFilter,
            'entity' => $entityFilter,
            'table' => $tableFilter,
            'permission' => $permissionFilter,
        ],
    ];
}

function kirpi_ai_tokenize_search(string $query): array
{
    $query = mb_strtolower(trim($query));
    if ($query === '') {
        return [];
    }

    $parts = preg_split('/[^\p{L}\p{N}_]+/u', $query) ?: [];
    $tokens = [];

    foreach ($parts as $part) {
        $part = trim((string) $part);
        if (mb_strlen($part) < 2) {
            continue;
        }

        $tokens[] = $part;
    }

    return array_values(array_unique($tokens));
}

function kirpi_ai_text_score(string $text, array $tokens, int $exactWeight = 6, int $containsWeight = 2): int
{
    $text = mb_strtolower($text);
    if ($text === '' || empty($tokens)) {
        return 0;
    }

    $score = 0;
    foreach ($tokens as $token) {
        if ($text === $token) {
            $score += $exactWeight;
            continue;
        }

        if (str_contains($text, $token)) {
            $score += $containsWeight;
        }
    }

    return $score;
}

function kirpi_ai_search_schema_index(array $tokens, int $limit, ?array $user = null): ?array
{
    if (!kirpi_ai_schema_index_ready() || kirpi_ai_schema_index_count() <= 0) {
        return null;
    }

    $user = $user ?? current_user();

    try {
        $stmt = db()->query("
            SELECT
                i.entity_id,
                i.field_id,
                i.module_key,
                i.entity_key,
                i.table_name,
                i.field_name,
                i.token,
                i.source_type,
                i.source_text,
                i.weight,
                e.description AS entity_description,
                e.permission_slug,
                f.field_type,
                f.description AS field_description
            FROM ai_schema_index i
            INNER JOIN ai_schema_entities e ON e.id = i.entity_id AND e.is_active = 1
            LEFT JOIN ai_schema_fields f ON f.id = i.field_id AND f.is_active = 1
            ORDER BY i.module_key ASC, i.entity_key ASC, i.field_name ASC, i.weight DESC
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        error_log('ai schema index search error: ' . $e->getMessage());
        return null;
    }

    $results = [];
    foreach ($rows as $row) {
        $permissionSlug = trim((string) ($row['permission_slug'] ?? ''));
        if (!kirpi_ai_user_has_permission($user, $permissionSlug)) {
            continue;
        }

        $indexToken = mb_strtolower(trim((string) ($row['token'] ?? '')));
        if ($indexToken === '') {
            continue;
        }

        $matchedTokens = [];
        $score = 0;
        foreach ($tokens as $queryToken) {
            $queryToken = mb_strtolower((string) $queryToken);
            if ($queryToken === '') {
                continue;
            }

            if ($indexToken === $queryToken) {
                $score += (int) ($row['weight'] ?? 1) * 3;
                $matchedTokens[] = $queryToken;
                continue;
            }

            if (str_contains($indexToken, $queryToken) || str_contains($queryToken, $indexToken)) {
                $score += (int) ($row['weight'] ?? 1);
                $matchedTokens[] = $queryToken;
            }
        }

        if ($score <= 0) {
            continue;
        }

        $entityId = (int) ($row['entity_id'] ?? 0);
        if ($entityId <= 0) {
            continue;
        }

        if (!isset($results[$entityId])) {
            $results[$entityId] = [
                'score' => 0,
                'module' => (string) ($row['module_key'] ?? ''),
                'entity' => (string) ($row['entity_key'] ?? ''),
                'table' => (string) ($row['table_name'] ?? ''),
                'description' => (string) ($row['entity_description'] ?? ''),
                'permission' => $permissionSlug !== '' ? $permissionSlug : null,
                'matched_fields' => [],
                'matched_terms' => [],
                'matched_sources' => [],
            ];
        }

        $results[$entityId]['score'] += $score;
        foreach ($matchedTokens as $matchedToken) {
            $results[$entityId]['matched_terms'][$matchedToken] = true;
        }

        $sourceType = (string) ($row['source_type'] ?? '');
        $sourceText = (string) ($row['source_text'] ?? '');
        if ($sourceType !== '') {
            $results[$entityId]['matched_sources'][] = [
                'type' => $sourceType,
                'text' => $sourceText,
                'token' => $indexToken,
                'score' => $score,
            ];
        }

        $fieldName = trim((string) ($row['field_name'] ?? ''));
        if ($fieldName !== '') {
            if (!isset($results[$entityId]['matched_fields'][$fieldName])) {
                $results[$entityId]['matched_fields'][$fieldName] = [
                    'name' => $fieldName,
                    'type' => (string) ($row['field_type'] ?? ''),
                    'description' => (string) ($row['field_description'] ?? ''),
                    'score' => 0,
                    'matched_terms' => [],
                ];
            }

            $results[$entityId]['matched_fields'][$fieldName]['score'] += $score;
            foreach ($matchedTokens as $matchedToken) {
                $results[$entityId]['matched_fields'][$fieldName]['matched_terms'][$matchedToken] = true;
            }
        }
    }

    $resultList = array_values($results);
    foreach ($resultList as &$result) {
        $fields = array_values((array) ($result['matched_fields'] ?? []));
        foreach ($fields as &$field) {
            $field['matched_terms'] = array_keys((array) ($field['matched_terms'] ?? []));
        }
        unset($field);

        usort($fields, static function (array $a, array $b): int {
            return ((int) ($b['score'] ?? 0)) <=> ((int) ($a['score'] ?? 0));
        });

        $sources = (array) ($result['matched_sources'] ?? []);
        usort($sources, static function (array $a, array $b): int {
            return ((int) ($b['score'] ?? 0)) <=> ((int) ($a['score'] ?? 0));
        });

        $result['matched_fields'] = $fields;
        $result['matched_terms'] = array_keys((array) ($result['matched_terms'] ?? []));
        $result['matched_sources'] = array_slice($sources, 0, 5);
    }
    unset($result);

    usort($resultList, static function (array $a, array $b): int {
        $scoreCompare = ((int) ($b['score'] ?? 0)) <=> ((int) ($a['score'] ?? 0));
        if ($scoreCompare !== 0) {
            return $scoreCompare;
        }

        return strcmp((string) ($a['entity'] ?? ''), (string) ($b['entity'] ?? ''));
    });

    return array_slice($resultList, 0, $limit);
}

function kirpi_ai_search_schema(string $query, array $options = [], ?array $user = null): array
{
    $tokens = kirpi_ai_tokenize_search($query);
    $limit = max(1, min(50, (int) ($options['limit'] ?? 10)));

    if (empty($tokens)) {
        return [
            'status' => 'success',
            'query' => trim($query),
            'tokens' => [],
            'results' => [],
            'meta' => [
                'result_count' => 0,
            ],
        ];
    }

    $indexedResults = kirpi_ai_search_schema_index($tokens, $limit, $user);
    if (is_array($indexedResults)) {
        kirpi_ai_log_operation('schema_search', 'success', [
            'query' => trim($query),
            'tokens' => $tokens,
            'result_count' => count($indexedResults),
            'mode' => 'metadata_index',
        ], null, 'schema_registry', null);

        return [
            'status' => 'success',
            'query' => trim($query),
            'tokens' => $tokens,
            'results' => $indexedResults,
            'meta' => [
                'result_count' => count($indexedResults),
                'mode' => 'metadata_index',
                'index_count' => kirpi_ai_schema_index_count(),
            ],
        ];
    }

    $discovery = kirpi_ai_discover_schema([
        'include_sensitive' => false,
        'filterable_only' => !empty($options['filterable_only']),
        'limit' => 200,
    ], $user);

    if (($discovery['status'] ?? '') !== 'success') {
        return [
            'status' => 'error',
            'query' => trim($query),
            'tokens' => $tokens,
            'results' => [],
            'meta' => [
                'result_count' => 0,
            ],
        ];
    }

    $results = [];
    foreach ((array) ($discovery['entities'] ?? []) as $entity) {
        $entityScore = 0;
        $entityScore += kirpi_ai_text_score((string) ($entity['module'] ?? ''), $tokens, 8, 3);
        $entityScore += kirpi_ai_text_score((string) ($entity['entity'] ?? ''), $tokens, 10, 4);
        $entityScore += kirpi_ai_text_score((string) ($entity['table'] ?? ''), $tokens, 8, 3);
        $entityScore += kirpi_ai_text_score((string) ($entity['description'] ?? ''), $tokens, 4, 1);
        $entityScore += kirpi_ai_text_score(json_encode($entity['metadata'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '', $tokens, 5, 2);

        $matchedFields = [];
        foreach ((array) ($entity['fields'] ?? []) as $field) {
            $fieldScore = 0;
            $fieldScore += kirpi_ai_text_score((string) ($field['name'] ?? ''), $tokens, 8, 3);
            $fieldScore += kirpi_ai_text_score((string) ($field['type'] ?? ''), $tokens, 3, 1);
            $fieldScore += kirpi_ai_text_score((string) ($field['description'] ?? ''), $tokens, 4, 1);
            $fieldScore += kirpi_ai_text_score(json_encode($field['metadata'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '', $tokens, 5, 2);

            if ($fieldScore <= 0) {
                continue;
            }

            $matchedFields[] = [
                'name' => (string) ($field['name'] ?? ''),
                'type' => (string) ($field['type'] ?? ''),
                'description' => (string) ($field['description'] ?? ''),
                'score' => $fieldScore,
            ];
        }

        $score = $entityScore;
        foreach ($matchedFields as $field) {
            $score += (int) ($field['score'] ?? 0);
        }

        if ($score <= 0) {
            continue;
        }

        usort($matchedFields, static function (array $a, array $b): int {
            return ((int) ($b['score'] ?? 0)) <=> ((int) ($a['score'] ?? 0));
        });

        $results[] = [
            'score' => $score,
            'module' => (string) ($entity['module'] ?? ''),
            'entity' => (string) ($entity['entity'] ?? ''),
            'table' => (string) ($entity['table'] ?? ''),
            'description' => (string) ($entity['description'] ?? ''),
            'permission' => $entity['permission'] ?? null,
            'matched_fields' => $matchedFields,
        ];
    }

    usort($results, static function (array $a, array $b): int {
        $scoreCompare = ((int) ($b['score'] ?? 0)) <=> ((int) ($a['score'] ?? 0));
        if ($scoreCompare !== 0) {
            return $scoreCompare;
        }

        return strcmp((string) ($a['entity'] ?? ''), (string) ($b['entity'] ?? ''));
    });

    $results = array_slice($results, 0, $limit);

    kirpi_ai_log_operation('schema_search', 'success', [
        'query' => trim($query),
        'tokens' => $tokens,
        'result_count' => count($results),
        'mode' => 'discovery_fallback',
    ], null, 'schema_registry', null);

    return [
        'status' => 'success',
        'query' => trim($query),
        'tokens' => $tokens,
        'results' => $results,
        'meta' => [
            'result_count' => count($results),
            'mode' => 'discovery_fallback',
        ],
    ];
}

function kirpi_ai_build_query_plan(string $question, array $options = [], ?array $user = null): array
{
    $question = trim($question);
    $limit = max(1, min(20, (int) ($options['limit'] ?? 5)));
    $tokens = kirpi_ai_tokenize_search($question);
    $safetyNotes = [
        'Bu aşama SQL üretmez ve veri okumaz.',
        'Adaylar RBAC ve hassas alan kurallarıyla sınırlıdır.',
        'SQL aşamasına geçmeden önce Read-only SQL Guard zorunludur.',
    ];

    if ($question === '' || empty($tokens)) {
        return [
            'status' => 'success',
            'question' => $question,
            'tokens' => [],
            'search_mode' => null,
            'index_count' => kirpi_ai_schema_index_count(),
            'candidate_count' => 0,
            'primary_candidate' => null,
            'candidates' => [],
            'safety_notes' => $safetyNotes,
            'meta' => [
                'message' => 'empty_question',
            ],
        ];
    }

    $search = kirpi_ai_search_schema($question, ['limit' => $limit], $user);
    if (($search['status'] ?? '') !== 'success') {
        kirpi_ai_log_operation('query_plan_preview', 'failed', [
            'question' => $question,
            'tokens' => $tokens,
            'reason' => 'schema_search_failed',
        ], null, 'query_plan', null);

        return [
            'status' => 'error',
            'question' => $question,
            'tokens' => $tokens,
            'search_mode' => null,
            'index_count' => kirpi_ai_schema_index_count(),
            'candidate_count' => 0,
            'primary_candidate' => null,
            'candidates' => [],
            'safety_notes' => $safetyNotes,
            'meta' => [
                'message' => 'schema_search_failed',
            ],
        ];
    }

    $candidates = [];
    foreach ((array) ($search['results'] ?? []) as $index => $result) {
        $matchedFields = array_values((array) ($result['matched_fields'] ?? []));
        $fieldDetails = [];
        $recommendedFields = [];

        foreach ($matchedFields as $field) {
            $name = trim((string) ($field['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $recommendedFields[] = $name;
            $fieldDetails[] = [
                'name' => $name,
                'type' => (string) ($field['type'] ?? ''),
                'description' => (string) ($field['description'] ?? ''),
                'score' => (int) ($field['score'] ?? 0),
                'matched_terms' => array_values((array) ($field['matched_terms'] ?? [])),
            ];

            if (count($recommendedFields) >= 8) {
                break;
            }
        }

        $notes = [];
        if (empty($recommendedFields)) {
            $notes[] = 'Entity metadata ile eşleşti; field seçimi için kullanıcı onayı gerekir.';
        }

        $candidates[] = [
            'rank' => $index + 1,
            'score' => (int) ($result['score'] ?? 0),
            'module' => (string) ($result['module'] ?? ''),
            'entity' => (string) ($result['entity'] ?? ''),
            'table' => (string) ($result['table'] ?? ''),
            'description' => (string) ($result['description'] ?? ''),
            'permission' => $result['permission'] ?? null,
            'matched_terms' => array_values((array) ($result['matched_terms'] ?? [])),
            'matched_sources' => array_values((array) ($result['matched_sources'] ?? [])),
            'recommended_fields' => array_values(array_unique($recommendedFields)),
            'field_details' => $fieldDetails,
            'notes' => $notes,
        ];
    }

    $plan = [
        'status' => 'success',
        'question' => $question,
        'tokens' => $tokens,
        'search_mode' => $search['meta']['mode'] ?? null,
        'index_count' => (int) ($search['meta']['index_count'] ?? kirpi_ai_schema_index_count()),
        'candidate_count' => count($candidates),
        'primary_candidate' => $candidates[0] ?? null,
        'candidates' => $candidates,
        'safety_notes' => $safetyNotes,
        'meta' => [
            'result_count' => (int) ($search['meta']['result_count'] ?? count($candidates)),
            'sql_generated' => false,
            'data_read' => false,
        ],
    ];

    kirpi_ai_log_operation('query_plan_preview', 'success', [
        'question' => $question,
        'tokens' => $tokens,
        'candidate_count' => count($candidates),
        'primary_entity' => $candidates[0]['entity'] ?? null,
        'search_mode' => $plan['search_mode'],
        'sql_generated' => false,
    ], null, 'query_plan', null);

    return $plan;
}

function kirpi_ai_log_operation(
    string $action,
    string $status,
    array $details = [],
    ?string $modelAdapter = null,
    ?string $entityType = null,
    ?int $entityId = null
): void {
    $status = in_array($status, ['success', 'failed', 'blocked'], true) ? $status : 'success';
    $user = current_user();
    $userId = (int) ($user['id'] ?? 0);

    $detailsJson = null;
    if (!empty($details)) {
        $encoded = json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $detailsJson = $encoded === false ? null : $encoded;
    }

    if (kirpi_ai_audit_table_ready()) {
        try {
            $stmt = db()->prepare("
                INSERT INTO ai_audit_logs (
                    user_id,
                    action_key,
                    status,
                    model_adapter,
                    entity_type,
                    entity_id,
                    route_path,
                    ip_address,
                    details_json
                ) VALUES (
                    :user_id,
                    :action_key,
                    :status,
                    :model_adapter,
                    :entity_type,
                    :entity_id,
                    :route_path,
                    :ip_address,
                    :details_json
                )
            ");

            $stmt->execute([
                ':user_id' => $userId > 0 ? $userId : null,
                ':action_key' => mb_substr(trim($action), 0, 120),
                ':status' => $status,
                ':model_adapter' => $modelAdapter !== null ? mb_substr(trim($modelAdapter), 0, 120) : null,
                ':entity_type' => $entityType !== null ? mb_substr(trim($entityType), 0, 80) : null,
                ':entity_id' => $entityId,
                ':route_path' => mb_substr((string) ($GLOBALS['current_route_path'] ?? ''), 0, 190),
                ':ip_address' => function_exists('kirpi_request_ip') ? kirpi_request_ip() : null,
                ':details_json' => $detailsJson,
            ]);
        } catch (Throwable $e) {
            error_log('ai audit log insert error: ' . $e->getMessage());
        }
    }

    if (function_exists('kirpi_audit_log')) {
        kirpi_audit_log($action, 'ai', $details, $entityType, $entityId, $status);
    }
}

function kirpi_ai_sql_guard_readonly(string $sql): array
{
    $normalized = trim($sql);
    $withoutComments = preg_replace('/(--[^\r\n]*|\/\*.*?\*\/)/s', ' ', $normalized);
    $canonical = strtolower(trim((string) $withoutComments));

    if ($canonical === '') {
        return [
            'allowed' => false,
            'reason' => 'empty_sql',
        ];
    }

    if (!str_starts_with($canonical, 'select')) {
        return [
            'allowed' => false,
            'reason' => 'only_select_allowed',
        ];
    }

    if (preg_match('/\b(delete|update|insert|drop|alter|truncate|create|replace|grant|revoke)\b/i', $canonical) === 1) {
        return [
            'allowed' => false,
            'reason' => 'dangerous_keyword',
        ];
    }

    return [
        'allowed' => true,
        'reason' => null,
    ];
}
