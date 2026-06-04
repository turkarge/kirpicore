<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

require_once BASE_PATH . '/modules/users/language.php';
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle"><?php echo e(users_lang('system_management')); ?></div>
                <h2 class="page-title"><?php echo e(users_lang('users')); ?></h2>
            </div>

            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="<?php echo base_url('users/actions/export?format=csv'); ?>" class="btn btn-outline-secondary js-users-export" data-format="csv">
                        <i class="ti ti-file-type-csv"></i>
                        <?php echo e(users_lang('csv_export')); ?>
                    </a>
                    <a href="<?php echo base_url('users/actions/export?format=xls'); ?>" class="btn btn-outline-secondary js-users-export" data-format="xls">
                        <i class="ti ti-file-spreadsheet"></i>
                        <?php echo e(users_lang('excel_export')); ?>
                    </a>
                    <a
                        href="#"
                        class="btn btn-primary btn-modal-trigger"
                        data-url="/ajax/users/create"
                        data-size="modal-lg"
                    >
                        <i class="ti ti-plus"></i>
                        <?php echo e(users_lang('new_user')); ?>
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
                    <div class="col-12 col-md-6">
                        <input
                            type="text"
                            id="users-search"
                            class="form-control"
                            placeholder="<?php echo e(users_lang('search_placeholder')); ?>"
                        >
                    </div>

                    <div class="col-6 col-md-3">
                        <select id="users-role-filter" class="form-select">
                            <option value=""><?php echo e(users_lang('all_roles')); ?></option>
                            <?php
                            try {
                                $roles = get_roles_for_select();
                                foreach ($roles as $role) {
                                    $label = $role['name'];

                                    if (isset($role['is_active']) && (int) $role['is_active'] !== 1) {
                                        $label .= users_lang('status_inactive_suffix');
                                    }

                                    echo '<option value="' . (int) $role['id'] . '">' . e($label) . '</option>';
                                }
                            } catch (Throwable $e) {
                                // sessiz gec
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <select id="users-status-filter" class="form-select">
                            <option value=""><?php echo e(users_lang('all_statuses')); ?></option>
                            <option value="1"><?php echo e(users_lang('active')); ?></option>
                            <option value="0"><?php echo e(users_lang('inactive')); ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div id="users-table-container">
                <div class="kirpi-loading">
                    <div class="spinner-border" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>
