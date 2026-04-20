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
$tokenId = (int) ($_POST['token_id'] ?? 0);

if ($userId <= 0 || $roleName !== 'Super Admin') {
    set_flash_message('danger', 'Sadece Super Admin API token yonetebilir.');
    redirect(base_url('profile/view'));
}

if ($tokenId <= 0) {
    set_flash_message('warning', 'Gecersiz token kaydi.');
    redirect(base_url('profile/view'));
}

if (!api_token_table_ready()) {
    set_flash_message('warning', 'API token tablosu hazir degil.');
    redirect(base_url('profile/view'));
}

try {
    $ok = api_revoke_token_for_user($tokenId, $userId);
    if (!$ok) {
        set_flash_message('warning', 'Token bulunamadi veya zaten iptal edilmis.');
        redirect(base_url('profile/view'));
    }

    kirpi_audit_log('revoke_token', 'api', [
        'token_id' => $tokenId,
    ], 'api_token', $tokenId, 'success');

    set_flash_message('success', 'API token iptal edildi.');
    redirect(base_url('profile/view'));
} catch (Throwable $e) {
    error_log('profile revoke api token error: ' . $e->getMessage());

    kirpi_audit_log('revoke_token', 'api', [
        'token_id' => $tokenId,
        'error' => $e->getMessage(),
    ], 'api_token', $tokenId, 'failed');

    set_flash_message('danger', 'Token iptal edilirken bir hata olustu.');
    redirect(base_url('profile/view'));
}

