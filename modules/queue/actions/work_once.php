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

$result = kirpi_queue_work_once('default');
$status = (string) ($result['status'] ?? 'error');

if ($status === 'failed') {
    kirpi_audit_log('work_once', 'queue', $result, 'queue_job', isset($result['job_id']) ? (int) $result['job_id'] : null, 'failed');

    json_response([
        'status' => 'error',
        'message' => (string) ($result['message'] ?? 'Queue job failed.'),
        'reload_page' => true,
    ], 422);
}

if ($status === 'processed') {
    kirpi_audit_log('work_once', 'queue', $result, 'queue_job', (int) ($result['job_id'] ?? 0), 'success');

    json_response([
        'status' => 'success',
        'message' => 'Queue job calistirildi. Job ID: ' . (int) ($result['job_id'] ?? 0),
        'reload_page' => true,
    ]);
}

json_response([
    'status' => 'info',
    'message' => (string) ($result['message'] ?? 'Queue idle.'),
    'reload_page' => true,
]);
