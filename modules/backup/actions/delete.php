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

if (!kirpi_backup_table_ready()) {
    json_response([
        'status' => 'error',
        'message' => 'Backup tablosu henuz kurulu degil.',
    ], 422);
}

try {
    $stmt = db()->prepare("
        SELECT id, file_name, file_path
        FROM db_backups
        WHERE id = :id
        LIMIT 1
    ");
    $stmt->execute([
        ':id' => $backupId,
    ]);

    $backup = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$backup) {
        json_response([
            'status' => 'error',
            'message' => 'Backup kaydi bulunamadi.',
        ], 404);
    }

    $fileName = (string) ($backup['file_name'] ?? '');
    $filePath = (string) ($backup['file_path'] ?? '');
    $backupDir = realpath(kirpi_backup_storage_dir()) ?: '';
    $realFile = realpath($filePath) ?: '';

    if ($backupDir !== '' && $realFile !== '' && str_starts_with($realFile, $backupDir . DIRECTORY_SEPARATOR)) {
        if (is_file($realFile)) {
            @unlink($realFile);
        }
    }

    $deleteStmt = db()->prepare("DELETE FROM db_backups WHERE id = :id");
    $deleteStmt->execute([
        ':id' => $backupId,
    ]);

    kirpi_audit_log('delete', 'backup', [
        'backup_id' => $backupId,
        'file_name' => $fileName,
    ], 'backup', $backupId, 'success');

    json_response([
        'status' => 'success',
        'message' => 'Backup kaydi silindi.',
        'reload_page' => true,
    ]);
} catch (Throwable $e) {
    error_log('backup delete error: ' . $e->getMessage());

    kirpi_audit_log('delete', 'backup', [
        'backup_id' => $backupId,
        'error' => $e->getMessage(),
    ], 'backup', $backupId, 'failed');

    json_response([
        'status' => 'error',
        'message' => 'Backup silinirken bir hata olustu.',
    ], 500);
}
