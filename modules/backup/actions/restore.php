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

$backupId = (int) ($_POST['backup_id'] ?? 0);
if ($backupId <= 0) {
    json_response([
        'status' => 'error',
        'message' => 'Gecersiz backup kaydi.',
    ], 422);
}

$currentUser = current_user();
$userId = (int) ($currentUser['id'] ?? 0);

$result = kirpi_backup_restore($backupId, $userId > 0 ? $userId : null);
if (!($result['success'] ?? false)) {
    kirpi_audit_log('restore', 'backup', [
        'backup_id' => $backupId,
        'error' => (string) ($result['message'] ?? 'backup restore failed'),
    ], 'backup', $backupId, 'failed');

    json_response([
        'status' => 'error',
        'message' => (string) ($result['message'] ?? 'Restore islemi basarisiz.'),
    ], 422);
}

kirpi_audit_log('restore', 'backup', [
    'backup_id' => $backupId,
], 'backup', $backupId, 'success');

json_response([
    'status' => 'success',
    'message' => 'Restore komutu calistirildi.',
    'reload_page' => true,
]);
