<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Sistem Yönetimi</div>
                <h2 class="page-title">Kullanıcılar</h2>
            </div>

            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a
                        href="#"
                        class="btn btn-primary btn-modal-trigger"
                        data-url="/ajax/users/create"
                        data-size="modal-lg"
                    >
                        <i class="ti ti-plus"></i>
                        Yeni Kullanıcı
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
                            placeholder="Ad, e-posta veya rol ara..."
                        >
                    </div>

                    <div class="col-6 col-md-3">
                        <select id="users-role-filter" class="form-select">
                            <option value="">Tüm Roller</option>
                            <?php
                            try {
                                $roles = get_roles_for_select();
                                foreach ($roles as $role) {
                                    $label = $role['name'];

                                    if (isset($role['is_active']) && (int)$role['is_active'] !== 1) {
                                        $label .= ' (Pasif)';
                                    }

                                    echo '<option value="' . (int)$role['id'] . '">' . e($label) . '</option>';
                                }
                            } catch (Throwable $e) {
                                // sessiz geç
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <select id="users-status-filter" class="form-select">
                            <option value="">Tüm Durumlar</option>
                            <option value="1">Aktif</option>
                            <option value="0">Pasif</option>
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
