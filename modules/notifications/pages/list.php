<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

$notificationsTableReady = db_table_exists('notifications');
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">İletişim Merkezi</div>
                <h2 class="page-title">Bildirimler</h2>
            </div>

            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <?php if (check_permission('notifications.settings')): ?>
                        <a href="<?php echo base_url('notifications/settings'); ?>" class="btn">
                            Ayarlar
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
                                Tümünü Okundu Yap
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
                Bildirim tabloları henüz kurulu değil. Önce
                <code>modules/notifications/database/schema.sql</code> dosyasını çalıştırın.
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body border-bottom py-3">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-8">
                        <input
                            type="text"
                            id="notifications-search"
                            class="form-control"
                            placeholder="Başlık veya mesaj ara..."
                            <?php echo !$notificationsTableReady ? 'disabled' : ''; ?>
                        >
                    </div>

                    <div class="col-12 col-md-4">
                        <select id="notifications-status-filter" class="form-select" <?php echo !$notificationsTableReady ? 'disabled' : ''; ?>>
                            <option value="">Tüm Durumlar</option>
                            <option value="unread">Okunmadı</option>
                            <option value="read">Okundu</option>
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
                        Bildirim tablosu hazır olduğunda liste burada görünecek.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
