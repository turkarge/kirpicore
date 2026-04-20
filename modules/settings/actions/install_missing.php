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

try {
    $result = kirpi_install_missing_database_schema();
    $beforeMissing = (int) ($result['before']['missing_table_count'] ?? 0);
    $afterMissing = (int) ($result['after']['missing_table_count'] ?? 0);
    $beforeMissingIndexes = (int) ($result['indexes']['before']['missing_index_count'] ?? 0);
    $afterMissingIndexes = (int) ($result['indexes']['after']['missing_index_count'] ?? 0);

    kirpi_audit_log('install_missing_schema', 'settings', [
        'before_missing_table_count' => $beforeMissing,
        'after_missing_table_count' => $afterMissing,
        'before_missing_index_count' => $beforeMissingIndexes,
        'after_missing_index_count' => $afterMissingIndexes,
        'installed_files' => $result['installed_files'] ?? [],
        'installed_indexes' => $result['indexes']['installed_indexes'] ?? [],
    ], 'schema', null, 'success');

    if ($beforeMissing <= 0 && $beforeMissingIndexes <= 0) {
        json_response([
            'status' => 'success',
            'message' => 'Eksik tablo veya indeks yok. Sistem zaten tam.',
            'reload_page' => true,
        ]);
    }

    if ($afterMissing <= 0 && $afterMissingIndexes <= 0) {
        json_response([
            'status' => 'success',
            'message' => 'Eksik tablo ve indeksler basariyla kuruldu.',
            'reload_page' => true,
        ]);
    }

    json_response([
        'status' => 'warning',
        'message' => 'Kurulum denendi ancak halen eksikler var. Loglari kontrol edin.',
        'reload_page' => true,
    ]);
} catch (Throwable $e) {
    error_log('install missing schema action error: ' . $e->getMessage());

    kirpi_audit_log('install_missing_schema', 'settings', [
        'error' => $e->getMessage(),
    ], 'schema', null, 'failed');

    json_response([
        'status' => 'error',
        'message' => 'Eksik tablolar kurulurken bir hata olustu.',
    ], 500);
}
