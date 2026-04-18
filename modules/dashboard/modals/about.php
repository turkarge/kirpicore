<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}
?>

<div class="modal-header">
    <h5 class="modal-title">Kirpi Core Hakkında</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body">
    <div class="d-flex flex-column gap-3">
        <div>
            <div class="text-secondary small">Uygulama</div>
            <div class="fw-bold"><?php echo e(app_name()); ?></div>
        </div>

        <div>
            <div class="text-secondary small">Ortam</div>
            <div class="fw-bold"><?php echo e(APP_ENV); ?></div>
        </div>

        <div>
            <div class="text-secondary small">Debug</div>
            <div class="fw-bold"><?php echo APP_DEBUG ? 'Açık' : 'Kapalı'; ?></div>
        </div>

        <div>
            <div class="text-secondary small">Açıklama</div>
            <div>
                Kirpi Core; modüler, hızlı geliştirilebilir ve tekrar kullanılabilir
                PHP uygulamaları üretmek için hazırlanmış çekirdek uygulama yapısıdır.
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
</div>