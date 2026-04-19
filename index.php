<?php

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');

define('BASE_PATH', __DIR__);
define('KIRPI_CORE_ENTRY', true);

require_once BASE_PATH . '/core/config.php';
require_once BASE_PATH . '/core/database.php';
require_once BASE_PATH . '/core/functions.php';
require_once BASE_PATH . '/core/setup.php';

kirpi_try_auto_setup_if_empty();
kirpi_try_auto_setup_if_missing();

require_once BASE_PATH . '/core/routes.php';

$request_path = trim($_GET['url'] ?? '', '/');

if ($request_path === '') {
    $requestUriPath = (string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $requestUriPath = trim($requestUriPath, '/');

    $scriptDir = trim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');

    if ($scriptDir !== '') {
        if ($requestUriPath === $scriptDir) {
            $requestUriPath = '';
        } elseif (str_starts_with($requestUriPath, $scriptDir . '/')) {
            $requestUriPath = substr($requestUriPath, strlen($scriptDir) + 1);
        }
    }

    if ($requestUriPath === 'index.php') {
        $requestUriPath = '';
    } elseif (str_starts_with($requestUriPath, 'index.php/')) {
        $requestUriPath = substr($requestUriPath, strlen('index.php/'));
    }

    $request_path = trim($requestUriPath, '/');
}

$request_path = $request_path !== '' ? $request_path : APP_DEFAULT_ROUTE;

$segments = explode('/', $request_path);
$module = $segments[0] ?? 'dashboard';

$route_info = $routes[$request_path] ?? null;

global $current_module;
$current_module = $module;

if (!$route_info) {
    display_error_page(
        '404 - Sayfa Bulunamadi',
        'Aradiginiz sayfa bulunamadi.',
        404,
        true
    );
}

$route_method = strtoupper($route_info['method'] ?? 'GET');
$current_method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($route_method !== $current_method) {
    display_error_page(
        '405 - Yontem Desteklenmiyor',
        'Bu istek yontemi desteklenmiyor.',
        405,
        false
    );
}

$render_layout = (bool)($route_info['layout'] ?? false);
$required_permission = $route_info['permission'] ?? null;
$auth_required = $route_info['auth'] ?? true;

if ($auth_required && !is_user_logged_in()) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'] ?? (BASE_URL . '/' . $request_path);
    set_flash_message('info', 'Devam etmek icin lutfen giris yapin.');
    redirect(base_url('auth/login'));
}

if ($auth_required && is_user_logged_in() && !validate_active_session_user()) {
    unset($_SESSION['user']);
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'] ?? (BASE_URL . '/' . $request_path);
    set_flash_message('warning', 'Hesabinizin veya rolunuzun durumu degismis. Lutfen tekrar giris yapin.');
    redirect(base_url('auth/login'));
}

if ($required_permission && !check_permission($required_permission)) {
    display_error_page(
        '403 - Yetkisiz Erisim',
        'Bu sayfayi goruntuleme yetkiniz bulunmamaktadir.',
        403,
        $render_layout
    );
}

$target_file_relative_path = $route_info['file'] ?? '';
$target_file_full_path = BASE_PATH . '/' . ltrim($target_file_relative_path, '/');

if (!is_file($target_file_full_path)) {
    display_error_page(
        '500 - Ic Sunucu Hatasi',
        'Tanimli rota icin hedef dosya bulunamadi.',
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
