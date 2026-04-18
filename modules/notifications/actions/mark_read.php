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

if (!db_table_exists('notifications')) {
    json_response([
        'status' => 'error',
        'message' => 'Bildirim tablosu henüz kurulu değil.',
    ], 422);
}

$currentUser = current_user();
$userId = (int) ($currentUser['id'] ?? 0);
$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0 || $userId <= 0) {
    json_response([
        'status' => 'error',
        'message' => 'Geçersiz istek.',
    ], 422);
}

try {
    $stmt = db()->prepare("
        UPDATE notifications
        SET read_at = NOW()
        WHERE id = :id
          AND user_id = :user_id
          AND read_at IS NULL
    ");
    $stmt->execute([
        ':id' => $id,
        ':user_id' => $userId,
    ]);

    json_response([
        'status' => 'success',
        'message' => 'Bildirim okundu olarak işaretlendi.',
    ]);
} catch (Throwable $e) {
    error_log('notifications mark read error: ' . $e->getMessage());

    json_response([
        'status' => 'error',
        'message' => 'Bildirim güncellenirken bir hata oluştu.',
    ], 500);
}
