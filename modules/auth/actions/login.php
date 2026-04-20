<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

require_action('POST', false);

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    json_response([
        'status' => 'error',
        'message' => 'GÃ¼venlik doÄŸrulamasÄ± baÅŸarÄ±sÄ±z oldu. SayfayÄ± yenileyip tekrar deneyin.',
    ], 419);
}

$email = trim((string)($_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    json_response([
        'status' => 'error',
        'message' => 'E-posta ve ÅŸifre alanlarÄ± zorunludur.',
    ], 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response([
        'status' => 'error',
        'message' => 'GeÃ§erli bir e-posta adresi girin.',
    ], 422);
}

try {
    $hasLockSchema = kirpi_auth_lock_schema_ready();
    $lockSelectSql = $hasLockSchema
        ? "u.lock_enabled, u.session_version,"
        : "0 AS lock_enabled, 0 AS session_version,";

    $stmt = db()->prepare("
    SELECT 
        u.id,
        u.name,
        u.email,
        u.password,
        u.role_id,
        {$lockSelectSql}
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
        kirpi_audit_log('login_failed', 'auth', [
            'email' => $email,
            'reason' => 'invalid_credentials',
        ], 'session', null, 'failed');

        json_response([
            'status' => 'error',
            'message' => 'E-posta veya ÅŸifre hatalÄ±.',
        ], 401);
    }

    if (($user['role_id'] ?? null) && isset($user['role_is_active']) && (int) $user['role_is_active'] !== 1) {
        kirpi_audit_log('login_failed', 'auth', [
            'email' => $email,
            'reason' => 'role_inactive',
            'role_id' => (int) ($user['role_id'] ?? 0),
        ], 'session', null, 'failed');

        json_response([
            'status' => 'error',
            'message' => 'Bu kullanÄ±cÄ±ya baÄŸlÄ± rol pasif durumda.',
        ], 403);
    }

    unset($user['password']);
    unset($user['role_is_active']);

    $user['permissions'] = load_user_permissions(
        isset($user['role_id']) ? (int) $user['role_id'] : null,
        $user['role_name'] ?? null
    );
    $user['lock_enabled'] = $hasLockSchema && (int) ($user['lock_enabled'] ?? 0) === 1;
    $user['session_version'] = $hasLockSchema ? (int) ($user['session_version'] ?? 0) : 0;

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }

    $_SESSION['user'] = $user;
    unset($_SESSION['_auth_lock']);
    kirpi_register_user_session((int) ($user['id'] ?? 0));
    unset($_SESSION['flash_message']);

    $defaultRedirect = base_url(APP_DEFAULT_ROUTE);
    $redirect = $_SESSION['redirect_to'] ?? $defaultRedirect;
    unset($_SESSION['redirect_to']);

    $redirect = is_string($redirect) ? trim($redirect) : '';
    if ($redirect === '') {
        $redirect = $defaultRedirect;
    }

    $baseParts = parse_url(BASE_URL);
    $redirectParts = parse_url($redirect);

    if ($redirectParts === false) {
        $redirect = $defaultRedirect;
    } elseif (!isset($redirectParts['scheme']) && !isset($redirectParts['host'])) {
        $redirect = base_url(ltrim($redirect, '/'));
    } else {
        $baseHost = strtolower((string) ($baseParts['host'] ?? ''));
        $baseScheme = strtolower((string) ($baseParts['scheme'] ?? 'http'));
        $basePort = (int) ($baseParts['port'] ?? ($baseScheme === 'https' ? 443 : 80));

        $redirectHost = strtolower((string) ($redirectParts['host'] ?? ''));
        $redirectScheme = strtolower((string) ($redirectParts['scheme'] ?? $baseScheme));
        $redirectPort = (int) ($redirectParts['port'] ?? ($redirectScheme === 'https' ? 443 : 80));

        if (
            $redirectHost !== $baseHost ||
            $redirectScheme !== $baseScheme ||
            $redirectPort !== $basePort
        ) {
            $redirect = $defaultRedirect;
        }
    }

    if (preg_match('#/auth/login/?$#i', $redirect) === 1) {
        $redirect = $defaultRedirect;
    }

    kirpi_audit_log('login', 'auth', [
        'email' => (string) ($user['email'] ?? ''),
        'role_id' => (int) ($user['role_id'] ?? 0),
        'role_name' => (string) ($user['role_name'] ?? ''),
    ], 'session', null, 'success');

    json_response([
        'status' => 'success',
        'message' => 'GiriÅŸ baÅŸarÄ±lÄ±. YÃ¶nlendiriliyorsunuz.',
        'redirect' => $redirect,
    ]);
} catch (Throwable $e) {
    error_log('Login action hatasÄ±: ' . $e->getMessage());

    json_response([
        'status' => 'error',
        'message' => 'GiriÅŸ iÅŸlemi sÄ±rasÄ±nda bir hata oluÅŸtu.',
    ], 500);
}
