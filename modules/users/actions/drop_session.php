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

$targetUserId = (int) ($_POST['id'] ?? 0);
$currentUser = current_user();
$actorUserId = (int) ($currentUser['id'] ?? 0);

if ($targetUserId <= 0) {
    json_response([
        'status' => 'error',
        'message' => 'Gecersiz kullanici.',
    ], 422);
}

if (!kirpi_auth_lock_schema_ready()) {
    json_response([
        'status' => 'error',
        'message' => 'Session altyapisi hazir degil. Ayarlar > Eksikleri Kur calistirin.',
    ], 422);
}

try {
    $userStmt = db()->prepare("
        SELECT id, name, email
        FROM users
        WHERE id = :id
        LIMIT 1
    ");
    $userStmt->execute([
        ':id' => $targetUserId,
    ]);
    $targetUser = $userStmt->fetch(PDO::FETCH_ASSOC);

    if (!$targetUser) {
        json_response([
            'status' => 'error',
            'message' => 'Kullanici bulunamadi.',
        ], 404);
    }

    $updateStmt = db()->prepare("
        UPDATE users
        SET session_version = session_version + 1
        WHERE id = :id
        LIMIT 1
    ");
    $updateStmt->execute([
        ':id' => $targetUserId,
    ]);

    if (kirpi_user_sessions_table_ready()) {
        $sessionDeleteStmt = db()->prepare("DELETE FROM user_sessions WHERE user_id = :user_id");
        $sessionDeleteStmt->execute([
            ':user_id' => $targetUserId,
        ]);
    }

    kirpi_create_notification(
        $targetUserId,
        'Oturum sonlandirildi',
        'Yetkili bir kullanici tum aktif oturumlarinizi sonlandirdi. Lutfen yeniden giris yapin.'
    );

    if (!empty($targetUser['email'])) {
        kirpi_send_templated_mail(
            (string) $targetUser['email'],
            'users.session_dropped',
            [
                'user_name' => (string) ($targetUser['name'] ?? ''),
            ],
            $targetUserId > 0 ? $targetUserId : null
        );
    }

    kirpi_audit_log('drop_session', 'users', [
        'target_user_id' => $targetUserId,
        'target_email' => (string) ($targetUser['email'] ?? ''),
    ], 'session', null, 'success');

    if ($actorUserId > 0 && $actorUserId === $targetUserId) {
        kirpi_delete_current_user_session();
        unset($_SESSION['user'], $_SESSION['_auth_lock']);

        json_response([
            'status' => 'success',
            'message' => 'Kendi oturumunuz sonlandirildi. Yeniden giris yapin.',
            'redirect' => base_url('auth/login'),
        ]);
    }

    json_response([
        'status' => 'success',
        'message' => 'Kullanicinin aktif oturumlari sonlandirildi.',
        'reload_page' => true,
    ]);
} catch (Throwable $e) {
    error_log('drop session action error: ' . $e->getMessage());

    json_response([
        'status' => 'error',
        'message' => 'Oturum sonlandirilirken bir hata olustu.',
    ], 500);
}
