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

$label = trim((string) ($_POST['label'] ?? ''));
$currentUser = current_user();
$userId = (int) ($currentUser['id'] ?? 0);

$result = kirpi_backup_create($label !== '' ? $label : null, $userId > 0 ? $userId : null);
if (!($result['success'] ?? false)) {
    kirpi_audit_log('create', 'backup', [
        'label' => $label,
        'error' => (string) ($result['message'] ?? 'backup create failed'),
    ], 'backup', null, 'failed');

    json_response([
        'status' => 'error',
        'message' => (string) ($result['message'] ?? 'Backup olusturulamadi.'),
    ], 422);
}

$backupId = (int) ($result['backup_id'] ?? 0);
$retentionDeletedCount = (int) ($result['retention_deleted_count'] ?? 0);
kirpi_audit_log('create', 'backup', [
    'backup_id' => $backupId,
    'file_name' => (string) ($result['file_name'] ?? ''),
    'retention_deleted_count' => $retentionDeletedCount,
], 'backup', $backupId, 'success');

$message = 'Backup olusturuldu. ID: ' . $backupId;
if ($retentionDeletedCount > 0) {
    $message .= ' Retention temizligi: ' . $retentionDeletedCount . ' eski backup silindi.';
}

json_response([
    'status' => 'success',
    'message' => $message,
    'reload_page' => true,
]);
