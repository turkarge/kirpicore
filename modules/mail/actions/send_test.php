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

$recipientEmail = trim((string) ($_POST['recipient_email'] ?? ''));
$subject = trim((string) ($_POST['subject'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));

if ($recipientEmail === '' || $subject === '' || $message === '') {
    json_response([
        'status' => 'error',
        'message' => 'Alici e-posta, konu ve mesaj zorunludur.',
    ], 422);
}

$currentUser = current_user();
$userId = (int) ($currentUser['id'] ?? 0);

$htmlBody = nl2br(e($message));
$sendResult = kirpi_send_mail($recipientEmail, $subject, $htmlBody, $userId > 0 ? $userId : null);

if (!($sendResult['success'] ?? false)) {
    json_response([
        'status' => 'error',
        'message' => (string) ($sendResult['message'] ?? 'Test maili gonderilemedi.'),
    ], 422);
}

json_response([
    'status' => 'success',
    'message' => (string) ($sendResult['message'] ?? 'Test maili gonderildi.'),
    'reload_page' => true,
]);
