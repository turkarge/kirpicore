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

function kirpi_extract_tables_from_sql(string $sqlContent): array
{
    $tables = [];

    if (preg_match_all('/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+`?([a-zA-Z0-9_]+)`?/i', $sqlContent, $matches)) {
        foreach ($matches[1] as $tableName) {
            $name = trim((string) $tableName);
            if ($name !== '') {
                $tables[] = $name;
            }
        }
    }

    return array_values(array_unique($tables));
}

function kirpi_schema_file_tables(string $filePath): array
{
    if (!is_file($filePath)) {
        return [];
    }

    $content = file_get_contents($filePath);
    if ($content === false) {
        return [];
    }

    return kirpi_extract_tables_from_sql($content);
}

function kirpi_schema_files_with_tables(): array
{
    $schemaFiles = [BASE_PATH . '/database/core.sql'];
    foreach (kirpi_module_schema_files() as $moduleSchemaFile) {
        $schemaFiles[] = $moduleSchemaFile;
    }

    $result = [];
    foreach ($schemaFiles as $filePath) {
        $tables = kirpi_schema_file_tables($filePath);
        if (empty($tables)) {
            continue;
        }

        $result[] = [
            'file' => $filePath,
            'tables' => $tables,
        ];
    }

    return $result;
}

function kirpi_missing_tables_report(): array
{
    $missingTables = [];
    $missingByFile = [];
    $requiredCount = 0;

    foreach (kirpi_schema_files_with_tables() as $item) {
        $filePath = (string) ($item['file'] ?? '');
        $tables = (array) ($item['tables'] ?? []);

        if ($filePath === '' || empty($tables)) {
            continue;
        }

        $requiredCount += count($tables);
        $fileMissing = [];

        foreach ($tables as $tableName) {
            if (!db_table_exists($tableName, true)) {
                $fileMissing[] = $tableName;
                $missingTables[] = $tableName;
            }
        }

        if (!empty($fileMissing)) {
            $missingByFile[] = [
                'file' => str_replace(BASE_PATH . '/', '', $filePath),
                'tables' => array_values(array_unique($fileMissing)),
            ];
        }
    }

    $missingTables = array_values(array_unique($missingTables));

    return [
        'required_table_count' => $requiredCount,
        'missing_table_count' => count($missingTables),
        'missing_tables' => $missingTables,
        'missing_by_file' => $missingByFile,
    ];
}

function kirpi_install_missing_database_schema(): array
{
    $report = kirpi_missing_tables_report();
    $installedFiles = [];
    $installedStatements = 0;

    foreach ($report['missing_by_file'] as $item) {
        $relativeFile = (string) ($item['file'] ?? '');
        if ($relativeFile === '') {
            continue;
        }

        $fullPath = BASE_PATH . '/' . $relativeFile;
        $count = kirpi_run_sql_file($fullPath);
        $installedStatements += $count;

        $installedFiles[] = [
            'file' => $relativeFile,
            'statements' => $count,
            'target_tables' => $item['tables'] ?? [],
        ];
    }

    if (function_exists('sync_permission_catalog') && db_table_exists('permissions') && db_table_exists('role_permissions')) {
        sync_permission_catalog();
    }

    $after = kirpi_missing_tables_report();

    return [
        'before' => $report,
        'after' => $after,
        'installed_files' => $installedFiles,
        'installed_statements' => $installedStatements,
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

function kirpi_try_auto_setup_if_missing(): bool
{
    static $ran = false;

    if ($ran) {
        return false;
    }
    $ran = true;

    if (!env_bool('AUTO_DB_ENSURE_MISSING', false)) {
        return false;
    }

    try {
        $report = kirpi_missing_tables_report();
        if (($report['missing_table_count'] ?? 0) <= 0) {
            return false;
        }

        kirpi_install_missing_database_schema();
        return true;
    } catch (Throwable $e) {
        error_log('Auto missing schema setup failed: ' . $e->getMessage());
        return false;
    }
}
