<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

require_action('GET', false);

api_require_token('users.view');

$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = (int) ($_GET['per_page'] ?? 20);
if ($perPage <= 0) {
    $perPage = 20;
}
if ($perPage > 100) {
    $perPage = 100;
}

$search = trim((string) ($_GET['search'] ?? ''));
$roleId = trim((string) ($_GET['role_id'] ?? ''));
$status = trim((string) ($_GET['status'] ?? ''));

$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(u.name LIKE :search OR u.email LIKE :search OR r.name LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if ($roleId !== '') {
    $where[] = "u.role_id = :role_id";
    $params[':role_id'] = (int) $roleId;
}

if ($status !== '' && in_array($status, ['0', '1'], true)) {
    $where[] = "u.is_active = :is_active";
    $params[':is_active'] = (int) $status;
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

$offset = ($page - 1) * $perPage;

try {
    $countSql = "\n        SELECT COUNT(u.id)\n        FROM users u\n        LEFT JOIN roles r ON r.id = u.role_id\n        {$whereSql}\n    ";

    $countStmt = db()->prepare($countSql);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $total = (int) $countStmt->fetchColumn();
    $totalPages = (int) ceil($total / $perPage);

    $sql = "\n        SELECT\n            u.id,\n            u.name,\n            u.email,\n            u.avatar,\n            u.is_active,\n            u.created_at,\n            u.updated_at,\n            u.role_id,\n            r.name AS role_name\n        FROM users u\n        LEFT JOIN roles r ON r.id = u.role_id\n        {$whereSql}\n        ORDER BY u.id DESC\n        LIMIT :limit OFFSET :offset\n    ";

    $stmt = db()->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $users = array_map(static function (array $row): array {
        return [
            'id' => (int) ($row['id'] ?? 0),
            'name' => (string) ($row['name'] ?? ''),
            'email' => (string) ($row['email'] ?? ''),
            'avatar_url' => !empty($row['avatar']) ? base_url('uploads/avatars/' . ltrim((string) $row['avatar'], '/')) : null,
            'is_active' => (int) ($row['is_active'] ?? 0) === 1,
            'role_id' => isset($row['role_id']) ? (int) $row['role_id'] : null,
            'role_name' => $row['role_name'] ?? null,
            'created_at' => (string) ($row['created_at'] ?? ''),
            'updated_at' => (string) ($row['updated_at'] ?? ''),
        ];
    }, $rows);

    api_response(200, 'OK', [
        'users' => $users,
    ], [
        'page' => $page,
        'per_page' => $perPage,
        'total' => $total,
        'total_pages' => $totalPages,
    ]);
} catch (Throwable $e) {
    error_log('api users list error: ' . $e->getMessage());
    api_error(500, 'Kullanicilar listelenemedi.');
}

