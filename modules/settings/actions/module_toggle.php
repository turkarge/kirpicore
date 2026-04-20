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

$moduleKey = trim((string) ($_POST['module_key'] ?? ''));
$isEnabledRaw = trim((string) ($_POST['is_enabled'] ?? ''));
$isEnabled = in_array($isEnabledRaw, ['1', 'true', 'on'], true);

if ($moduleKey === '') {
    json_response([
        'status' => 'error',
        'message' => 'Modul anahtari zorunludur.',
    ], 422);
}

try {
    kirpi_sync_module_registry();

    $currentUser = current_user();
    $updatedBy = (int) ($currentUser['id'] ?? 0);

    $result = kirpi_set_module_enabled($moduleKey, $isEnabled, $updatedBy > 0 ? $updatedBy : null);
    if (!($result['success'] ?? false)) {
        json_response([
            'status' => 'error',
            'message' => (string) ($result['message'] ?? 'Modul durumu guncellenemedi.'),
        ], 422);
    }

    kirpi_audit_log('module_toggle', 'settings', [
        'module_key' => $moduleKey,
        'is_enabled' => $isEnabled,
    ], 'module', null, 'success');

    json_response([
        'status' => 'success',
        'message' => (string) ($result['message'] ?? 'Modul durumu guncellendi.'),
        'reload_page' => true,
    ]);
} catch (Throwable $e) {
    error_log('module toggle action error: ' . $e->getMessage());

    kirpi_audit_log('module_toggle', 'settings', [
        'module_key' => $moduleKey,
        'is_enabled' => $isEnabled,
        'error' => $e->getMessage(),
    ], 'module', null, 'failed');

    json_response([
        'status' => 'error',
        'message' => 'Modul durumu guncellenirken bir hata olustu.',
    ], 500);
}
