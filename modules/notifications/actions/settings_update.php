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

if (!db_table_exists('notification_settings')) {
    json_response([
        'status' => 'error',
        'message' => 'Bildirim ayarları tablosu henüz kurulu değil.',
    ], 422);
}

$currentUser = current_user();
$userId = (int) ($currentUser['id'] ?? 0);
$emailEnabled = isset($_POST['email_enabled']) ? 1 : 0;
$inAppEnabled = isset($_POST['in_app_enabled']) ? 1 : 0;

if ($userId <= 0) {
    json_response([
        'status' => 'error',
        'message' => 'Geçersiz kullanıcı oturumu.',
    ], 422);
}

try {
    $stmt = db()->prepare("
        INSERT INTO notification_settings (user_id, email_enabled, in_app_enabled)
        VALUES (:user_id, :email_enabled, :in_app_enabled)
        ON DUPLICATE KEY UPDATE
            email_enabled = VALUES(email_enabled),
            in_app_enabled = VALUES(in_app_enabled)
    ");
    $stmt->execute([
        ':user_id' => $userId,
        ':email_enabled' => $emailEnabled,
        ':in_app_enabled' => $inAppEnabled,
    ]);

    json_response([
        'status' => 'success',
        'message' => 'Bildirim ayarları başarıyla güncellendi.',
        'reload_page' => true,
    ]);
} catch (Throwable $e) {
    error_log('notification settings update error: ' . $e->getMessage());

    json_response([
        'status' => 'error',
        'message' => 'Bildirim ayarları güncellenirken bir hata oluştu.',
    ], 500);
}
