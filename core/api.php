<?php

function api_is_enabled(): bool
{
    if (function_exists('kirpi_settings_table_ready') && kirpi_settings_table_ready()) {
        return kirpi_setting_bool('api.enabled', env_bool('API_ENABLED', true));
    }

    return env_bool('API_ENABLED', true);
}

function api_json_input(): array
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    $raw = (string) file_get_contents('php://input');
    if (trim($raw) === '') {
        $cached = [];
        return $cached;
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        $cached = [];
        return $cached;
    }

    $cached = $decoded;
    return $cached;
}

function api_response(int $statusCode, string $message, array $data = [], array $meta = []): never
{
    $payload = [
        'status' => $statusCode >= 200 && $statusCode < 300 ? 'success' : 'error',
        'message' => $message,
        'data' => $data,
    ];

    if (!empty($meta)) {
        $payload['meta'] = $meta;
    }

    json_response($payload, $statusCode);
}

function api_error(int $statusCode, string $message, array $meta = []): never
{
    api_response($statusCode, $message, [], $meta);
}

function api_extract_bearer_token(): ?string
{
    $header = (string) ($_SERVER['HTTP_AUTHORIZATION'] ?? '');
    if ($header === '' && function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        $header = (string) ($headers['Authorization'] ?? $headers['authorization'] ?? '');
    }

    if ($header === '') {
        return null;
    }

    if (preg_match('/^\s*Bearer\s+(.+)\s*$/i', $header, $matches) !== 1) {
        return null;
    }

    $token = trim((string) ($matches[1] ?? ''));
    return $token !== '' ? $token : null;
}

function api_token_table_ready(): bool
{
    return db_table_exists('api_tokens');
}

function api_token_hash(string $plainToken): string
{
    return hash('sha256', $plainToken);
}

function api_issue_token_for_user(int $userId, ?string $tokenName = null, ?int $ttlSeconds = null): ?array
{
    if ($userId <= 0 || !api_token_table_ready()) {
        return null;
    }

    $plain = bin2hex(random_bytes(32));
    $hash = api_token_hash($plain);
    $name = trim((string) ($tokenName ?? 'default')) ?: 'default';
    $ttl = $ttlSeconds ?? (int) env('API_TOKEN_TTL_SECONDS', '2592000');
    $isUnlimited = $ttlSeconds !== null && $ttlSeconds < 0;

    if ($isUnlimited) {
        $expiresAt = '2099-12-31 23:59:59';
    } else {
        if ($ttl <= 0) {
            $ttl = 2592000;
        }
        $expiresAt = (new DateTimeImmutable('now'))->add(new DateInterval('PT' . $ttl . 'S'))->format('Y-m-d H:i:s');
    }

    $stmt = db()->prepare("\n        INSERT INTO api_tokens (\n            user_id,\n            token_name,\n            token_hash,\n            expires_at,\n            last_used_at\n        ) VALUES (\n            :user_id,\n            :token_name,\n            :token_hash,\n            :expires_at,\n            NULL\n        )\n    ");
    $stmt->execute([
        ':user_id' => $userId,
        ':token_name' => mb_substr($name, 0, 120),
        ':token_hash' => $hash,
        ':expires_at' => $expiresAt,
    ]);

    return [
        'token_id' => (int) db()->lastInsertId(),
        'token' => $plain,
        'expires_at' => $expiresAt,
        'is_unlimited' => $isUnlimited,
    ];
}

function api_list_tokens_for_user(int $userId, int $limit = 50): array
{
    if ($userId <= 0 || !api_token_table_ready()) {
        return [];
    }

    $limit = max(1, min(200, $limit));

    try {
        $stmt = db()->prepare("\n            SELECT\n                id,\n                token_name,\n                last_used_at,\n                expires_at,\n                revoked_at,\n                created_at,\n                updated_at\n            FROM api_tokens\n            WHERE user_id = :user_id\n            ORDER BY id DESC\n            LIMIT :limit\n        ");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        error_log('api list tokens error: ' . $e->getMessage());
        return [];
    }
}

