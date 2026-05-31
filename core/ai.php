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
