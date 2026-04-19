<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

require_action('POST', true);

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    json_response([
        'status' => 'error',
        'message' => 'Guvenlik dogrulamasi basarisiz oldu.',
    ], 419);
}

$id = (int)($_POST['id'] ?? 0);
$status = (int)($_POST['status'] ?? -1);
$currentUser = current_user();
$currentUserId = (int)($currentUser['id'] ?? 0);

if ($id <= 0 || !in_array($status, [0, 1], true)) {
    json_response([
        'status' => 'error',
        'message' => 'Gecersiz istek.',
    ], 422);
}

if ($id === $currentUserId && $status !== 1) {
    json_response([
        'status' => 'error',
        'message' => 'Kendi hesabinizi pasife alamazsiniz.',
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
            'message' => 'Kullanici bulunamadi.',
        ], 404);
    }

    if (($user['role_name'] ?? null) === 'Super Admin' && $status !== 1) {
        json_response([
            'status' => 'error',
            'message' => 'Super Admin kullanici pasife alinamaz.',
        ], 422);
    }

    $stmt = db()->prepare("UPDATE users SET is_active = :status WHERE id = :id");
    $stmt->execute([
        ':status' => $status,
        ':id' => $id,
    ]);

    json_response([
        'status' => 'success',
        'message' => $status === 1 ? 'Kullanici aktif yapildi.' : 'Kullanici pasif yapildi.',
    ]);
} catch (Throwable $e) {
    error_log('users toggle status hatasi: ' . $e->getMessage());

    json_response([
        'status' => 'error',
        'message' => 'Durum guncellenirken bir hata olustu.',
    ], 500);
}
