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

function kirpi_mysql_ssl_candidates(): array
{
    $sslMode = strtoupper(trim((string) env('DB_SSL_MODE', 'DISABLED')));
    $allowed = ['DISABLED', 'PREFERRED', 'REQUIRED', 'VERIFY_CA', 'VERIFY_IDENTITY'];

    if (!in_array($sslMode, $allowed, true)) {
        $sslMode = 'DISABLED';
    }

    if ($sslMode === 'DISABLED') {
        return [
            '',
            '--skip-ssl',
            '--ssl=0',
            '--ssl-mode=DISABLED',
        ];
    }

    if ($sslMode === 'PREFERRED') {
        return [
            '',
            '--ssl-mode=PREFERRED',
        ];
    }

    return [
        '--ssl-mode=REQUIRED',
        '--ssl',
    ];
}

function kirpi_backup_run_command(string $command, ?string $errorPath = null): array
{
    $outputLines = [];
    $exitCode = 0;
    exec($command, $outputLines, $exitCode);

    $errorOutput = '';
    if ($errorPath !== null && is_file($errorPath)) {
        $errorOutput = trim((string) file_get_contents($errorPath));
        @unlink($errorPath);
    }

    $output = trim(implode(PHP_EOL, $outputLines));
    if ($errorOutput === '' && $output !== '') {
        $errorOutput = $output;
    }

    return [
        'exit_code' => $exitCode,
        'error_output' => $errorOutput,
    ];
}

function kirpi_backup_is_unknown_ssl_option(string $errorText): bool
{
    $errorText = strtolower($errorText);
    if ($errorText === '') {
        return false;
    }

    return str_contains($errorText, 'unknown variable') || str_contains($errorText, 'unknown option');
}

function kirpi_backup_ignore_tables_args(): string
{
    $includeSystemTables = filter_var((string) env('BACKUP_INCLUDE_SYSTEM_TABLES', 'false'), FILTER_VALIDATE_BOOLEAN);
    if ($includeSystemTables) {
        return '';
    }

    $dbName = (string) DB_NAME;
    $ignoredTables = [
        'db_backups',
        'db_backup_restores',
    ];

    $args = [];
    foreach ($ignoredTables as $table) {
        $args[] = '--ignore-table=' . escapeshellarg($dbName . '.' . $table);
    }

    return implode(' ', $args);
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

    $attemptErrors = [];
    $success = false;

    foreach (kirpi_mysql_ssl_candidates() as $sslArg) {
        $command = sprintf(
            'mysqldump --single-transaction --routines --triggers --events %s %s -h %s -P %s -u %s %s %s 1> %s 2> %s',
            $sslArg,
            kirpi_backup_ignore_tables_args(),
            escapeshellarg((string) DB_HOST),
            escapeshellarg((string) DB_PORT),
            escapeshellarg((string) DB_USER),
            kirpi_mysql_password_arg(),
            escapeshellarg((string) DB_NAME),
            escapeshellarg($fullPath),
            escapeshellarg($errorPath)
        );

        $run = kirpi_backup_run_command($command, $errorPath);
        $exitCode = (int) ($run['exit_code'] ?? 1);
        $errorOutput = trim((string) ($run['error_output'] ?? ''));

        if ($exitCode === 0 && is_file($fullPath) && filesize($fullPath) > 0) {
            $success = true;
            break;
        }

        if (is_file($fullPath)) {
            @unlink($fullPath);
        }

        if ($errorOutput !== '') {
            $attemptErrors[] = $errorOutput;
        } else {
            $attemptErrors[] = 'exit code: ' . $exitCode;
        }

        if ($errorOutput !== '' && !kirpi_backup_is_unknown_ssl_option($errorOutput)) {
            // Not an option-compatibility issue; no need to keep trying unsupported variants.
            continue;
        }
    }

    if (!$success) {
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }

        return [
            'success' => false,
            'message' => 'mysqldump basarisiz. ' . implode(' | ', array_slice($attemptErrors, -2)),
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

    $stmt = db()->prepare("\n        SELECT id, label, file_name, file_path, file_size, status, created_by\n        FROM db_backups\n        WHERE id = :id\n        LIMIT 1\n    ");
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

    $exitCode = 1;
    $output = '';

    foreach (kirpi_mysql_ssl_candidates() as $sslArg) {
        $command = sprintf(
            'mysql %s -h %s -P %s -u %s %s %s < %s 2>&1',
            $sslArg,
            escapeshellarg((string) DB_HOST),
            escapeshellarg((string) DB_PORT),
            escapeshellarg((string) DB_USER),
            kirpi_mysql_password_arg(),
            escapeshellarg((string) DB_NAME),
            escapeshellarg($filePath)
        );

        $run = kirpi_backup_run_command($command);
        $exitCode = (int) ($run['exit_code'] ?? 1);
        $output = trim((string) ($run['error_output'] ?? ''));

        if ($exitCode === 0) {
            break;
        }
    }

    if ($exitCode !== 0) {
        return [
            'success' => false,
            'message' => 'Restore komutu basarisiz. ' . ($output !== '' ? $output : ('exit code: ' . $exitCode)),
        ];
    }

    // Restore dump eskiyse backup kayitlarini geri alabilir/silebilir.
    // Dosya kaydini geri ekleyip restore logunu "best effort" olarak yazariz.
    try {
        if (db_table_exists('db_backups')) {
            $checkStmt = db()->prepare("\n                SELECT id\n                FROM db_backups\n                WHERE id = :id\n                LIMIT 1\n            ");
            $checkStmt->execute([
                ':id' => $backupId,
            ]);

            $exists = $checkStmt->fetchColumn();
            if ($exists === false) {
                $reInsertStmt = db()->prepare("\n                    INSERT INTO db_backups (\n                        label,\n                        file_name,\n                        file_path,\n                        file_size,\n                        status,\n                        created_by\n                    ) VALUES (\n                        :label,\n                        :file_name,\n                        :file_path,\n                        :file_size,\n                        :status,\n                        :created_by\n                    )\n                ");
                $reInsertStmt->execute([
                    ':label' => (string) ($backup['label'] ?? ('Backup ' . date('d.m.Y H:i'))),
                    ':file_name' => (string) ($backup['file_name'] ?? ''),
                    ':file_path' => (string) ($backup['file_path'] ?? ''),
                    ':file_size' => (int) ($backup['file_size'] ?? 0),
                    ':status' => (string) ($backup['status'] ?? 'ready'),
                    ':created_by' => (int) ($backup['created_by'] ?? 0) ?: null,
                ]);
            }
        }

        if (db_table_exists('db_backup_restores')) {
            $logStmt = db()->prepare("\n                INSERT INTO db_backup_restores (\n                    backup_id,\n                    restored_by,\n                    restore_output\n                ) VALUES (\n                    :backup_id,\n                    :restored_by,\n                    :restore_output\n                )\n            ");
            $logStmt->execute([
                ':backup_id' => $backupId,
                ':restored_by' => $userId,
                ':restore_output' => mb_substr(trim((string) $output), 0, 5000),
            ]);
        }
    } catch (Throwable $e) {
        error_log('backup restore post-process error: ' . $e->getMessage());
    }

    return [
        'success' => true,
        'message' => 'Backup geri yukleme komutu calistirildi.',
    ];
}
