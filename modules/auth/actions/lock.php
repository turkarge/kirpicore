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

$user = current_user();
$userId = (int) ($user['id'] ?? 0);
if ($userId <= 0) {
    json_response([
        'status' => 'error',
        'message' => 'Gecerli bir oturum bulunamadi.',
    ], 403);
}

if (!kirpi_auth_lock_schema_ready()) {
    json_response([
        'status' => 'error',
        'message' => 'Oturum kilitleme altyapisi hazir degil. Ayarlar > Eksikleri Kur calistirin.',
    ], 422);
}

try {
    $stmt = db()->prepare("
        SELECT lock_enabled, lock_pin_hash
        FROM users
        WHERE id = :id
        LIMIT 1
    ");
    $stmt->execute([
        ':id' => $userId,
    ]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $lockEnabled = (int) ($row['lock_enabled'] ?? 0) === 1;
    $pinHash = (string) ($row['lock_pin_hash'] ?? '');

    if (!$lockEnabled || $pinHash === '') {
        json_response([
            'status' => 'warning',
            'message' => 'Oturum kilitleme aktif degil. Profilinizden 4 haneli key tanimlayin.',
            'redirect' => base_url('profile/view'),
        ]);
    }

    kirpi_lock_session();

    kirpi_audit_log('lock', 'auth', [
        'user_id' => $userId,
    ], 'session', null, 'success');

    json_response([
        'status' => 'success',
        'message' => 'Oturum kilitlendi.',
        'redirect' => base_url('auth/lock'),
    ]);
} catch (Throwable $e) {
    error_log('auth lock action error: ' . $e->getMessage());

    json_response([
        'status' => 'error',
        'message' => 'Oturum kilitlenirken bir hata olustu.',
    ], 500);
}
