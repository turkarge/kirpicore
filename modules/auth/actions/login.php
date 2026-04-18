<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

require_action('POST', false);

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    json_response([
        'status' => 'error',
        'message' => 'Güvenlik doğrulaması başarısız oldu. Sayfayı yenileyip tekrar deneyin.',
    ], 419);
}

$email = trim((string)($_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    json_response([
        'status' => 'error',
        'message' => 'E-posta ve şifre alanları zorunludur.',
    ], 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response([
        'status' => 'error',
        'message' => 'Geçerli bir e-posta adresi girin.',
    ], 422);
}

try {
    $stmt = db()->prepare("
    SELECT 
        u.id,
        u.name,
        u.email,
        u.password,
        u.role_id,
        r.name AS role_name,
        r.is_active AS role_is_active
    FROM users u
    LEFT JOIN roles r ON r.id = u.role_id
    WHERE u.email = :email
      AND u.is_active = 1
    LIMIT 1
");
    $stmt->execute([
        ':email' => $email,
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        json_response([
            'status' => 'error',
            'message' => 'E-posta veya şifre hatalı.',
        ], 401);
    }

    if (($user['role_id'] ?? null) && isset($user['role_is_active']) && (int) $user['role_is_active'] !== 1) {
        json_response([
            'status' => 'error',
            'message' => 'Bu kullanıcıya bağlı rol pasif durumda.',
        ], 403);
    }

    unset($user['password']);
    unset($user['role_is_active']);

    $user['permissions'] = load_user_permissions(
        isset($user['role_id']) ? (int) $user['role_id'] : null,
        $user['role_name'] ?? null
    );

    $_SESSION['user'] = $user;
    unset($_SESSION['flash_message']);

    $redirect = $_SESSION['redirect_to'] ?? base_url(APP_DEFAULT_ROUTE);
    unset($_SESSION['redirect_to']);

    json_response([
        'status' => 'success',
        'message' => 'Giriş başarılı. Yönlendiriliyorsunuz.',
        'redirect' => $redirect,
    ]);
} catch (Throwable $e) {
    error_log('Login action hatası: ' . $e->getMessage());

    json_response([
        'status' => 'error',
        'message' => 'Giriş işlemi sırasında bir hata oluştu.',
    ], 500);
}
