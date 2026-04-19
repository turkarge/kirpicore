<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

require_action('POST', true);

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    json_response([
        'status' => 'error',
        'message' => 'Guvenlik dogrulamasi basarisiz oldu.',
    ], 419);
}

if (!kirpi_settings_table_ready()) {
    json_response([
        'status' => 'error',
        'message' => 'Ayarlar tablosu henuz kurulu degil.',
    ], 422);
}

$appName = trim((string) ($_POST['app_name'] ?? ''));
$mailHost = trim((string) ($_POST['mail_host'] ?? ''));
$mailPort = trim((string) ($_POST['mail_port'] ?? ''));
$mailUsername = trim((string) ($_POST['mail_username'] ?? ''));
$mailPassword = (string) ($_POST['mail_password'] ?? '');
$mailEncryption = strtolower(trim((string) ($_POST['mail_encryption'] ?? 'tls')));
$mailFromAddress = trim((string) ($_POST['mail_from_address'] ?? ''));
$mailFromName = trim((string) ($_POST['mail_from_name'] ?? ''));
$apiEnabled = isset($_POST['api_enabled']) ? '1' : '0';

if ($appName === '') {
    json_response([
        'status' => 'error',
        'message' => 'Uygulama adi bos olamaz.',
    ], 422);
}

if ($mailPort !== '' && (!ctype_digit($mailPort) || (int) $mailPort <= 0)) {
    json_response([
        'status' => 'error',
        'message' => 'MAIL_PORT sayisal ve pozitif olmalidir.',
    ], 422);
}

if (!in_array($mailEncryption, ['tls', 'ssl', 'none'], true)) {
    json_response([
        'status' => 'error',
        'message' => 'MAIL_ENCRYPTION gecersiz.',
    ], 422);
}

if ($mailFromAddress !== '' && !filter_var($mailFromAddress, FILTER_VALIDATE_EMAIL)) {
    json_response([
        'status' => 'error',
        'message' => 'MAIL_FROM_ADDRESS gecersiz bir e-posta adresi.',
    ], 422);
}

$currentUser = current_user();
$updatedBy = (int) ($currentUser['id'] ?? 0);
$updatedBy = $updatedBy > 0 ? $updatedBy : null;

try {
    $changes = [];

    kirpi_setting_set('app.name', $appName, $updatedBy);
    $changes[] = 'app.name';

    kirpi_setting_set('api.enabled', $apiEnabled, $updatedBy);
    $changes[] = 'api.enabled';

    kirpi_setting_set('mail.host', $mailHost, $updatedBy);
    $changes[] = 'mail.host';

    kirpi_setting_set('mail.port', $mailPort !== '' ? $mailPort : '587', $updatedBy);
    $changes[] = 'mail.port';

    kirpi_setting_set('mail.username', $mailUsername, $updatedBy);
    $changes[] = 'mail.username';

    if (trim($mailPassword) !== '') {
        kirpi_setting_set('mail.password', $mailPassword, $updatedBy, true);
        $changes[] = 'mail.password';
    }

    kirpi_setting_set('mail.encryption', $mailEncryption, $updatedBy);
    $changes[] = 'mail.encryption';

    kirpi_setting_set('mail.from_address', $mailFromAddress, $updatedBy);
    $changes[] = 'mail.from_address';

    kirpi_setting_set('mail.from_name', $mailFromName, $updatedBy);
    $changes[] = 'mail.from_name';

    kirpi_audit_log('update', 'settings', [
        'changed_keys' => $changes,
    ], 'settings', null, 'success');

    json_response([
        'status' => 'success',
        'message' => 'Ayarlar basariyla guncellendi.',
        'reload_page' => true,
    ]);
} catch (Throwable $e) {
    error_log('settings update error: ' . $e->getMessage());

    json_response([
        'status' => 'error',
        'message' => 'Ayarlar guncellenirken bir hata olustu.',
    ], 500);
}
