<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

require_once BASE_PATH . '/modules/notifications/language.php';

if (!db_table_exists('notifications')) {
    ?>
    <div class="p-4">
        <div class="alert alert-warning mb-0">
            <?php echo e(notifications_lang('table_missing_short')); ?>
        </div>
    </div>
    <?php
    exit;
}

$currentUser = current_user();
$userId = (int) ($currentUser['id'] ?? 0);
$page = max(1, (int) ($_GET['page'] ?? 1));
$search = trim((string) ($_GET['search'] ?? ''));
$status = trim((string) ($_GET['status'] ?? ''));

$limit = 10;
$offset = ($page - 1) * $limit;

$where = ['n.user_id = :user_id'];
$params = [
    ':user_id' => $userId,
];

if ($search !== '') {
    $where[] = '(n.title LIKE :search OR n.message LIKE :search)';
    $params[':search'] = '%' . $search . '%';
}

if ($status === 'unread') {
    $where[] = 'n.read_at IS NULL';
}

if ($status === 'read') {
    $where[] = 'n.read_at IS NOT NULL';
}

$whereSql = 'WHERE ' . implode(' AND ', $where);

try {
    $countSql = "
        SELECT COUNT(n.id)
        FROM notifications n
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
            n.id,
            n.title,
            n.message,
            n.channel,
            n.created_at,
            n.read_at
        FROM notifications n
        {$whereSql}
        ORDER BY n.id DESC
        LIMIT :limit OFFSET :offset
    ";

    $stmt = db()->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    error_log('notifications table error: ' . $e->getMessage());
    $notifications = [];
    $totalPages = 0;
}
?>

<div class="table-responsive">
    <table class="table table-vcenter card-table table-striped">
        <thead>
            <tr>
                <th><?php echo e(notifications_lang('table_notification')); ?></th>
                <th><?php echo e(notifications_lang('table_channel')); ?></th>
                <th><?php echo e(notifications_lang('table_status')); ?></th>
                <th><?php echo e(notifications_lang('table_date')); ?></th>
                <th class="w-1"></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($notifications)): ?>
                <tr>
                    <td colspan="5" class="text-center text-secondary py-4">
                        <?php echo e(notifications_lang('no_records')); ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($notifications as $notification): ?>
                    <tr class="<?php echo empty($notification['read_at']) ? 'table-warning' : ''; ?>">
                        <td>
                            <div class="fw-bold"><?php echo e($notification['title']); ?></div>
                            <div class="text-secondary"><?php echo e($notification['message']); ?></div>
                        </td>
                        <td>
                            <?php echo e($notification['channel'] ?: notifications_lang('default_channel')); ?>
                        </td>
                        <td>
                            <?php if (!empty($notification['read_at'])): ?>
                                <span class="badge bg-green-lt"><?php echo e(notifications_lang('status_read')); ?></span>
                            <?php else: ?>
                                <span class="badge bg-yellow-lt"><?php echo e(notifications_lang('status_unread')); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo e(kirpi_format_datetime((string) ($notification['created_at'] ?? ''))); ?>
                        </td>
                        <td>
                            <?php if (empty($notification['read_at'])): ?>
                                <form
                                    action="<?php echo base_url('notifications/actions/mark-read'); ?>"
                                    method="post"
                                    class="m-0 notifications-mark-read-form"
                                    data-ajax="true"
                                >
                                    <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">
                                    <input type="hidden" name="id" value="<?php echo (int) $notification['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        <?php echo e(notifications_lang('mark_read')); ?>
                                    </button>
                                </form>
                            <?php endif; ?>
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
