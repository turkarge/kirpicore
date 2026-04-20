<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

require_action('POST', true);

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    set_flash_message('danger', 'Guvenlik dogrulamasi basarisiz oldu.');
    redirect(base_url('profile/view'));
}

$currentUser = current_user();
$userId = (int) ($currentUser['id'] ?? 0);
$roleName = (string) ($currentUser['role_name'] ?? '');

if ($userId <= 0 || $roleName !== 'Super Admin') {
    set_flash_message('danger', 'Sadece Super Admin API token olusturabilir.');
    redirect(base_url('profile/view'));
}

if (!api_is_enabled()) {
    set_flash_message('warning', 'API devre disi oldugu icin token olusturulamadi.');
    redirect(base_url('profile/view'));
}

if (!api_token_table_ready()) {
    set_flash_message('warning', 'API token tablosu hazir degil. Once Eksikleri Kur calistirin.');
    redirect(base_url('profile/view'));
}

$tokenName = trim((string) ($_POST['token_name'] ?? 'profile-token'));
$tokenName = $tokenName !== '' ? $tokenName : 'profile-token';
$ttlOption = trim((string) ($_POST['ttl_option'] ?? '1_month'));

$ttlMap = [
    '24h' => 24 * 60 * 60,
    '1_month' => 30 * 24 * 60 * 60,
    '3_months' => 90 * 24 * 60 * 60,
    '6_months' => 180 * 24 * 60 * 60,
    '1_year' => 365 * 24 * 60 * 60,
    'unlimited' => -1,
];

$ttlSeconds = $ttlMap[$ttlOption] ?? $ttlMap['1_month'];

try {
    $issued = api_issue_token_for_user($userId, $tokenName, $ttlSeconds);
    if (!$issued) {
        set_flash_message('danger', 'API token olusturulamadi.');
        redirect(base_url('profile/view'));
    }

    $_SESSION['profile_api_token_once'] = [
        'token_id' => (int) ($issued['token_id'] ?? 0),
        'token' => (string) ($issued['token'] ?? ''),
        'expires_at' => (string) ($issued['expires_at'] ?? ''),
        'token_name' => $tokenName,
        'ttl_option' => $ttlOption,
        'is_unlimited' => (bool) ($issued['is_unlimited'] ?? false),
    ];

    $newTokenId = (int) ($issued['token_id'] ?? 0);
    if ($newTokenId > 0 && !empty($issued['token'])) {
        if (!isset($_SESSION['profile_api_token_copy_map']) || !is_array($_SESSION['profile_api_token_copy_map'])) {
            $_SESSION['profile_api_token_copy_map'] = [];
        }

        $_SESSION['profile_api_token_copy_map'][(string) $newTokenId] = (string) $issued['token'];
    }

    kirpi_audit_log('create_token', 'api', [
        'token_id' => (int) ($issued['token_id'] ?? 0),
        'token_name' => $tokenName,
        'expires_at' => (string) ($issued['expires_at'] ?? ''),
        'ttl_option' => $ttlOption,
    ], 'api_token', null, 'success');

    set_flash_message('success', 'API token olusturuldu. Profil sayfasinda bir kez gosterilecek.');
    redirect(base_url('profile/view'));
} catch (Throwable $e) {
    error_log('profile create api token error: ' . $e->getMessage());

    kirpi_audit_log('create_token', 'api', [
        'token_name' => $tokenName,
        'error' => $e->getMessage(),
    ], 'api_token', null, 'failed');

    set_flash_message('danger', 'API token olusturulurken bir hata olustu.');
    redirect(base_url('profile/view'));
}
