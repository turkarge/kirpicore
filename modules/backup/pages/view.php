<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

$backupReady = kirpi_backup_table_ready();
$backups = [];
$restores = [];

if ($backupReady) {
    try {
        $backupsStmt = db()->query("\n            SELECT b.id, b.label, b.file_name, b.file_size, b.status, b.created_at, u.name AS created_by_name\n            FROM db_backups b\n            LEFT JOIN users u ON u.id = b.created_by\n            ORDER BY b.id DESC\n            LIMIT 50\n        ");
        $backups = $backupsStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        if (db_table_exists('db_backup_restores')) {
            $restoresStmt = db()->query("\n                SELECT r.id, r.backup_id, r.created_at, u.name AS restored_by_name\n                FROM db_backup_restores r\n                LEFT JOIN users u ON u.id = r.restored_by\n                ORDER BY r.id DESC\n                LIMIT 20\n            ");
            $restores = $restoresStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
    } catch (Throwable $e) {
        error_log('backup view page error: ' . $e->getMessage());
    }
}
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Sistem Yonetimi</div>
                <h2 class="page-title">Backup / Restore</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <?php if (!$backupReady): ?>
            <div class="alert alert-warning">
                Backup tablolari kurulu degil. Kurulum icin setup veya db:install calistirin.
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Yeni Backup</h3>
            </div>
            <form action="<?php echo base_url('backup/actions/create'); ?>" method="post" data-ajax="true">
                <div class="card-body">
                    <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">
                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <label class="form-label">Etiket</label>
                            <input type="text" name="label" class="form-control" placeholder="ornek: deploy_oncesi" <?php echo !$backupReady ? 'disabled' : ''; ?>>
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100" <?php echo !$backupReady ? 'disabled' : ''; ?>>Backup Olustur</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Son Backup Kayitlari</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Etiket</th>
                            <th>Dosya</th>
                            <th>Boyut</th>
                            <th>Durum</th>
                            <th>Tarih</th>
                            <th>Olusturan</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($backups)): ?>
                            <tr><td colspan="8" class="text-secondary text-center py-4">Kayit bulunamadi.</td></tr>
                        <?php else: ?>
                            <?php foreach ($backups as $backup): ?>
                                <tr>
                                    <td><?php echo (int) ($backup['id'] ?? 0); ?></td>
                                    <td><?php echo e((string) ($backup['label'] ?? '')); ?></td>
                                    <td><code><?php echo e((string) ($backup['file_name'] ?? '')); ?></code></td>
                                    <td><?php echo number_format(((int) ($backup['file_size'] ?? 0)) / 1024, 2); ?> KB</td>
                                    <td><span class="badge bg-blue-lt"><?php echo e((string) ($backup['status'] ?? '')); ?></span></td>
                                    <td><?php echo e((string) ($backup['created_at'] ?? '')); ?></td>
                                    <td><?php echo e((string) ($backup['created_by_name'] ?? '-')); ?></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="<?php echo base_url('backup/actions/download?id=' . (int) ($backup['id'] ?? 0)); ?>" class="btn btn-sm btn-outline-primary">Indir</a>

                                            <form id="backup-verify-form-<?php echo (int) ($backup['id'] ?? 0); ?>" action="<?php echo base_url('backup/actions/verify'); ?>" method="post" data-ajax="true" class="m-0">
                                                <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">
                                                <input type="hidden" name="backup_id" value="<?php echo (int) ($backup['id'] ?? 0); ?>">
                                            </form>
                                            <a href="#" class="btn btn-sm btn-outline-warning" data-confirm="Bu backup dosyasi checksum ve dry-run restore ile dogrulanacak. Emin misiniz?" data-form="backup-verify-form-<?php echo (int) ($backup['id'] ?? 0); ?>">Dogrula</a>

                                            <form id="backup-restore-form-<?php echo (int) ($backup['id'] ?? 0); ?>" action="<?php echo base_url('backup/actions/restore'); ?>" method="post" data-ajax="true" class="m-0">
                                                <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">
                                                <input type="hidden" name="backup_id" value="<?php echo (int) ($backup['id'] ?? 0); ?>">
                                            </form>
                                            <a href="#" class="btn btn-sm btn-outline-danger" data-confirm="Bu backup geri yuklenecek. Emin misiniz?" data-form="backup-restore-form-<?php echo (int) ($backup['id'] ?? 0); ?>">Restore</a>

                                            <form id="backup-delete-form-<?php echo (int) ($backup['id'] ?? 0); ?>" action="<?php echo base_url('backup/actions/delete'); ?>" method="post" data-ajax="true" class="m-0">
                                                <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">
                                                <input type="hidden" name="backup_id" value="<?php echo (int) ($backup['id'] ?? 0); ?>">
                                            </form>
                                            <a href="#" class="btn btn-sm btn-outline-secondary" data-confirm="Bu backup kaydi silinecek. Emin misiniz?" data-form="backup-delete-form-<?php echo (int) ($backup['id'] ?? 0); ?>">Sil</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Son Restore Loglari</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Backup ID</th>
                            <th>Restore Eden</th>
                            <th>Tarih</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($restores)): ?>
                            <tr><td colspan="4" class="text-secondary text-center py-4">Kayit bulunamadi.</td></tr>
                        <?php else: ?>
                            <?php foreach ($restores as $restore): ?>
                                <tr>
                                    <td><?php echo (int) ($restore['id'] ?? 0); ?></td>
                                    <td><?php echo (int) ($restore['backup_id'] ?? 0); ?></td>
                                    <td><?php echo e((string) ($restore['restored_by_name'] ?? '-')); ?></td>
                                    <td><?php echo e((string) ($restore['created_at'] ?? '')); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
