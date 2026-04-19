<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script can only be run from CLI.' . PHP_EOL);
}

define('BASE_PATH', __DIR__);
define('KIRPI_CORE_ENTRY', true);

require_once BASE_PATH . '/core/config.php';
require_once BASE_PATH . '/core/functions.php';

function shell_output(string $message): void
{
    fwrite(STDOUT, $message . PHP_EOL);
}

function shell_error(string $message, int $code = 1): never
{
    fwrite(STDERR, $message . PHP_EOL);
    exit($code);
}

function shell_usage(): void
{
    shell_output('Kirpi Core Shell');
    shell_output('');
    shell_output('Usage:');
    shell_output('  php shell.php hash:password <password>');
    shell_output('  php shell.php db:create');
    shell_output('  php shell.php db:status');
    shell_output('  php shell.php db:tables');
    shell_output('  php shell.php db:query "<sql>"');
    shell_output('  php shell.php db:core:install');
    shell_output('  php shell.php db:modules:install [module]');
    shell_output('  php shell.php db:install');
    shell_output('  php shell.php db:permissions:install');
    shell_output('  php shell.php db:notifications:install');
    shell_output('  php shell.php db:notifications:seed-demo <user_id>');
    shell_output('');
    shell_output('Examples:');
    shell_output('  php shell.php hash:password 123456');
    shell_output('  php shell.php db:create');
    shell_output('  php shell.php db:status');
    shell_output('  php shell.php db:tables');
    shell_output('  php shell.php db:query "SHOW TABLES"');
    shell_output('  php shell.php db:core:install');
    shell_output('  php shell.php db:modules:install notifications');
    shell_output('  php shell.php db:install');
    shell_output('  php shell.php db:permissions:install');
    shell_output('  php shell.php db:notifications:install');
    shell_output('  php shell.php db:notifications:seed-demo 1');
}