function api_revoke_token_for_user(int $tokenId, int $userId): bool
{
    if ($tokenId <= 0 || $userId <= 0 || !api_token_table_ready()) {
        return false;
    }

    try {
        $stmt = db()->prepare("\n            UPDATE api_tokens\n            SET revoked_at = NOW(),\n                updated_at = NOW()\n            WHERE id = :id\n              AND user_id = :user_id\n              AND revoked_at IS NULL\n        ");
        $stmt->execute([
            ':id' => $tokenId,
            ':user_id' => $userId,
        ]);

        return $stmt->rowCount() > 0;
    } catch (Throwable $e) {
        error_log('api revoke token error: ' . $e->getMessage());
        return false;
    }
}

function api_authenticate_by_token(string $plainToken): ?array
{
    if ($plainToken === '' || !api_token_table_ready()) {
        return null;
    }

    $hash = api_token_hash($plainToken);

    $stmt = db()->prepare("\n        SELECT\n            t.id AS token_id,\n            t.user_id,\n            t.expires_at,\n            t.revoked_at,\n            u.id,\n            u.name,\n            u.email,\n            u.avatar,\n            u.is_active,\n            u.role_id,\n            r.name AS role_name,\n            r.is_active AS role_is_active\n        FROM api_tokens t\n        INNER JOIN users u ON u.id = t.user_id\n        LEFT JOIN roles r ON r.id = u.role_id\n        WHERE t.token_hash = :token_hash\n        LIMIT 1\n    ");
    $stmt->execute([
        ':token_hash' => $hash,
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return null;
    }

    if ((string) ($row['revoked_at'] ?? '') !== '') {
        return null;
    }

    $expiresAt = (string) ($row['expires_at'] ?? '');
    if ($expiresAt !== '' && strtotime($expiresAt) !== false && strtotime($expiresAt) < time()) {
        return null;
    }

    if ((int) ($row['is_active'] ?? 0) !== 1) {
        return null;
    }

    if (($row['role_id'] ?? null) && isset($row['role_is_active']) && (int) $row['role_is_active'] !== 1) {
        return null;
    }

    $tokenUpdateStmt = db()->prepare("\n        UPDATE api_tokens\n        SET last_used_at = NOW(),\n            updated_at = NOW()\n        WHERE id = :id\n    ");
    $tokenUpdateStmt->execute([
        ':id' => (int) ($row['token_id'] ?? 0),
    ]);

    $user = [
        'id' => (int) ($row['id'] ?? 0),
        'name' => (string) ($row['name'] ?? ''),
        'email' => (string) ($row['email'] ?? ''),
        'avatar' => $row['avatar'] ?? null,
        'role_id' => isset($row['role_id']) ? (int) $row['role_id'] : null,
        'role_name' => $row['role_name'] ?? null,
        'permissions' => load_user_permissions(
            isset($row['role_id']) ? (int) $row['role_id'] : null,
            $row['role_name'] ?? null
        ),
    ];

    return [
        'user' => $user,
        'token_id' => (int) ($row['token_id'] ?? 0),
    ];
}

function api_user_has_permission(array $user, ?string $permission): bool
{
    if ($permission === null || trim($permission) === '') {
        return true;
    }

    if (($user['role_name'] ?? null) === 'Super Admin') {
        return true;
    }

    return in_array($permission, (array) ($user['permissions'] ?? []), true);
}

function api_require_token(?string $requiredPermission = null): array
{
    $token = api_extract_bearer_token();
    if ($token === null) {
        api_error(401, 'Bearer token gereklidir.');
    }

    $auth = api_authenticate_by_token($token);
    if (!$auth) {
        api_error(401, 'Gecersiz veya suresi dolmus token.');
    }

    $user = (array) ($auth['user'] ?? []);
    if (!api_user_has_permission($user, $requiredPermission)) {
        api_error(403, 'Bu endpoint icin yetkiniz yok.');
    }

    return $user;
}
