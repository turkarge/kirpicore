<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

require_once BASE_PATH . '/modules/users/language.php';

$page = max(1, (int) ($_GET['page'] ?? 1));
$search = trim((string) ($_GET['search'] ?? ''));
$roleId = trim((string) ($_GET['role_id'] ?? ''));
$status = trim((string) ($_GET['status'] ?? ''));

$limit = 10;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(u.name LIKE :search_name OR u.email LIKE :search_email OR r.name LIKE :search_role)";
    $searchPattern = '%' . $search . '%';
    $params[':search_name'] = $searchPattern;
    $params[':search_email'] = $searchPattern;
    $params[':search_role'] = $searchPattern;
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
$canDropSession = check_permission('users.session.drop');
$canResetLockKey = check_permission('users.lock.reset');
$canEditUser = check_permission('users.edit');

try {
    $countSql = "
        SELECT COUNT(u.id)
        FROM users u
        LEFT JOIN roles r ON r.id = u.role_id
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
            u.id,
            u.name,
            u.email,
            u.avatar,
            u.is_active,
            u.created_at,
            r.name AS role_name,
            r.is_active AS role_is_active
        FROM users u
        LEFT JOIN roles r ON r.id = u.role_id
        {$whereSql}
        ORDER BY u.id DESC
        LIMIT :limit OFFSET :offset
    ";

    $stmt = db()->prepare($sql);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $users = [];
    $totalPages = 0;
    $totalRecords = 0;
}
?>

<div class="table-responsive">
    <table class="table table-vcenter card-table table-striped">
        <thead>
            <tr>
                <th><?php echo e(users_lang('table_user')); ?></th>
                <th><?php echo e(users_lang('table_role')); ?></th>
                <th><?php echo e(users_lang('table_status')); ?></th>
                <th><?php echo e(users_lang('table_created_at')); ?></th>
                <th class="w-1"></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="5" class="text-center text-secondary py-4">
                        <?php echo e(users_lang('no_records')); ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <?php
                    $avatar = $user['avatar']
                        ? base_url('uploads/avatars/' . ltrim($user['avatar'], '/'))
                        : null;

                    $initial = mb_strtoupper(mb_substr($user['name'], 0, 1));
                    ?>
                    <tr>
                        <td>
                            <div class="d-flex py-1 align-items-center">
                                <?php if ($avatar): ?>
                                    <span class="avatar me-2" style="background-image: url('<?php echo e($avatar); ?>')"></span>
                                <?php else: ?>
                                    <span class="avatar me-2"><?php echo e($initial); ?></span>
                                <?php endif; ?>

                                <div class="flex-fill">
                                    <div class="font-weight-medium"><?php echo e($user['name']); ?></div>
                                    <div class="text-secondary"><?php echo e($user['email']); ?></div>
                                </div>
                            </div>
                        </td>

                        <td>
                            <?php
                            $roleLabel = $user['role_name'] ?: '-';

                            if ($user['role_name'] && isset($user['role_is_active']) && (int) $user['role_is_active'] !== 1) {
                                $roleLabel .= users_lang('status_inactive_suffix');
                            }
                            ?>
                            <?php echo e($roleLabel); ?>
                        </td>

                        <td>
                            <form
                                action="<?php echo base_url('users/actions/toggle-status'); ?>"
                                method="post"
                                class="m-0 users-toggle-status-form"
                                data-ajax="true"
                            >
                                <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">
                                <input type="hidden" name="id" value="<?php echo (int) $user['id']; ?>">
                                <input type="hidden" name="status" value="<?php echo (int) $user['is_active']; ?>">

                                <label class="form-check form-switch m-0 d-inline-block">
                                    <input
                                        class="form-check-input users-status-switch"
                                        type="checkbox"
                                        <?php echo (int) $user['is_active'] === 1 ? 'checked' : ''; ?>
                                    >
                                </label>
                            </form>
                        </td>

                        <td>
                            <?php echo e(kirpi_format_datetime((string) ($user['created_at'] ?? ''))); ?>
                        </td>

                        <td>
                            <div class="btn-list flex-nowrap">
                                <?php if ($canEditUser): ?>
                                    <a
                                        href="#"
                                        class="btn btn-sm btn-outline-primary btn-modal-trigger"
                                        data-url="/ajax/users/edit?id=<?php echo (int) $user['id']; ?>"
                                        data-size="modal-lg"
                                    >
                                        <?php echo e(users_lang('edit')); ?>
                                    </a>
                                <?php endif; ?>

                                <?php if ($canDropSession): ?>
                                    <form id="users-drop-session-list-form-<?php echo (int) $user['id']; ?>" action="<?php echo base_url('users/actions/drop-session'); ?>" method="post" data-ajax="true" class="m-0">
                                        <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">
                                        <input type="hidden" name="id" value="<?php echo (int) $user['id']; ?>">
                                        <button type="button" class="btn btn-sm btn-outline-warning" data-confirm="<?php echo e(users_lang('drop_session_confirm')); ?>" data-form="users-drop-session-list-form-<?php echo (int) $user['id']; ?>">
                                            <?php echo e(users_lang('session')); ?>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($canResetLockKey): ?>
                                    <form id="users-reset-lock-list-form-<?php echo (int) $user['id']; ?>" action="<?php echo base_url('users/actions/reset-lock-key'); ?>" method="post" data-ajax="true" class="m-0">
                                        <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">
                                        <input type="hidden" name="id" value="<?php echo (int) $user['id']; ?>">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-confirm="<?php echo e(users_lang('reset_key_list_confirm')); ?>" data-form="users-reset-lock-list-form-<?php echo (int) $user['id']; ?>">
                                            <?php echo e(users_lang('key')); ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
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
