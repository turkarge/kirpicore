<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

require_once BASE_PATH . '/modules/users/language.php';

if (!db_table_exists('users')) {
    http_response_code(404);
    echo users_lang('table_not_ready');
    exit;
}

$search = trim((string) ($_GET['search'] ?? ''));
$roleId = trim((string) ($_GET['role_id'] ?? ''));
$status = trim((string) ($_GET['status'] ?? ''));
$format = trim((string) ($_GET['format'] ?? 'csv'));

$where = [];
$params = [];

if ($search !== '') {
    $where[] = '(u.name LIKE :search OR u.email LIKE :search OR r.name LIKE :search)';
    $params[':search'] = '%' . $search . '%';
}

if ($roleId !== '') {
    $where[] = 'u.role_id = :role_id';
    $params[':role_id'] = (int) $roleId;
}

if ($status !== '' && in_array($status, ['0', '1'], true)) {
    $where[] = 'u.is_active = :is_active';
    $params[':is_active'] = (int) $status;
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$sql = "
    SELECT
        u.id,
        u.name,
        u.email,
        u.is_active,
        u.lock_enabled,
        u.session_version,
        u.created_at,
        u.updated_at,
        r.name AS role_name,
        r.is_active AS role_is_active
    FROM users u
    LEFT JOIN roles r ON r.id = u.role_id
    {$whereSql}
    ORDER BY u.id DESC
    LIMIT 5000
";

$stmt = db()->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();

$rows = [];
while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $roleLabel = (string) ($user['role_name'] ?? '');

    if ($roleLabel !== '' && isset($user['role_is_active']) && (int) $user['role_is_active'] !== 1) {
        $roleLabel .= users_lang('status_inactive_suffix');
    }

    $rows[] = [
        (int) ($user['id'] ?? 0),
        (string) ($user['name'] ?? ''),
        (string) ($user['email'] ?? ''),
        $roleLabel,
        (int) ($user['is_active'] ?? 0) === 1 ? users_lang('active') : users_lang('inactive'),
        (int) ($user['lock_enabled'] ?? 0) === 1 ? users_lang('lock_enabled') : users_lang('lock_disabled'),
        (int) ($user['session_version'] ?? 0),
        kirpi_format_datetime((string) ($user['created_at'] ?? '')),
        kirpi_format_datetime((string) ($user['updated_at'] ?? '')),
    ];
}

kirpi_export_response($format, 'users-' . date('Ymd-His'), [
    'ID',
    users_lang('name_surname'),
    users_lang('email'),
    users_lang('role'),
    users_lang('table_status'),
    users_lang('lock_status'),
    users_lang('session_version'),
    users_lang('table_created_at'),
    users_lang('updated_at'),
], $rows);
