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

$currentUser = current_user();
$userId = (int) ($currentUser['id'] ?? 0);
$lockEnabled = isset($_POST['lock_enabled']) ? 1 : 0;
$lockPin = trim((string) ($_POST['lock_pin'] ?? ''));
$lockPinConfirm = trim((string) ($_POST['lock_pin_confirm'] ?? ''));

if ($userId <= 0) {
    json_response([
        'status' => 'error',
        'message' => 'Gecerli kullanici oturumu bulunamadi.',
    ], 403);
}

if (!kirpi_auth_lock_schema_ready()) {
    json_response([
        'status' => 'error',
        'message' => 'Oturum kilitleme altyapisi hazir degil. Ayarlar > Eksikleri Kur calistirin.',
    ], 422);
}

try {
    $currentStmt = db()->prepare("
        SELECT lock_pin_hash
        FROM users
        WHERE id = :id
        LIMIT 1
    ");
    $currentStmt->execute([
        ':id' => $userId,
    ]);
    $currentRow = $currentStmt->fetch(PDO::FETCH_ASSOC) ?: [];
    $currentPinHash = (string) ($currentRow['lock_pin_hash'] ?? '');

    $updatePin = ($lockPin !== '' || $lockPinConfirm !== '');
    if ($updatePin) {
        if (!preg_match('/^\d{4,6}$/', $lockPin)) {
            json_response([
                'status' => 'error',
                'message' => 'Key sadece rakam olmali ve 4-6 hane araliginda olmalidir.',
            ], 422);
        }

        if ($lockPin !== $lockPinConfirm) {
            json_response([
                'status' => 'error',
                'message' => 'Key tekrar alani uyusmuyor.',
            ], 422);
        }
    }

    if ($lockEnabled === 1 && !$updatePin && $currentPinHash === '') {
        json_response([
            'status' => 'error',
            'message' => 'Oturum kilitlemeyi acmak icin once bir key tanimlamalisiniz.',
        ], 422);
    }

    $fields = ['lock_enabled = :lock_enabled'];
    $params = [
        ':id' => $userId,
        ':lock_enabled' => $lockEnabled,
    ];

    if ($updatePin) {
        $fields[] = 'lock_pin_hash = :lock_pin_hash';
        $params[':lock_pin_hash'] = password_hash($lockPin, PASSWORD_DEFAULT);
    }

    $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    $_SESSION['user']['lock_enabled'] = $lockEnabled === 1;
    if ($lockEnabled !== 1 && kirpi_session_lock_state()) {
        kirpi_unlock_session();
    }

    kirpi_audit_log('lock_settings_update', 'profile', [
        'target_user_id' => $userId,
        'lock_enabled' => $lockEnabled,
        'pin_updated' => $updatePin,
    ], 'user', $userId, 'success');

    json_response([
        'status' => 'success',
        'message' => 'Oturum kilitleme ayarlari guncellendi.',
        'reload_page' => true,
    ]);
} catch (Throwable $e) {
    error_log('profile lock settings update error: ' . $e->getMessage());

    json_response([
        'status' => 'error',
        'message' => 'Ayarlar guncellenirken bir hata olustu.',
    ], 500);
}
