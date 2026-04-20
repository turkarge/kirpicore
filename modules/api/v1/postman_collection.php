<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

require_action('GET', false);

$filePath = BASE_PATH . '/postman/KirpiCore_API_v1.postman_collection.json';
if (!is_file($filePath) || !is_readable($filePath)) {
    api_error(404, 'Postman collection dosyasi bulunamadi.');
}

$content = (string) file_get_contents($filePath);
if ($content === '') {
    api_error(500, 'Postman collection dosyasi okunamadi.');
}

header('Content-Type: application/json; charset=UTF-8');
header('Content-Disposition: attachment; filename="KirpiCore_API_v1.postman_collection.json"');
header('Content-Length: ' . (string) strlen($content));
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

echo $content;
exit;

