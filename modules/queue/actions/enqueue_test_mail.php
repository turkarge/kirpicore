<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

require_once BASE_PATH . '/modules/queue/language.php';

require_action('POST', true);

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    json_response([
        'status' => 'error',
        'message' => queue_lang('csrf_failed'),
    ], 419);
}

if (!kirpi_queue_table_ready()) {
    json_response([
        'status' => 'error',
        'message' => queue_lang('table_not_ready'),
    ], 422);
}

$recipientEmail = trim((string) ($_POST['recipient_email'] ?? ''));
if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
    json_response([
        'status' => 'error',
        'message' => queue_lang('invalid_email'),
    ], 422);
}

$currentUser = current_user();
$userId = (int) ($currentUser['id'] ?? 0);

$jobId = kirpi_queue_push('mail.send', [
    'recipient_email' => $recipientEmail,
    'subject' => queue_lang('test_subject'),
    'body_html' => queue_lang('test_body_html'),
    'user_id' => $userId > 0 ? $userId : null,
]);

kirpi_audit_log('enqueue_test_mail', 'queue', [
    'job_id' => $jobId,
    'recipient_email' => $recipientEmail,
], 'queue_job', $jobId, 'success');

json_response([
    'status' => 'success',
    'message' => queue_lang('enqueue_success_prefix') . $jobId,
    'reload_page' => true,
]);
