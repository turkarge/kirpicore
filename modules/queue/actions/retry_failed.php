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

$stmt = db()->prepare("\n    UPDATE jobs_queue\n    SET status = 'queued',\n        last_error = NULL,\n        available_at = NOW(),\n        reserved_at = NULL,\n        finished_at = NULL,\n        updated_at = NOW()\n    WHERE status = 'failed'\n");
$stmt->execute();
$affected = (int) $stmt->rowCount();

kirpi_audit_log('retry_failed', 'queue', [
    'affected_rows' => $affected,
], 'queue_job', null, 'success');

json_response([
    'status' => 'success',
    'message' => 'Retry icin guncellenen failed job sayisi: ' . $affected,
    'reload_page' => true,
]);
