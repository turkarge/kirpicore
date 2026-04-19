<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

$statusFilter = trim((string) ($_GET['status'] ?? ''));
$moduleFilter = trim((string) ($_GET['module'] ?? ''));
$actionFilter = trim((string) ($_GET['action'] ?? ''));
$userIdFilter = (int) ($_GET['user_id'] ?? 0);

$where = [];
$params = [];

if ($statusFilter !== '') {
    $where[] = 'a.status = :status';
    $params[':status'] = $statusFilter;
}

if ($moduleFilter !== '') {
    $where[] = 'a.module_key = :module_key';
    $params[':module_key'] = $moduleFilter;
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

if (db_table_exists('audit_logs')) {
    try {
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
            LIMIT 200
        ";

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        error_log('audit list page error: ' . $e->getMessage());
    }
}
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Sistem Yonetimi</div>
                <h2 class="page-title">Audit Log</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <?php if (!db_table_exists('audit_logs')): ?>
            <div class="alert alert-warning">
                Audit log tablosu kurulu degil. Kurulum icin setup veya db:install calistirin.
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Filtreler</h3>
            </div>
            <div class="card-body">
                <form method="get" action="<?php echo base_url('audit/list'); ?>">
                    <div class="row g-3">
                        <div class="col-12 col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">Tum</option>
                                <option value="success" <?php echo $statusFilter === 'success' ? 'selected' : ''; ?>>success</option>
                                <option value="failed" <?php echo $statusFilter === 'failed' ? 'selected' : ''; ?>>failed</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Module</label>
                            <input type="text" name="module" class="form-control" value="<?php echo e($moduleFilter); ?>">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Action</label>
                            <input type="text" name="action" class="form-control" value="<?php echo e($actionFilter); ?>">
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label">User ID</label>
                            <input type="number" min="1" name="user_id" class="form-control" value="<?php echo $userIdFilter > 0 ? e((string) $userIdFilter) : ''; ?>">
                        </div>
                        <div class="col-12 col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Filtrele</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Son 200 Kayit</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tarih</th>
                            <th>Kullanici</th>
                            <th>Module</th>
                            <th>Action</th>
                            <th>Status</th>
                            <th>Route</th>
                            <th>IP</th>
                            <th>Detay</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="9" class="text-secondary">Kayit bulunamadi.</td>
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
                                            <span class="badge bg-red-lt"><?php echo e((string) ($log['status'] ?? 'failed')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div><code><?php echo e((string) ($log['route_path'] ?? '')); ?></code></div>
                                        <div class="small text-secondary"><?php echo e((string) ($log['request_method'] ?? '')); ?></div>
                                    </td>
                                    <td><code><?php echo e((string) ($log['ip_address'] ?? '')); ?></code></td>
                                    <td>
                                        <details>
                                            <summary>Gor</summary>
                                            <pre class="mb-0"><?php echo e((string) ($log['details_json'] ?? '')); ?></pre>
                                        </details>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
