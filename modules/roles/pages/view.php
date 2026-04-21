<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

require_once BASE_PATH . '/modules/roles/language.php';
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle"><?php echo e(roles_lang('system_management')); ?></div>
                <h2 class="page-title"><?php echo e(roles_lang('roles')); ?></h2>
            </div>

            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a
                        href="#"
                        class="btn btn-primary btn-modal-trigger"
                        data-url="/ajax/roles/create"
                        data-size="modal-md"
                    >
                        <i class="ti ti-plus"></i>
                        <?php echo e(roles_lang('new_role')); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-body border-bottom py-3">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-9">
                        <input
                            type="text"
                            id="roles-search"
                            class="form-control"
                            placeholder="<?php echo e(roles_lang('search_placeholder')); ?>"
                        >
                    </div>

                    <div class="col-12 col-md-3">
                        <select id="roles-status-filter" class="form-select">
                            <option value=""><?php echo e(roles_lang('all_statuses')); ?></option>
                            <option value="1"><?php echo e(roles_lang('active')); ?></option>
                            <option value="0"><?php echo e(roles_lang('inactive')); ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div id="roles-table-container">
                <div class="kirpi-loading">
                    <div class="spinner-border" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>
