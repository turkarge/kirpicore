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

$result = kirpi_backup_verify($backupId, $userId > 0 ? $userId : null);
if (!($result['success'] ?? false)) {
    kirpi_audit_log('verify', 'backup', [
        'backup_id' => $backupId,
        'error' => (string) ($result['message'] ?? 'backup verify failed'),
    ], 'backup', $backupId, 'failed');

    json_response([
        'status' => 'error',
        'message' => (string) ($result['message'] ?? 'Backup dogrulama basarisiz.'),
    ], 422);
}

$message = (string) ($result['message'] ?? 'Backup dogrulandi.');
$checksum = (string) ($result['checksum'] ?? '');
$dryRun = (bool) ($result['dry_run'] ?? false);
$tableCount = (int) ($result['dry_run_table_count'] ?? 0);

if ($checksum !== '') {
    $message .= ' SHA256: ' . substr($checksum, 0, 12) . '...';
}
if ($dryRun) {
    $message .= ' Dry-run tablo: ' . $tableCount . '.';
}

kirpi_audit_log('verify', 'backup', [
    'backup_id' => $backupId,
    'checksum' => $checksum,
    'dry_run' => $dryRun,
    'dry_run_table_count' => $tableCount,
], 'backup', $backupId, 'success');

json_response([
    'status' => 'success',
    'message' => $message,
]);