function shell_render_rows(array $rows): void
{
    if (empty($rows)) {
        shell_output('No rows returned.');
        return;
    }

    foreach ($rows as $row) {
        shell_output(json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}

function shell_boot_database(): void
{
    static $booted = false;
    global $pdo;

    if ($booted) {
        return;
    }

    require_once BASE_PATH . '/core/database.php';

    if (!isset($pdo) || !$pdo instanceof PDO) {
        shell_error('Database bootstrap failed: PDO instance is unavailable.');
    }

    $booted = true;
}

function shell_create_database_if_not_exists(): void
{
    $dsn = sprintf(
        'mysql:host=%s;port=%s;charset=%s',
        DB_HOST,
        DB_PORT,
        DB_CHARSET
    );

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    $dbName = str_replace('`', '``', DB_NAME);
    $charset = preg_replace('/[^a-zA-Z0-9_]/', '', DB_CHARSET) ?: 'utf8mb4';

    $pdo->exec(sprintf(
        "CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET %s COLLATE %s_unicode_ci",
        $dbName,
        $charset,
        $charset
    ));
}

function shell_run_sql_file(string $filePath): int
{
    $schemaSql = file_get_contents($filePath);
    if ($schemaSql === false) {
        shell_error('SQL file could not be read: ' . $filePath);
    }

    $count = 0;

    foreach (array_filter(array_map('trim', explode(';', $schemaSql))) as $statement) {
        if ($statement === '') {
            continue;
        }

        db()->exec($statement);
        $count++;
    }

    return $count;
}

function shell_module_schema_files(?string $moduleName = null): array
{
    $paths = [];

    if ($moduleName !== null && $moduleName !== '') {
        $moduleSchemaPattern = BASE_PATH . '/modules/' . $moduleName . '/database/*.sql';
        $paths = glob($moduleSchemaPattern) ?: [];
        sort($paths);
        return $paths;
    }

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

$command = $argv[1] ?? null;

if ($command === null || in_array($command, ['help', '--help', '-h'], true)) {
    shell_usage();
    exit(0);
}

try {
    switch ($command) {
        case 'hash:password':
            $password = $argv[2] ?? null;

            if ($password === null || $password === '') {
                shell_error('Password is required.');
            }

            shell_output(password_hash($password, PASSWORD_DEFAULT));
            break;

        case 'db:create':
            shell_create_database_if_not_exists();
            shell_output('Database ensured: ' . DB_NAME);
            break;

        case 'db:status':
            shell_boot_database();

            $stmt = db()->query('SELECT DATABASE() AS database_name, NOW() AS server_time');
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                shell_error('Database connection is active but no status row was returned.');
            }

            shell_output('Database connection successful.');
            shell_output('Database: ' . ($row['database_name'] ?? '-'));
            shell_output('Server Time: ' . ($row['server_time'] ?? '-'));
            break;

        case 'db:tables':
            shell_boot_database();

            $stmt = db()->query('SHOW TABLES');
            $rows = $stmt->fetchAll(PDO::FETCH_NUM);

            if (empty($rows)) {
                shell_output('No tables found.');
                break;
            }

            foreach ($rows as $row) {
                shell_output((string) ($row[0] ?? ''));
            }
            break;

        case 'db:query':
            $sql = $argv[2] ?? null;

            if ($sql === null || trim($sql) === '') {
                shell_error('SQL query is required.');
            }

            shell_boot_database();

            $stmt = db()->query($sql);

            if ($stmt instanceof PDOStatement) {
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                shell_render_rows($rows);
                break;
            }

            shell_output('Query executed.');
            break;

        case 'db:core:install':
            shell_create_database_if_not_exists();
            shell_boot_database();

            $coreSchemaPath = BASE_PATH . '/database/core.sql';
            $statementCount = shell_run_sql_file($coreSchemaPath);

            shell_output('Core schema installed. Statements: ' . $statementCount);
            break;

        case 'db:modules:install':
            shell_create_database_if_not_exists();
            shell_boot_database();

            $moduleName = $argv[2] ?? null;
            $schemaFiles = shell_module_schema_files($moduleName);

            if (empty($schemaFiles)) {
                $target = $moduleName ? ('module "' . $moduleName . '"') : 'all modules';
                shell_output('No schema files found for ' . $target . '.');
                break;
            }

            $totalStatements = 0;
            foreach ($schemaFiles as $schemaFile) {
                $count = shell_run_sql_file($schemaFile);
                $totalStatements += $count;

                $relativePath = str_replace(BASE_PATH . '/', '', $schemaFile);
                shell_output('Installed: ' . $relativePath . ' (' . $count . ' statements)');
            }

            if (db_table_exists('permissions') && db_table_exists('role_permissions')) {
                sync_permission_catalog();
                shell_output('Permission catalog synced.');
            }

            shell_output('Module schemas installed. Total statements: ' . $totalStatements);
            break;

        case 'db:install':
            shell_create_database_if_not_exists();
            shell_boot_database();

            $coreSchemaPath = BASE_PATH . '/database/core.sql';
            $coreStatementCount = shell_run_sql_file($coreSchemaPath);
            shell_output('Core schema installed. Statements: ' . $coreStatementCount);

            $schemaFiles = shell_module_schema_files();
            $moduleStatementCount = 0;

            foreach ($schemaFiles as $schemaFile) {
                $count = shell_run_sql_file($schemaFile);
                $moduleStatementCount += $count;

                $relativePath = str_replace(BASE_PATH . '/', '', $schemaFile);
                shell_output('Installed: ' . $relativePath . ' (' . $count . ' statements)');
            }

            if (db_table_exists('permissions') && db_table_exists('role_permissions')) {
                sync_permission_catalog();
                shell_output('Permission catalog synced.');
            }

            shell_output('Database setup completed. Core statements: ' . $coreStatementCount . ', Module statements: ' . $moduleStatementCount);
            break;

        case 'db:permissions:install':
            shell_create_database_if_not_exists();
            shell_boot_database();

            $statementCount = shell_run_sql_file(BASE_PATH . '/modules/roles/database/permissions.sql');

            sync_permission_catalog();
            shell_output('Permission schema installed. Statements: ' . $statementCount);
            shell_output('Core permissions synced.');
            break;

        case 'db:notifications:install':
            shell_create_database_if_not_exists();
            shell_boot_database();

            $statementCount = shell_run_sql_file(BASE_PATH . '/modules/notifications/database/schema.sql');

            shell_output('Notification schema installed. Statements: ' . $statementCount);
            break;

        case 'db:notifications:seed-demo':
            shell_boot_database();

            if (!db_table_exists('notifications')) {
                shell_error('notifications table is not installed. Run db:notifications:install first.');
            }

            $userId = (int) ($argv[2] ?? 0);
            if ($userId <= 0) {
                shell_error('User ID is required.');
            }

            $userStmt = db()->prepare('SELECT id, name, email FROM users WHERE id = :id LIMIT 1');
            $userStmt->execute([
                ':id' => $userId,
            ]);

            $user = $userStmt->fetch(PDO::FETCH_ASSOC);
            if (!$user) {
                shell_error('User not found.');
            }

            $notifications = [
                [
                    'title' => 'Hoş geldiniz',
                    'message' => ($user['name'] ?? 'Kullanıcı') . ' için demo bildirim oluşturuldu.',
                    'channel' => 'in_app',
                    'read_at' => null,
                ],
                [
                    'title' => 'Rol güncellendi',
                    'message' => 'Kullanıcı rol değişikliği başarıyla tamamlandı.',
                    'channel' => 'in_app',
                    'read_at' => null,
                ],
                [
                    'title' => 'Güvenlik bildirimi',
                    'message' => 'Son giriş hareketiniz kaydedildi.',
                    'channel' => 'email',
                    'read_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'title' => 'Sistem duyurusu',
                    'message' => 'Bildirim modülü test verisi başarıyla eklendi.',
                    'channel' => 'in_app',
                    'read_at' => null,
                ],
            ];

            $insertStmt = db()->prepare("
                INSERT INTO notifications (user_id, title, message, channel, read_at)
                VALUES (:user_id, :title, :message, :channel, :read_at)
            ");

            foreach ($notifications as $notification) {
                $insertStmt->execute([
                    ':user_id' => $userId,
                    ':title' => $notification['title'],
                    ':message' => $notification['message'],
                    ':channel' => $notification['channel'],
                    ':read_at' => $notification['read_at'],
                ]);
            }

            shell_output('Demo notifications inserted for user #' . $userId . '.');
            break;

        default:
            shell_error('Unknown command: ' . $command);
    }
} catch (Throwable $e) {
    shell_error('Shell error: ' . $e->getMessage());
}
