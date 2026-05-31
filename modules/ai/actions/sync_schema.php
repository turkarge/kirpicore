<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

require_once BASE_PATH . '/modules/ai/language.php';

require_action('POST', true);

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    json_response([
        'status' => 'error',
        'message' => ai_lang('csrf_failed'),
    ], 419);
}

$result = kirpi_ai_sync_schema_registry_from_manifests();

if (($result['status'] ?? '') === 'error') {
    json_response([
        'status' => 'error',
        'message' => ai_lang('schema_sync_error'),
    ], 422);
}

if (($result['status'] ?? '') === 'partial') {
    json_response([
        'status' => 'warning',
        'message' => ai_lang('schema_sync_partial'),
        'reload_page' => true,
    ], 207);
}

json_response([
    'status' => 'success',
    'message' => sprintf(
        ai_lang('schema_sync_success'),
        (int) ($result['entity_count'] ?? 0),
        (int) ($result['field_count'] ?? 0)
    ),
    'reload_page' => true,
]);
