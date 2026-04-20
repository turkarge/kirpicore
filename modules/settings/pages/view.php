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
$apiEnabled = kirpi_setting_bool('api.enabled', env_bool('API_ENABLED', true));

$mailPasswordStored = trim((string) kirpi_setting_get('mail.password', '')) !== '' || trim((string) MAIL_PASSWORD) !== '';
$schemaReport = kirpi_missing_tables_report();
$missingTables = (array) ($schemaReport['missing_tables'] ?? []);
$missingByFile = (array) ($schemaReport['missing_by_file'] ?? []);
$columnReport = kirpi_missing_columns_report();
$missingColumnCount = (int) ($columnReport['missing_column_count'] ?? 0);
$requiredColumnCount = (int) ($columnReport['required_column_count'] ?? 0);
$missingColumnsByTable = (array) ($columnReport['missing_by_table'] ?? []);
$indexReport = kirpi_missing_indexes_report();
$missingIndexCount = (int) ($indexReport['missing_index_count'] ?? 0);
$requiredIndexCount = (int) ($indexReport['required_index_count'] ?? 0);
$missingIndexesByTable = (array) ($indexReport['missing_by_table'] ?? []);
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Sistem Yonetimi</div>
                <h2 class="page-title">Ayarlar</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a
                    href="#"
                    class="btn btn-outline-primary btn-modal-trigger"
                    data-url="/ajax/settings/session"
                    data-size="modal-lg"
                >
                    Session Verileri
                </a>
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

        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Sistem Kontrol ve Eksik Kurulum</h3>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-2">
                        <div class="text-secondary small">Beklenen Toplam Tablo</div>
                        <div class="h3 mb-0"><?php echo (int) ($schemaReport['required_table_count'] ?? 0); ?></div>
                    </div>
                    <div class="col-12 col-md-2">
                        <div class="text-secondary small">Eksik Tablo</div>
                        <div class="h3 mb-0 <?php echo count($missingTables) > 0 ? 'text-red' : 'text-green'; ?>">
                            <?php echo count($missingTables); ?>
                        </div>
                    </div>
                    <div class="col-12 col-md-2">
                        <div class="text-secondary small">Eksik Indeks / Beklenen</div>
                        <div class="h3 mb-0 <?php echo $missingIndexCount > 0 ? 'text-red' : 'text-green'; ?>">
                            <?php echo $missingIndexCount; ?> / <?php echo $requiredIndexCount; ?>
                        </div>
                    </div>
                    <div class="col-12 col-md-2">
                        <div class="text-secondary small">Eksik Kolon / Beklenen</div>
                        <div class="h3 mb-0 <?php echo $missingColumnCount > 0 ? 'text-red' : 'text-green'; ?>">
                            <?php echo $missingColumnCount; ?> / <?php echo $requiredColumnCount; ?>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 d-flex align-items-end">
                        <form
                            action="<?php echo base_url('settings/actions/install-missing'); ?>"
                            method="post"
                            data-ajax="true"
                            class="w-100"
                        >
                            <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">
                            <button type="submit" class="btn btn-outline-primary w-100">
                                Eksikleri Kur
                            </button>
                        </form>
                    </div>
                </div>

                <?php if (!empty($missingByFile)): ?>
                    <hr class="my-4">
                    <div class="text-secondary mb-2">Eksik tablo bulunan schema dosyalari:</div>
                    <ul class="mb-0">
                        <?php foreach ($missingByFile as $item): ?>
                            <li>
                                <code><?php echo e((string) ($item['file'] ?? '')); ?></code>
                                - <?php echo e(implode(', ', array_map('strval', (array) ($item['tables'] ?? [])))); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if (!empty($missingIndexesByTable)): ?>
                    <hr class="my-4">
                    <div class="text-secondary mb-2">Eksik indeksler:</div>
                    <ul class="mb-0">
                        <?php foreach ($missingIndexesByTable as $tableName => $indexes): ?>
                            <?php foreach ((array) $indexes as $index): ?>
                                <li>
                                    <code><?php echo e((string) $tableName); ?></code>
                                    - <code><?php echo e((string) ($index['name'] ?? '')); ?></code>
                                    (<?php echo e(implode(', ', array_map('strval', (array) ($index['columns'] ?? [])))); ?>)
                                </li>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if (!empty($missingColumnsByTable)): ?>
                    <hr class="my-4">
                    <div class="text-secondary mb-2">Eksik kolonlar:</div>
                    <ul class="mb-0">
                        <?php foreach ($missingColumnsByTable as $tableName => $columns): ?>
                            <?php foreach ((array) $columns as $column): ?>
                                <li>
                                    <code><?php echo e((string) $tableName); ?></code>
                                    - <code><?php echo e((string) ($column['name'] ?? '')); ?></code>
                                </li>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

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
                        <div class="col-12 col-md-6">
                            <label class="form-label d-block">API Durumu</label>
                            <label class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="api_enabled" value="1" <?php echo $apiEnabled ? 'checked' : ''; ?> <?php echo !$tableReady ? 'disabled' : ''; ?>>
                                <span class="form-check-label">REST API aktif</span>
                            </label>
                            <div class="form-hint">Kapali oldugunda /api/v1/* endpointleri 503 doner.</div>
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
