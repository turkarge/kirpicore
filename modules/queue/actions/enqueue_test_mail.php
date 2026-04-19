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

if (!kirpi_queue_table_ready()) {
    json_response([
        'status' => 'error',
        'message' => 'Queue tablosu henuz kurulu degil.',
    ], 422);
}

$recipientEmail = trim((string) ($_POST['recipient_email'] ?? ''));
if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
    json_response([
        'status' => 'error',
        'message' => 'Gecerli bir e-posta adresi girin.',
    ], 422);
}

$currentUser = current_user();
$userId = (int) ($currentUser['id'] ?? 0);

$jobId = kirpi_queue_push('mail.send', [
    'recipient_email' => $recipientEmail,
    'subject' => 'Kirpi Queue Test Mail',
    'body_html' => '<p>Bu e-posta queue uzerinden gonderilmistir.</p>',
    'user_id' => $userId > 0 ? $userId : null,
]);

kirpi_audit_log('enqueue_test_mail', 'queue', [
    'job_id' => $jobId,
    'recipient_email' => $recipientEmail,
], 'queue_job', $jobId, 'success');

json_response([
    'status' => 'success',
    'message' => 'Mail job kuyruga eklendi. Job ID: ' . $jobId,
    'reload_page' => true,
]);
