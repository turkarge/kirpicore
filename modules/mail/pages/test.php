<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

$configStatus = kirpi_mail_config_status();
$currentUser = current_user();
$defaultRecipient = (string) ($currentUser['email'] ?? '');

$mailLogs = [];
if (db_table_exists('mail_logs')) {
    try {
        $stmt = db()->query("\n            SELECT id, recipient_email, subject, transport, status, error_message, created_at\n            FROM mail_logs\n            ORDER BY id DESC\n            LIMIT 20\n        ");
        $mailLogs = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        error_log('mail test page logs error: ' . $e->getMessage());
    }
}
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Mail Merkezi</div>
                <h2 class="page-title">Mail Test ve Durum</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row g-4">
            <div class="col-12 col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Mail Konfigurasyonu</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table table-striped">
                            <thead>
                            <tr>
                                <th>Kontrol</th>
                                <th>Durum</th>
                            </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Transport</td>
                                    <td><code><?php echo e($configStatus['transport']); ?></code></td>
                                </tr>
                                <tr>
                                    <td>MAIL_HOST</td>
                                    <td><?php echo $configStatus['mail_host'] ? '<span class="badge bg-green-lt">Tanimli</span>' : '<span class="badge bg-red-lt">Bos</span>'; ?></td>
                                </tr>
                                <tr>
                                    <td>MAIL_PORT</td>
                                    <td><?php echo $configStatus['mail_port'] ? '<span class="badge bg-green-lt">Tanimli</span>' : '<span class="badge bg-red-lt">Gecersiz</span>'; ?></td>
                                </tr>
                                <tr>
                                    <td>MAIL_USERNAME</td>
                                    <td><?php echo $configStatus['mail_username'] ? '<span class="badge bg-green-lt">Tanimli</span>' : '<span class="badge bg-orange-lt">Bos</span>'; ?></td>
                                </tr>
                                <tr>
                                    <td>MAIL_PASSWORD</td>
                                    <td><?php echo $configStatus['mail_password'] ? '<span class="badge bg-green-lt">Tanimli</span>' : '<span class="badge bg-orange-lt">Bos</span>'; ?></td>
                                </tr>
                                <tr>
                                    <td>MAIL_FROM_ADDRESS</td>
                                    <td><?php echo $configStatus['mail_from_address'] ? '<span class="badge bg-green-lt">Tanimli</span>' : '<span class="badge bg-red-lt">Bos</span>'; ?></td>
                                </tr>
                                <tr>
                                    <td>MAIL_ENCRYPTION</td>
                                    <td><?php echo $configStatus['mail_encryption'] ? '<span class="badge bg-green-lt">Gecerli</span>' : '<span class="badge bg-red-lt">Gecersiz</span>'; ?></td>
                                </tr>
                                <tr>
                                    <td>Hazirlik</td>
                                    <td><?php echo $configStatus['ready'] ? '<span class="badge bg-green">Hazir</span>' : '<span class="badge bg-red">Eksik</span>'; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-7">
                <div class="card">
                    <form action="<?php echo base_url('mail/actions/send-test'); ?>" method="post" data-ajax="true">
                        <div class="card-header">
                            <h3 class="card-title">Test E-posta Gonder</h3>
                        </div>
                        <div class="card-body">
                            <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">

                            <div class="mb-3">
                                <label class="form-label">Alici E-posta</label>
                                <input type="email" name="recipient_email" class="form-control" required value="<?php echo e($defaultRecipient); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Konu</label>
                                <input type="text" name="subject" class="form-control" required value="Kirpi Core Test Maili">
                            </div>

                            <div>
                                <label class="form-label">Mesaj</label>
                                <textarea name="message" rows="6" class="form-control" required>Bu mesaj Kirpi Core mail modulu testi icin gonderilmistir.</textarea>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <button type="submit" class="btn btn-primary" <?php echo !$configStatus['ready'] ? 'disabled' : ''; ?>>Test Maili Gonder</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">Son Mail Loglari</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-striped">
                    <thead>
                    <tr>
                        <th>Tarih</th>
                        <th>Alici</th>
                        <th>Konu</th>
                        <th>Transport</th>
                        <th>Durum</th>
                        <th>Hata</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($mailLogs)): ?>
                            <tr>
                                <td colspan="6" class="text-secondary">Henuz mail logu yok.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($mailLogs as $log): ?>
                                <tr>
                                    <td><?php echo e((string) ($log['created_at'] ?? '')); ?></td>
                                    <td><?php echo e((string) ($log['recipient_email'] ?? '')); ?></td>
                                    <td><?php echo e((string) ($log['subject'] ?? '')); ?></td>
                                    <td><code><?php echo e((string) ($log['transport'] ?? '')); ?></code></td>
                                    <td>
                                        <?php if (($log['status'] ?? '') === 'sent'): ?>
                                            <span class="badge bg-green-lt">Gonderildi</span>
                                        <?php else: ?>
                                            <span class="badge bg-red-lt">Basarisiz</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-secondary"><?php echo e((string) ($log['error_message'] ?? '')); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
