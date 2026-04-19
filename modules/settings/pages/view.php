<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

$tableReady = kirpi_settings_table_ready();

$appName = trim((string) kirpi_setting_get('app.name', APP_NAME));
$mailHost = trim((string) kirpi_setting_get('mail.host', MAIL_HOST));
$mailPort = trim((string) kirpi_setting_get('mail.port', (string) MAIL_PORT));
$mailUsername = trim((string) kirpi_setting_get('mail.username', MAIL_USERNAME));
$mailEncryption = trim((string) kirpi_setting_get('mail.encryption', MAIL_ENCRYPTION));
$mailFromAddress = trim((string) kirpi_setting_get('mail.from_address', MAIL_FROM_ADDRESS));
$mailFromName = trim((string) kirpi_setting_get('mail.from_name', MAIL_FROM_NAME));

$mailPasswordStored = trim((string) kirpi_setting_get('mail.password', '')) !== '' || trim((string) MAIL_PASSWORD) !== '';
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Sistem Yonetimi</div>
                <h2 class="page-title">Ayarlar</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <?php if (!$tableReady): ?>
            <div class="alert alert-warning">
                Ayarlar tablosu kurulu degil. Kurulum icin setup veya db:install calistirin.
            </div>
        <?php endif; ?>

        <div class="card">
            <form id="settings-update-form" action="<?php echo base_url('settings/actions/update'); ?>" method="post" data-ajax="true">
                <div class="card-body">
                    <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">

                    <h3 class="mb-3">Uygulama</h3>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Uygulama Adi</label>
                            <input type="text" name="app_name" class="form-control" value="<?php echo e($appName); ?>" <?php echo !$tableReady ? 'disabled' : ''; ?>>
                        </div>
                    </div>

                    <h3 class="mb-3">Mail</h3>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">MAIL_HOST</label>
                            <input type="text" name="mail_host" class="form-control" value="<?php echo e($mailHost); ?>" <?php echo !$tableReady ? 'disabled' : ''; ?>>
                        </div>

                        <div class="col-12 col-md-2">
                            <label class="form-label">MAIL_PORT</label>
                            <input type="number" min="1" name="mail_port" class="form-control" value="<?php echo e($mailPort); ?>" <?php echo !$tableReady ? 'disabled' : ''; ?>>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">MAIL_ENCRYPTION</label>
                            <select name="mail_encryption" class="form-select" <?php echo !$tableReady ? 'disabled' : ''; ?>>
                                <option value="tls" <?php echo strtolower($mailEncryption) === 'tls' ? 'selected' : ''; ?>>tls</option>
                                <option value="ssl" <?php echo strtolower($mailEncryption) === 'ssl' ? 'selected' : ''; ?>>ssl</option>
                                <option value="none" <?php echo strtolower($mailEncryption) === 'none' ? 'selected' : ''; ?>>none</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">MAIL_USERNAME</label>
                            <input type="text" name="mail_username" class="form-control" value="<?php echo e($mailUsername); ?>" <?php echo !$tableReady ? 'disabled' : ''; ?>>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">MAIL_PASSWORD</label>
                            <input type="password" name="mail_password" class="form-control" value="" placeholder="Degistirmek icin yeni sifre girin" <?php echo !$tableReady ? 'disabled' : ''; ?>>
                            <div class="form-hint">
                                <?php echo $mailPasswordStored ? 'Parola tanimli (guvenlik icin gosterilmiyor).' : 'Parola tanimli degil.'; ?>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">MAIL_FROM_ADDRESS</label>
                            <input type="email" name="mail_from_address" class="form-control" value="<?php echo e($mailFromAddress); ?>" <?php echo !$tableReady ? 'disabled' : ''; ?>>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">MAIL_FROM_NAME</label>
                            <input type="text" name="mail_from_name" class="form-control" value="<?php echo e($mailFromName); ?>" <?php echo !$tableReady ? 'disabled' : ''; ?>>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary" <?php echo !$tableReady ? 'disabled' : ''; ?>>Ayarlari Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>
