<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

require_action('POST', true);

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    json_response([
        'status' => 'error',
        'message' => 'Güvenlik doğrulaması başarısız oldu.',
    ], 419);
}

$name = trim((string) ($_POST['name'] ?? ''));
$isActive = isset($_POST['is_active']) ? 1 : 0;

if ($name === '') {
    json_response([
        'status' => 'error',
        'message' => 'Rol adı zorunludur.',
    ], 422);
}

if (mb_strlen($name) < 2) {
    json_response([
        'status' => 'error',
        'message' => 'Rol adı en az 2 karakter olmalıdır.',
    ], 422);
}

try {
    $checkStmt = db()->prepare("
        SELECT COUNT(id)
        FROM roles
        WHERE LOWER(name) = LOWER(:name)
    ");
    $checkStmt->execute([
        ':name' => $name,
    ]);

    if ((int) $checkStmt->fetchColumn() > 0) {
        json_response([
            'status' => 'error',
            'message' => 'Bu rol adı zaten kayıtlı.',
        ], 422);
    }

    $stmt = db()->prepare("
        INSERT INTO roles (name, is_active)
        VALUES (:name, :is_active)
    ");
    $stmt->execute([
        ':name' => $name,
        ':is_active' => $isActive,
    ]);

    json_response([
        'status' => 'success',
        'message' => '"' . $name . '" rolü başarıyla oluşturuldu.',
    ]);
} catch (Throwable $e) {
    error_log('roles create error: ' . $e->getMessage());

    json_response([
        'status' => 'error',
        'message' => 'Rol oluşturulurken bir hata oluştu.',
    ], 500);
}
