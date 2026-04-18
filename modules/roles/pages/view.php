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
                <h2 class="page-title">Roller</h2>
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
                        Yeni Rol
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
                            placeholder="Rol adı ara..."
                        >
                    </div>

                    <div class="col-12 col-md-3">
                        <select id="roles-status-filter" class="form-select">
                            <option value="">Tüm Durumlar</option>
                            <option value="1">Aktif</option>
                            <option value="0">Pasif</option>
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
