<?php

define('BASE_PATH', __DIR__);
define('KIRPI_CORE_ENTRY', true);

require_once BASE_PATH . '/core/config.php';
require_once BASE_PATH . '/core/database.php';
require_once BASE_PATH . '/core/functions.php';
require_once BASE_PATH . '/core/setup.php';

$setupKey = trim((string) env('SETUP_KEY', ''));
$providedKey = trim((string) ($_GET['key'] ?? $_POST['key'] ?? ''));

if (APP_ENV === 'production' && $setupKey === '') {
    http_response_code(403);
    exit('SETUP_KEY tanimlanmamis. Production ortaminda setup.php kullanimi icin SETUP_KEY gerekli.');
}

if ($setupKey !== '' && !hash_equals($setupKey, $providedKey)) {
    http_response_code(403);
    exit('Gecersiz setup key.');
}

try {
    $result = kirpi_install_database_schema();

    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'status' => 'success',
        'message' => 'Kurulum tamamlandi.',
        'result' => $result,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'status' => 'error',
        'message' => 'Kurulum basarisiz oldu.',
        'error' => APP_DEBUG ? $e->getMessage() : 'internal_error',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
