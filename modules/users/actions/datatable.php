<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

require_action('GET', true);

$draw = max(0, (int) ($_GET['draw'] ?? 0));
$start = max(0, (int) ($_GET['start'] ?? 0));
$length = (int) ($_GET['length'] ?? 10);
$length = min(100, max(10, $length));
$columns = is_array($_GET['columns'] ?? null) ? $_GET['columns'] : [];
$orders = is_array($_GET['order'] ?? null) ? $_GET['order'] : [];
$globalSearch = trim((string) ($_GET['search']['value'] ?? ''));
$roleId = trim((string) ($_GET['role_id'] ?? ''));
$status = trim((string) ($_GET['status'] ?? ''));

$columnMap = [
    'name' => 'u.name',
    'email' => 'u.email',
    'role_name' => 'r.name',
    'is_active' => 'u.is_active',
    'created_at' => 'u.created_at',
    'created_at_display' => 'u.created_at',
    'updated_at' => 'u.updated_at',
    'updated_at_display' => 'u.updated_at',
];

$where = [];
$params = [];

if ($globalSearch !== '') {
    $pattern = '%' . $globalSearch . '%';
    $where[] = '(u.name LIKE :global_name OR u.email LIKE :global_email OR r.name LIKE :global_role)';
    $params[':global_name'] = $pattern;
    $params[':global_email'] = $pattern;
    $params[':global_role'] = $pattern;
}

if ($roleId !== '' && ctype_digit($roleId)) {
    $where[] = 'u.role_id = :role_id';
    $params[':role_id'] = (int) $roleId;
}

if (in_array($status, ['0', '1'], true)) {
    $where[] = 'u.is_active = :status';
    $params[':status'] = (int) $status;
}

foreach ($columns as $index => $column) {
    if (!is_array($column)) {
        continue;
    }
    $columnName = (string) ($column['name'] ?? $column['data'] ?? '');
    $searchValue = trim((string) ($column['search']['value'] ?? ''));
    if ($searchValue === '' || !isset($columnMap[$columnName])) {
        continue;
    }

    $parameter = ':column_' . (int) $index;
    if ($columnName === 'is_active' && in_array($searchValue, ['0', '1'], true)) {
        $where[] = $columnMap[$columnName] . ' = ' . $parameter;
        $params[$parameter] = (int) $searchValue;
        continue;
    }

    $where[] = $columnMap[$columnName] . ' LIKE ' . $parameter;
    $params[$parameter] = '%' . $searchValue . '%';
}

$orderParts = [];
foreach (array_slice($orders, 0, 3) as $order) {
    if (!is_array($order)) {
        continue;
    }
    $columnIndex = (int) ($order['column'] ?? -1);
    $column = $columns[$columnIndex] ?? null;
    $columnName = is_array($column) ? (string) ($column['name'] ?? $column['data'] ?? '') : '';
    if (!isset($columnMap[$columnName])) {
        continue;
    }
    $direction = strtolower((string) ($order['dir'] ?? 'asc')) === 'desc' ? 'DESC' : 'ASC';
    $orderParts[] = $columnMap[$columnName] . ' ' . $direction;
}
$orderSql = $orderParts ? implode(', ', $orderParts) : 'u.id DESC';
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

try {
    $total = (int) db()->query('SELECT COUNT(id) FROM users')->fetchColumn();

    $countStmt = db()->prepare("SELECT COUNT(u.id) FROM users u LEFT JOIN roles r ON r.id = u.role_id {$whereSql}");
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $countStmt->execute();
    $filtered = (int) $countStmt->fetchColumn();

    $stmt = db()->prepare("
        SELECT
            u.id,
            u.name,
            u.email,
            u.avatar,
            u.is_active,
            u.created_at,
            u.updated_at,
            r.name AS role_name,
            r.is_active AS role_is_active
        FROM users u
        LEFT JOIN roles r ON r.id = u.role_id
        {$whereSql}
        ORDER BY {$orderSql}
        LIMIT :length OFFSET :start
    ");
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->execute();

    $data = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $name = (string) ($row['name'] ?? '');
        $avatar = trim((string) ($row['avatar'] ?? ''));
        $data[] = [
            'id' => (int) ($row['id'] ?? 0),
            'row_key' => 'user-' . (int) ($row['id'] ?? 0),
            'name' => $name,
            'initial' => mb_strtoupper(mb_substr($name, 0, 1)),
            'email' => (string) ($row['email'] ?? ''),
            'avatar_url' => $avatar !== '' ? base_url('uploads/avatars/' . ltrim($avatar, '/')) : null,
            'role_name' => $row['role_name'] ?? null,
            'role_is_active' => $row['role_is_active'] === null ? null : (int) $row['role_is_active'] === 1,
            'is_active' => (int) ($row['is_active'] ?? 0) === 1,
            'created_at' => (string) ($row['created_at'] ?? ''),
            'created_at_display' => kirpi_format_datetime((string) ($row['created_at'] ?? '')),
            'updated_at' => (string) ($row['updated_at'] ?? ''),
            'updated_at_display' => kirpi_format_datetime((string) ($row['updated_at'] ?? '')),
        ];
    }

    json_response([
        'draw' => $draw,
        'recordsTotal' => $total,
        'recordsFiltered' => $filtered,
        'data' => $data,
    ]);
} catch (Throwable $e) {
    error_log('users datatable error: ' . $e->getMessage());
    json_response([
        'draw' => $draw,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'Kullanıcı tablosu yüklenemedi.',
    ], 500);
}
