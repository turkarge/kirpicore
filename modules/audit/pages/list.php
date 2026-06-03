<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

require_once BASE_PATH . '/modules/audit/language.php';

$auditTableReady = db_table_exists('audit_logs');
?>

<script>
window.KIRPI_AUDIT_I18N = {
    loadError: <?php echo json_encode(audit_lang('load_error')); ?>
};
</script>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle"><?php echo e(audit_lang('system_management')); ?></div>
                <h2 class="page-title"><?php echo e(audit_lang('audit_log')); ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <?php if (!$auditTableReady): ?>
            <div class="alert alert-warning">
                <?php echo e(audit_lang('table_missing')); ?>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title"><?php echo e(audit_lang('filters')); ?></h3>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-2">
                        <label class="form-label"><?php echo e(audit_lang('status')); ?></label>
                        <select id="audit-status-filter" class="form-select" <?php echo !$auditTableReady ? 'disabled' : ''; ?>>
                            <option value=""><?php echo e(audit_lang('all')); ?></option>
                            <option value="success">success</option>
                            <option value="failed">failed</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label"><?php echo e(audit_lang('module')); ?></label>
                        <input id="audit-module-filter" type="text" class="form-control" <?php echo !$auditTableReady ? 'disabled' : ''; ?>>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label"><?php echo e(audit_lang('action')); ?></label>
                        <input id="audit-action-filter" type="text" class="form-control" <?php echo !$auditTableReady ? 'disabled' : ''; ?>>
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label"><?php echo e(audit_lang('user_id')); ?></label>
                        <input id="audit-user-filter" type="number" min="1" class="form-control" <?php echo !$auditTableReady ? 'disabled' : ''; ?>>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><?php echo e(audit_lang('records')); ?></h3>
                <?php if ($auditTableReady): ?>
                    <div class="card-actions">
                        <div class="btn-list">
                            <button type="button" class="btn btn-outline-secondary js-audit-export" data-format="csv">
                                <i class="ti ti-file-type-csv"></i>
                                <?php echo e(audit_lang('csv_export')); ?>
                            </button>
                            <button type="button" class="btn btn-outline-secondary js-audit-export" data-format="xls">
                                <i class="ti ti-file-spreadsheet"></i>
                                <?php echo e(audit_lang('excel_export')); ?>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div id="audit-table-container">
                <?php if ($auditTableReady): ?>
                    <div class="kirpi-loading">
                        <div class="spinner-border" role="status"></div>
                    </div>
                <?php else: ?>
                    <div class="p-4 text-secondary">
                        <?php echo e(audit_lang('table_waiting')); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
