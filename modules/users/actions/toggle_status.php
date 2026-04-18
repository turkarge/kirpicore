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

$id = (int)($_POST['id'] ?? 0);
$status = (int)($_POST['status'] ?? -1);

if ($id <= 0 || !in_array($status, [0, 1], true)) {
    json_response([
        'status' => 'error',
        'message' => 'Geçersiz istek.',
    ], 422);
}

try {
    $userStmt = db()->prepare("
        SELECT
            u.id,
            u.is_active,
            r.name AS role_name
        FROM users u
        LEFT JOIN roles r ON r.id = u.role_id
        WHERE u.id = :id
        LIMIT 1
    ");
    $userStmt->execute([
        ':id' => $id,
    ]);

    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        json_response([
            'status' => 'error',
            'message' => 'Kullanıcı bulunamadı.',
        ], 404);
    }

    if (($user['role_name'] ?? null) === 'Super Admin' && $status !== 1) {
        json_response([
            'status' => 'error',
            'message' => 'Super Admin kullanıcı pasife alınamaz.',
        ], 422);
    }

    $stmt = db()->prepare("UPDATE users SET is_active = :status WHERE id = :id");
    $stmt->execute([
        ':status' => $status,
        ':id' => $id,
    ]);

    json_response([
        'status' => 'success',
        'message' => $status === 1 ? 'Kullanıcı aktif yapıldı.' : 'Kullanıcı pasif yapıldı.',
    ]);
} catch (Throwable $e) {
    error_log('users toggle status hatası: ' . $e->getMessage());

    json_response([
        'status' => 'error',
        'message' => 'Durum güncellenirken bir hata oluştu.',
    ], 500);
}
