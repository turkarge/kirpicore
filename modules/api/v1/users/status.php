<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

require_action('POST', false);

$actor = api_require_token('users.status');
$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    api_error(422, 'Gecersiz kullanici id.');
}

$input = api_json_input();
$isActiveRaw = $input['is_active'] ?? null;

if ($isActiveRaw === null) {
    api_error(422, 'is_active zorunludur.');
}

$isActive = filter_var($isActiveRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
if ($isActive === null) {
    $isActive = ((int) $isActiveRaw === 1);
}

try {
    $stmt = db()->prepare("\n        SELECT\n            u.id,\n            u.is_active,\n            r.name AS role_name\n        FROM users u\n        LEFT JOIN roles r ON r.id = u.role_id\n        WHERE u.id = :id\n        LIMIT 1\n    ");
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        api_error(404, 'Kullanici bulunamadi.');
    }

    $isSuperAdminUser = ((string) ($user['role_name'] ?? '')) === 'Super Admin';
    if ($isSuperAdminUser && !$isActive) {
        api_error(422, 'Super Admin kullanici pasife alinamaz.');
    }

    $updateStmt = db()->prepare("UPDATE users SET is_active = :is_active WHERE id = :id");
    $updateStmt->execute([
        ':is_active' => $isActive ? 1 : 0,
        ':id' => $id,
    ]);

    kirpi_audit_log('api_status', 'users', [
        'actor_user_id' => (int) ($actor['id'] ?? 0),
        'target_user_id' => $id,
        'is_active' => $isActive,
    ], 'user', $id, 'success');

    api_response(200, 'Kullanici durumu guncellendi.', [
        'user' => [
            'id' => $id,
            'is_active' => $isActive,
        ],
    ]);
} catch (Throwable $e) {
    error_log('api users status error: ' . $e->getMessage());
    api_error(500, 'Kullanici durumu guncellenemedi.');
}

