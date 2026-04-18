<?php

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');

define('BASE_PATH', __DIR__);
define('KIRPI_CORE_ENTRY', true);

require_once BASE_PATH . '/core/config.php';
require_once BASE_PATH . '/core/database.php';
require_once BASE_PATH . '/core/functions.php';
require_once BASE_PATH . '/core/routes.php';

$request_path = trim($_GET['url'] ?? '', '/');
$request_path = $request_path !== '' ? $request_path : APP_DEFAULT_ROUTE;

$segments = explode('/', $request_path);
$module = $segments[0] ?? 'dashboard';

$route_info = $routes[$request_path] ?? null;

global $current_module;
$current_module = $module;

if (!$route_info) {
    display_error_page(
        '404 - Sayfa Bulunamadı',
        'Aradığınız sayfa bulunamadı.',
        404,
        true
    );
}

$route_method = strtoupper($route_info['method'] ?? 'GET');
$current_method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($route_method !== $current_method) {
    display_error_page(
        '405 - Yöntem Desteklenmiyor',
        'Bu istek yöntemi desteklenmiyor.',
        405,
        false
    );
}

$render_layout = (bool)($route_info['layout'] ?? false);
$required_permission = $route_info['permission'] ?? null;
$auth_required = $route_info['auth'] ?? true;

if ($auth_required && !is_user_logged_in()) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'] ?? (BASE_URL . '/' . $request_path);
    set_flash_message('info', 'Devam etmek için lütfen giriş yapın.');
    redirect(base_url('auth/login'));
}

if ($required_permission && !check_permission($required_permission)) {
    display_error_page(
        '403 - Yetkisiz Erişim',
        'Bu sayfayı görüntüleme yetkiniz bulunmamaktadır.',
        403,
        $render_layout
    );
}

$target_file_relative_path = $route_info['file'] ?? '';
$target_file_full_path = BASE_PATH . '/' . ltrim($target_file_relative_path, '/');

if (!is_file($target_file_full_path)) {
    display_error_page(
        '500 - İç Sunucu Hatası',
        'Tanımlı rota için hedef dosya bulunamadı.',
        500,
        $render_layout
    );
}

global $current_route;
$current_route = $route_info;
$GLOBALS['current_route'] = $route_info;
$GLOBALS['current_route_path'] = $request_path;

if ($render_layout) {
    require_once BASE_PATH . '/layouts/header.php';
    require $target_file_full_path;
    require_once BASE_PATH . '/layouts/footer.php';
} else {
    require $target_file_full_path;
}