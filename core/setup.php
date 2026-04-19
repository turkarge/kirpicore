<?php

function kirpi_database_table_count(): int
{
    try {
        $stmt = db()->query("
            SELECT COUNT(*)
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
        ");

        return (int) $stmt->fetchColumn();
    } catch (Throwable $e) {
        error_log('Database table count error: ' . $e->getMessage());
        return 0;
    }
}

function kirpi_database_has_any_tables(): bool
{
    return kirpi_database_table_count() > 0;
}

function kirpi_run_sql_file(string $filePath): int
{
    if (!is_file($filePath)) {
        throw new RuntimeException('Schema file not found: ' . $filePath);
    }

    $schemaSql = file_get_contents($filePath);
    if ($schemaSql === false) {
        throw new RuntimeException('Schema file read failed: ' . $filePath);
    }

    $statementCount = 0;

    foreach (array_filter(array_map('trim', explode(';', $schemaSql))) as $statement) {
        if ($statement === '') {
            continue;
        }

        db()->exec($statement);
        $statementCount++;
    }

    return $statementCount;
}

function kirpi_module_schema_files(): array
{
    $paths = [];
    $moduleDirs = glob(BASE_PATH . '/modules/*', GLOB_ONLYDIR) ?: [];
    sort($moduleDirs);

    foreach ($moduleDirs as $moduleDir) {
        $schemaFiles = glob($moduleDir . '/database/*.sql') ?: [];
        sort($schemaFiles);

        foreach ($schemaFiles as $schemaFile) {
            $paths[] = $schemaFile;
        }
    }

    return $paths;
}

function kirpi_install_database_schema(): array
{
    $coreStatements = kirpi_run_sql_file(BASE_PATH . '/database/core.sql');
    $moduleStatements = 0;
    $installedFiles = [];

    foreach (kirpi_module_schema_files() as $schemaFile) {
        $count = kirpi_run_sql_file($schemaFile);
        $moduleStatements += $count;
        $installedFiles[] = [
            'file' => str_replace(BASE_PATH . '/', '', $schemaFile),
            'statements' => $count,
        ];
    }

    if (function_exists('sync_permission_catalog') && db_table_exists('permissions') && db_table_exists('role_permissions')) {
        sync_permission_catalog();
    }

    return [
        'core_statements' => $coreStatements,
        'module_statements' => $moduleStatements,
        'installed_files' => $installedFiles,
    ];
}

function kirpi_try_auto_setup_if_empty(): bool
{
    static $ran = false;

    if ($ran) {
        return false;
    }
    $ran = true;

    if (!env_bool('AUTO_WEB_SETUP', true)) {
        return false;
    }

    if (kirpi_database_has_any_tables()) {
        return false;
    }

    try {
        kirpi_install_database_schema();
        return true;
    } catch (Throwable $e) {
        error_log('Auto setup failed: ' . $e->getMessage());
        return false;
    }
}
