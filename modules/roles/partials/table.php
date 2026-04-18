<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$search = trim((string) ($_GET['search'] ?? ''));
$status = trim((string) ($_GET['status'] ?? ''));

$limit = 10;
$offset = ($page - 1) * $limit;
$hasPermissionSchema = db_table_exists('permissions') && db_table_exists('role_permissions');

$where = [];
$params = [];

if ($search !== '') {
    $where[] = 'r.name LIKE :search';
    $params[':search'] = '%' . $search . '%';
}

if ($status !== '' && in_array($status, ['0', '1'], true)) {
    $where[] = 'r.is_active = :is_active';
    $params[':is_active'] = (int) $status;
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

try {
    $countSql = "
        SELECT COUNT(r.id)
        FROM roles r
        {$whereSql}
    ";

    $countStmt = db()->prepare($countSql);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();

    $totalRecords = (int) $countStmt->fetchColumn();
    $totalPages = (int) ceil($totalRecords / $limit);

    $sql = "
        SELECT
            r.id,
            r.name,
            r.is_active,
            COUNT(DISTINCT u.id) AS user_count," . ($hasPermissionSchema
                ? "
            COUNT(DISTINCT rp.permission_id) AS permission_count"
                : "
            0 AS permission_count") . "
        FROM roles r
        LEFT JOIN users u ON u.role_id = r.id
        " . ($hasPermissionSchema ? "LEFT JOIN role_permissions rp ON rp.role_id = r.id" : "") . "
        {$whereSql}
        GROUP BY r.id, r.name, r.is_active
        ORDER BY r.name ASC
        LIMIT :limit OFFSET :offset
    ";

    $stmt = db()->prepare($sql);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    error_log('roles table error: ' . $e->getMessage());
    $roles = [];
    $totalPages = 0;
}
?>

<div class="table-responsive">
    <table class="table table-vcenter card-table table-striped">
        <thead>
            <tr>
                <th>Rol</th>
                <th>Durum</th>
                <th>Kullanıcı Sayısı</th>
                <th>İzin Sayısı</th>
                <th class="w-1"></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($roles)): ?>
                <tr>
                    <td colspan="5" class="text-center text-secondary py-4">
                        Kayıt bulunamadı.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($roles as $role): ?>
                    <tr>
                        <td>
                            <div class="fw-bold"><?php echo e($role['name']); ?></div>
                            <div class="text-secondary">ID: <?php echo (int) $role['id']; ?></div>
                        </td>
                        <td>
                            <form
                                action="<?php echo base_url('roles/actions/toggle-status'); ?>"
                                method="post"
                                class="m-0 roles-toggle-status-form"
                                data-ajax="true"
                            >
                                <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">
                                <input type="hidden" name="id" value="<?php echo (int) $role['id']; ?>">
                                <input type="hidden" name="status" value="<?php echo (int) $role['is_active']; ?>">

                                <label class="form-check form-switch m-0 d-inline-block">
                                    <input
                                        class="form-check-input roles-status-switch"
                                        type="checkbox"
                                        <?php echo (int) $role['is_active'] === 1 ? 'checked' : ''; ?>
                                    >
                                </label>
                            </form>
                        </td>
                        <td>
                            <?php echo (int) $role['user_count']; ?>
                        </td>
                        <td>
                            <?php echo (int) ($role['permission_count'] ?? 0); ?>
                        </td>
                        <td>
                            <div class="btn-list flex-nowrap">
                                <?php if (check_permission('roles.permissions')): ?>
                                    <a
                                        href="<?php echo base_url('roles/permissions?id=' . (int) $role['id']); ?>"
                                        class="btn btn-sm btn-outline-secondary"
                                    >
                                        İzinler
                                    </a>
                                <?php endif; ?>

                                <a
                                    href="#"
                                    class="btn btn-sm btn-outline-primary btn-modal-trigger"
                                    data-url="/ajax/roles/edit?id=<?php echo (int) $role['id']; ?>"
                                    data-size="modal-md"
                                >
                                    Düzenle
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPages > 1): ?>
    <div class="card-footer d-flex align-items-center">
        <?php render_pagination($page, $totalPages); ?>
    </div>
<?php endif; ?>
