<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

require_action('POST', true);

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    if ($isAjax) {
        json_response([
            'status' => 'error',
            'message' => 'Güvenlik doğrulaması başarısız oldu.',
        ], 419);
    }

    set_flash_message('danger', 'Güvenlik doğrulaması başarısız oldu.');
    redirect(base_url('auth/login'));
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

if ($isAjax) {
    json_response([
        'status' => 'success',
        'message' => 'Oturum kapatıldı.',
        'redirect' => base_url('auth/login'),
    ]);
}

redirect(base_url('auth/login'));