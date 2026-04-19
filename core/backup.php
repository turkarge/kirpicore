<?php

function kirpi_backup_table_ready(): bool
{
    return db_table_exists('db_backups');
}

function kirpi_backup_storage_dir(): string
{
    $dir = BASE_PATH . '/storage/backups';

    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    return $dir;
}

function kirpi_shell_exec_available(): bool
{
    if (!function_exists('shell_exec')) {
        return false;
    }

    $disabled = (string) ini_get('disable_functions');
    if ($disabled === '') {
        return true;
    }

    $items = array_map('trim', explode(',', $disabled));
    return !in_array('shell_exec', $items, true);
}

function kirpi_mysql_password_arg(): string
{
    $password = (string) DB_PASS;
    return '--password=' . escapeshellarg($password);
}

function kirpi_mysql_ssl_arg(): string
{
    $sslMode = strtoupper(trim((string) env('DB_SSL_MODE', 'DISABLED')));
    $allowed = ['DISABLED', 'PREFERRED', 'REQUIRED', 'VERIFY_CA', 'VERIFY_IDENTITY'];

    if (!in_array($sslMode, $allowed, true)) {
        $sslMode = 'DISABLED';
    }

    if (in_array($sslMode, ['REQUIRED', 'VERIFY_CA', 'VERIFY_IDENTITY'], true)) {
        // Generic flag supported by both MySQL and MariaDB clients.
        return '--ssl';
    }

    // DISABLED/PREFERRED -> do not pass client-specific SSL mode flags.
    return '';
}

function kirpi_backup_create(?string $label = null, ?int $userId = null): array
{
    if (!kirpi_backup_table_ready()) {
        return [
            'success' => false,
            'message' => 'Backup table is not ready.',
        ];
    }

    if (!kirpi_shell_exec_available()) {
        return [
            'success' => false,
            'message' => 'shell_exec kullanilamiyor. Backup olusturulamadi.',
        ];
    }

    $dir = kirpi_backup_storage_dir();
    $stamp = date('Ymd_His');
    $safeLabel = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string) ($label ?? 'manual')) ?: 'manual';
    $fileName = 'backup_' . $stamp . '_' . $safeLabel . '.sql';
    $fullPath = $dir . '/' . $fileName;
    $errorPath = $dir . '/' . $fileName . '.err';

    $command = sprintf(
        'mysqldump --single-transaction --routines --triggers --events %s -h %s -P %s -u %s %s %s 1> %s 2> %s',
        kirpi_mysql_ssl_arg(),
        escapeshellarg((string) DB_HOST),
        escapeshellarg((string) DB_PORT),
        escapeshellarg((string) DB_USER),
        kirpi_mysql_password_arg(),
        escapeshellarg((string) DB_NAME),
        escapeshellarg($fullPath),
        escapeshellarg($errorPath)
    );

    $outputLines = [];
    $exitCode = 0;
    exec($command, $outputLines, $exitCode);

    $errorOutput = '';
    if (is_file($errorPath)) {
        $errorOutput = trim((string) file_get_contents($errorPath));
        @unlink($errorPath);
    }

    if ($exitCode !== 0 || !is_file($fullPath) || filesize($fullPath) === 0) {
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }

        return [
            'success' => false,
            'message' => 'mysqldump basarisiz. ' . ($errorOutput !== '' ? $errorOutput : ('exit code: ' . $exitCode)),
        ];
    }

    $size = (int) filesize($fullPath);

    $stmt = db()->prepare("\n        INSERT INTO db_backups (\n            label,\n            file_name,\n            file_path,\n            file_size,\n            status,\n            created_by\n        ) VALUES (\n            :label,\n            :file_name,\n            :file_path,\n            :file_size,\n            'ready',\n            :created_by\n        )\n    ");
    $stmt->execute([
        ':label' => $label !== null && trim($label) !== '' ? trim($label) : ('Backup ' . date('d.m.Y H:i')),
        ':file_name' => $fileName,
        ':file_path' => $fullPath,
        ':file_size' => $size,
        ':created_by' => $userId,
    ]);

    return [
        'success' => true,
        'backup_id' => (int) db()->lastInsertId(),
        'file_name' => $fileName,
        'file_size' => $size,
    ];
}

function kirpi_backup_restore(int $backupId, ?int $userId = null): array
{
    if (!kirpi_backup_table_ready()) {
        return [
            'success' => false,
            'message' => 'Backup table is not ready.',
        ];
    }

    if (!kirpi_shell_exec_available()) {
        return [
            'success' => false,
            'message' => 'shell_exec kullanilamiyor. Restore calistirilamadi.',
        ];
    }

    $stmt = db()->prepare("\n        SELECT id, file_path, file_name\n        FROM db_backups\n        WHERE id = :id\n        LIMIT 1\n    ");
    $stmt->execute([
        ':id' => $backupId,
    ]);

    $backup = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$backup) {
        return [
            'success' => false,
            'message' => 'Backup kaydi bulunamadi.',
        ];
    }

    $filePath = (string) ($backup['file_path'] ?? '');
    if ($filePath === '' || !is_file($filePath)) {
        return [
            'success' => false,
            'message' => 'Backup dosyasi bulunamadi.',
        ];
    }

    $command = sprintf(
        'mysql %s -h %s -P %s -u %s %s %s < %s 2>&1',
        kirpi_mysql_ssl_arg(),
        escapeshellarg((string) DB_HOST),
        escapeshellarg((string) DB_PORT),
        escapeshellarg((string) DB_USER),
        kirpi_mysql_password_arg(),
        escapeshellarg((string) DB_NAME),
        escapeshellarg($filePath)
    );

    $outputLines = [];
    $exitCode = 0;
    exec($command, $outputLines, $exitCode);
    $output = trim(implode(PHP_EOL, $outputLines));

    $logStmt = db()->prepare("\n        INSERT INTO db_backup_restores (\n            backup_id,\n            restored_by,\n            restore_output\n        ) VALUES (\n            :backup_id,\n            :restored_by,\n            :restore_output\n        )\n    ");
    $logStmt->execute([
        ':backup_id' => $backupId,
        ':restored_by' => $userId,
        ':restore_output' => mb_substr(trim((string) $output), 0, 5000),
    ]);

    if ($exitCode !== 0) {
        return [
            'success' => false,
            'message' => 'Restore komutu basarisiz. ' . ($output !== '' ? $output : ('exit code: ' . $exitCode)),
        ];
    }

    return [
        'success' => true,
        'message' => 'Backup geri yukleme komutu calistirildi.',
    ];
}
