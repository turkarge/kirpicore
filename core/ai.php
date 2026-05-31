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

    kirpi_ai_log_operation('schema_sync', $status === 'success' ? 'success' : 'failed', [
        'files' => $installedFiles,
        'errors' => $errors,
        'entity_count' => $entityCount,
        'field_count' => $fieldCount,
    ], null, 'schema_registry', null);

    return [
        'status' => $status,
        'message' => null,
        'files' => $installedFiles,
        'entity_count' => $entityCount,
        'field_count' => $fieldCount,
        'errors' => $errors,
    ];
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

        $haystack = mb_strtolower(implode(' ', [
            (string) ($row['module_key'] ?? ''),
            (string) ($row['entity_key'] ?? ''),
            (string) ($row['table_name'] ?? ''),
            (string) ($row['entity_description'] ?? ''),
            (string) ($row['field_name'] ?? ''),
            (string) ($row['field_description'] ?? ''),
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
        ],
    ];
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
