<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

$auditTableReady = db_table_exists('audit_logs');
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
        <?php if (!$auditTableReady): ?>
            <div class="alert alert-warning">
                Audit log tablosu kurulu degil. Kurulum icin setup veya db:install calistirin.
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Filtreler</h3>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-2">
                        <label class="form-label">Status</label>
                        <select id="audit-status-filter" class="form-select" <?php echo !$auditTableReady ? 'disabled' : ''; ?>>
                            <option value="">Tum</option>
                            <option value="success">success</option>
                            <option value="failed">failed</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Module</label>
                        <input id="audit-module-filter" type="text" class="form-control" <?php echo !$auditTableReady ? 'disabled' : ''; ?>>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Action</label>
                        <input id="audit-action-filter" type="text" class="form-control" <?php echo !$auditTableReady ? 'disabled' : ''; ?>>
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label">User ID</label>
                        <input id="audit-user-filter" type="number" min="1" class="form-control" <?php echo !$auditTableReady ? 'disabled' : ''; ?>>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Audit Kayitlari</h3>
            </div>
            <div id="audit-table-container">
                <?php if ($auditTableReady): ?>
                    <div class="kirpi-loading">
                        <div class="spinner-border" role="status"></div>
                    </div>
                <?php else: ?>
                    <div class="p-4 text-secondary">
                        Audit tablosu hazir oldugunda liste burada gorunecek.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
