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

if ($userId <= 0) {
    json_response([
        'status' => 'error',
        'message' => 'Geçersiz kullanıcı oturumu.',
    ], 422);
}

try {
    $stmt = db()->prepare("
        UPDATE notifications
        SET read_at = NOW()
        WHERE user_id = :user_id
          AND read_at IS NULL
    ");
    $stmt->execute([
        ':user_id' => $userId,
    ]);

    json_response([
        'status' => 'success',
        'message' => 'Tüm bildirimler okundu olarak işaretlendi.',
    ]);
} catch (Throwable $e) {
    error_log('notifications mark all read error: ' . $e->getMessage());

    json_response([
        'status' => 'error',
        'message' => 'Bildirimler güncellenirken bir hata oluştu.',
    ], 500);
}
