<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

require_once BASE_PATH . '/modules/audit/language.php';

if (!db_table_exists('audit_logs')) {
    ?>
    <div class="p-4">
        <div class="alert alert-warning mb-0">
            <?php echo e(audit_lang('table_missing_short')); ?>
        </div>
    </div>
    <?php
    exit;
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$statusFilter = trim((string) ($_GET['status'] ?? ''));
$moduleFilter = trim((string) ($_GET['module'] ?? ''));
$actionFilter = trim((string) ($_GET['action'] ?? ''));
$userIdFilter = (int) ($_GET['user_id'] ?? 0);

$limit = 20;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];

if ($statusFilter !== '') {
    $where[] = 'a.status = :status';
    $params[':status'] = $statusFilter;
}

if ($moduleFilter !== '') {
    $where[] = 'a.module_key LIKE :module_key';
    $params[':module_key'] = '%' . $moduleFilter . '%';
}

if ($actionFilter !== '') {
    $where[] = 'a.action_key LIKE :action_key';
    $params[':action_key'] = '%' . $actionFilter . '%';
}

if ($userIdFilter > 0) {
    $where[] = 'a.user_id = :user_id';
    $params[':user_id'] = $userIdFilter;
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
$logs = [];
$totalPages = 0;

try {
    $countSql = "
        SELECT COUNT(a.id)
        FROM audit_logs a
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
            a.id,
            a.user_id,
            a.module_key,
            a.action_key,
            a.status,
            a.entity_type,
            a.entity_id,
            a.route_path,
            a.request_method,
            a.ip_address,
            a.details_json,
            a.created_at,
            u.name AS user_name
        FROM audit_logs a
        LEFT JOIN users u ON u.id = a.user_id
        {$whereSql}
        ORDER BY a.id DESC
        LIMIT :limit OFFSET :offset
    ";

    $stmt = db()->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {
    error_log('audit table partial error: ' . $e->getMessage());
    $logs = [];
    $totalPages = 0;
}
?>

<div class="table-responsive">
    <table class="table table-vcenter card-table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th><?php echo e(audit_lang('date')); ?></th>
                <th><?php echo e(audit_lang('user')); ?></th>
                <th><?php echo e(audit_lang('module')); ?></th>
                <th><?php echo e(audit_lang('action')); ?></th>
                <th><?php echo e(audit_lang('status')); ?></th>
                <th><?php echo e(audit_lang('route')); ?></th>
                <th><?php echo e(audit_lang('ip')); ?></th>
                <th><?php echo e(audit_lang('detail')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="9" class="text-secondary py-4 text-center"><?php echo e(audit_lang('no_records')); ?></td>
                </tr>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo (int) ($log['id'] ?? 0); ?></td>
                        <td><?php echo e((string) ($log['created_at'] ?? '')); ?></td>
                        <td>
                            <?php
                            $userName = trim((string) ($log['user_name'] ?? ''));
                            $userId = (int) ($log['user_id'] ?? 0);
                            echo e($userName !== '' ? ($userName . ' (#' . $userId . ')') : '-');
                            ?>
                        </td>
                        <td><code><?php echo e((string) ($log['module_key'] ?? '')); ?></code></td>
                        <td><code><?php echo e((string) ($log['action_key'] ?? '')); ?></code></td>
                        <td>
                            <?php if (($log['status'] ?? '') === 'success'): ?>
                                <span class="badge bg-green-lt">success</span>
                            <?php else: ?>
                                <span class="badge bg-red-lt"><?php echo e((string) ($log['status'] ?? audit_lang('failed'))); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div><code><?php echo e((string) ($log['route_path'] ?? '')); ?></code></div>
                            <div class="small text-secondary"><?php echo e((string) ($log['request_method'] ?? '')); ?></div>
                        </td>
                        <td><code><?php echo e((string) ($log['ip_address'] ?? '')); ?></code></td>
                        <td>
                            <details>
                                <summary><?php echo e(audit_lang('view')); ?></summary>
                                <pre class="mb-0"><?php echo e((string) ($log['details_json'] ?? '')); ?></pre>
                            </details>
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
