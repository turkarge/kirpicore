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
    shell_output('  php shell.php db:status');
    shell_output('  php shell.php db:tables');
    shell_output('  php shell.php db:query "<sql>"');
    shell_output('  php shell.php db:permissions:install');
    shell_output('  php shell.php db:notifications:install');
    shell_output('  php shell.php db:notifications:seed-demo <user_id>');
    shell_output('');
    shell_output('Examples:');
    shell_output('  php shell.php hash:password 123456');
    shell_output('  php shell.php db:status');
    shell_output('  php shell.php db:tables');
    shell_output('  php shell.php db:query "SHOW TABLES"');
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

    if ($booted) {
        return;
    }

    require_once BASE_PATH . '/core/database.php';
    $booted = true;
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

        case 'db:permissions:install':
            shell_boot_database();

            $schemaSql = file_get_contents(BASE_PATH . '/database/permissions.sql');
            if ($schemaSql === false) {
                shell_error('permissions.sql could not be read.');
            }

            foreach (array_filter(array_map('trim', explode(';', $schemaSql))) as $statement) {
                if ($statement === '') {
                    continue;
                }

                db()->exec($statement);
            }

            sync_permission_catalog();
            shell_output('Permission schema installed and core permissions synced.');
            break;

        case 'db:notifications:install':
            shell_boot_database();

            $schemaSql = file_get_contents(BASE_PATH . '/database/notifications.sql');
            if ($schemaSql === false) {
                shell_error('notifications.sql could not be read.');
            }

            foreach (array_filter(array_map('trim', explode(';', $schemaSql))) as $statement) {
                if ($statement === '') {
                    continue;
                }

                db()->exec($statement);
            }

            shell_output('Notification schema installed.');
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
