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

$currentUser = current_user();
$id = (int) ($currentUser['id'] ?? 0);
$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
$passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

if ($id <= 0) {
    json_response([
        'status' => 'error',
        'message' => 'Geçersiz kullanıcı oturumu.',
    ], 403);
}

if ($name === '' || $email === '') {
    json_response([
        'status' => 'error',
        'message' => 'Ad soyad ve e-posta alanları zorunludur.',
    ], 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response([
        'status' => 'error',
        'message' => 'Geçerli bir e-posta adresi girin.',
    ], 422);
}

$passwordWillChange = ($password !== '' || $passwordConfirm !== '');

if ($passwordWillChange) {
    if (mb_strlen($password) < 6) {
        json_response([
            'status' => 'error',
            'message' => 'Yeni şifre en az 6 karakter olmalıdır.',
        ], 422);
    }

    if ($password !== $passwordConfirm) {
        json_response([
            'status' => 'error',
            'message' => 'Yeni şifreler uyuşmuyor.',
        ], 422);
    }
}

$newAvatarFileName = null;
$oldAvatarFileName = null;

try {
    $userStmt = db()->prepare("
        SELECT id, avatar, role_id
        FROM users
        WHERE id = :id
        LIMIT 1
    ");
    $userStmt->execute([
        ':id' => $id,
    ]);

    $existingUser = $userStmt->fetch(PDO::FETCH_ASSOC);

    if (!$existingUser) {
        json_response([
            'status' => 'error',
            'message' => 'Kullanıcı bulunamadı.',
        ], 404);
    }

    $oldAvatarFileName = $existingUser['avatar'] ?? null;

    $checkStmt = db()->prepare("
        SELECT COUNT(id)
        FROM users
        WHERE email = :email
          AND id != :id
    ");
    $checkStmt->execute([
        ':email' => $email,
        ':id' => $id,
    ]);

    if ((int) $checkStmt->fetchColumn() > 0) {
        json_response([
            'status' => 'error',
            'message' => 'Bu e-posta adresi başka bir kullanıcı tarafından kullanılıyor.',
        ], 422);
    }

    if (!empty($_FILES['avatar']['name'] ?? '')) {
        $uploadResult = kirpi_upload_avatar($_FILES['avatar']);

        if (!$uploadResult['success']) {
            json_response([
                'status' => 'error',
                'message' => $uploadResult['message'],
            ], 422);
        }

        $newAvatarFileName = $uploadResult['file_name'];
    }

    $fields = [
        'name = :name',
        'email = :email',
    ];

    $params = [
        ':id' => $id,
        ':name' => $name,
        ':email' => $email,
    ];

    if ($passwordWillChange) {
        $fields[] = 'password = :password';
        $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
    }

    if ($newAvatarFileName !== null) {
        $fields[] = 'avatar = :avatar';
        $params[':avatar'] = $newAvatarFileName;
    }

    $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    if ($newAvatarFileName !== null && $oldAvatarFileName) {
        $oldPath = BASE_PATH . '/uploads/avatars/' . $oldAvatarFileName;
        if (is_file($oldPath)) {
            @unlink($oldPath);
        }
    }

    $roleName = $currentUser['role_name'] ?? null;
    $roleId = isset($existingUser['role_id']) ? (int) $existingUser['role_id'] : null;

    $_SESSION['user']['name'] = $name;
    $_SESSION['user']['email'] = $email;
    $_SESSION['user']['role_id'] = $roleId;
    $_SESSION['user']['role_name'] = $roleName;
    $_SESSION['user']['permissions'] = load_user_permissions($roleId, $roleName);

    if ($newAvatarFileName !== null) {
        $_SESSION['user']['avatar'] = $newAvatarFileName;
    }

    kirpi_audit_log('update', 'profile', [
        'target_user_id' => $id,
        'email' => $email,
        'password_changed' => $passwordWillChange,
        'avatar_changed' => $newAvatarFileName !== null,
    ], 'user', $id, 'success');

    json_response([
        'status' => 'success',
        'message' => 'Profil başarıyla güncellendi.',
        'reload_page' => true,
    ]);
} catch (Throwable $e) {
    error_log('profile update error: ' . $e->getMessage());

    if ($newAvatarFileName && is_file(BASE_PATH . '/uploads/avatars/' . $newAvatarFileName)) {
        @unlink(BASE_PATH . '/uploads/avatars/' . $newAvatarFileName);
    }

    json_response([
        'status' => 'error',
        'message' => 'Profil güncellenirken bir hata oluştu.',
    ], 500);
}
