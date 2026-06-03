<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

require_once BASE_PATH . '/modules/notifications/language.php';

$notificationsTableReady = db_table_exists('notifications');
$hasMetadataColumns = $notificationsTableReady
    && db_column_exists('notifications', 'source_module')
    && db_column_exists('notifications', 'template_key');
$sourceModules = [];
$templateKeys = [];

if ($hasMetadataColumns) {
    try {
        $sourceModules = db()->query("
            SELECT DISTINCT source_module
            FROM notifications
            WHERE source_module IS NOT NULL AND source_module <> ''
            ORDER BY source_module ASC
        ")->fetchAll(PDO::FETCH_COLUMN) ?: [];

        $templateKeys = db()->query("
            SELECT DISTINCT template_key
            FROM notifications
            WHERE template_key IS NOT NULL AND template_key <> ''
            ORDER BY template_key ASC
        ")->fetchAll(PDO::FETCH_COLUMN) ?: [];
    } catch (Throwable $e) {
        error_log('notifications metadata filters error: ' . $e->getMessage());
        $sourceModules = [];
        $templateKeys = [];
    }
}
?>

<script>
window.KIRPI_NOTIFICATIONS_I18N = {
    listLoadError: <?php echo json_encode(notifications_lang('list_load_error')); ?>
};
</script>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle"><?php echo e(notifications_lang('communication_center')); ?></div>
                <h2 class="page-title"><?php echo e(notifications_lang('notifications')); ?></h2>
            </div>

            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <?php if (check_permission('notifications.settings')): ?>
                        <a href="<?php echo base_url('notifications/settings'); ?>" class="btn">
                            <?php echo e(notifications_lang('settings')); ?>
                        </a>
                    <?php endif; ?>

                    <?php if ($notificationsTableReady): ?>
                        <form
                            id="notifications-mark-all-read-form"
                            action="<?php echo base_url('notifications/actions/mark-all-read'); ?>"
                            method="post"
                            data-ajax="true"
                            class="m-0"
                        >
                            <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">
                            <button type="submit" class="btn btn-primary">
                                <?php echo e(notifications_lang('mark_all_read')); ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <?php if (!$notificationsTableReady): ?>
            <div class="alert alert-warning">
                <?php echo e(notifications_lang('tables_missing')); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body border-bottom py-3">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-lg-5">
                        <input
                            type="text"
                            id="notifications-search"
                            class="form-control"
                            placeholder="<?php echo e(notifications_lang('search_placeholder')); ?>"
                            <?php echo !$notificationsTableReady ? 'disabled' : ''; ?>
                        >
                    </div>

                    <div class="col-12 col-sm-4 col-lg-2">
                        <select id="notifications-status-filter" class="form-select" <?php echo !$notificationsTableReady ? 'disabled' : ''; ?>>
                            <option value=""><?php echo e(notifications_lang('all_statuses')); ?></option>
                            <option value="unread"><?php echo e(notifications_lang('status_unread')); ?></option>
                            <option value="read"><?php echo e(notifications_lang('status_read')); ?></option>
                        </select>
                    </div>

                    <div class="col-12 col-sm-4 col-lg-2">
                        <select id="notifications-source-filter" class="form-select" <?php echo !$hasMetadataColumns ? 'disabled' : ''; ?>>
                            <option value=""><?php echo e(notifications_lang('all_sources')); ?></option>
                            <?php foreach ($sourceModules as $sourceModule): ?>
                                <option value="<?php echo e((string) $sourceModule); ?>"><?php echo e((string) $sourceModule); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-sm-4 col-lg-3">
                        <select id="notifications-template-filter" class="form-select" <?php echo !$hasMetadataColumns ? 'disabled' : ''; ?>>
                            <option value=""><?php echo e(notifications_lang('all_templates')); ?></option>
                            <?php foreach ($templateKeys as $key): ?>
                                <option value="<?php echo e((string) $key); ?>"><?php echo e((string) $key); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div id="notifications-table-container">
                <?php if ($notificationsTableReady): ?>
                    <div class="kirpi-loading">
                        <div class="spinner-border" role="status"></div>
                    </div>
                <?php else: ?>
                    <div class="p-4 text-secondary">
                        <?php echo e(notifications_lang('table_waiting')); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
