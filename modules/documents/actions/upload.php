<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

require_once BASE_PATH . '/modules/documents/language.php';

require_action('POST', true);

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    json_response(['status' => 'error', 'message' => documents_lang('csrf_failed')], 419);
}

$documentType = documents_sanitize_key((string) ($_POST['document_type'] ?? 'attachment'));
$entityType = documents_sanitize_key((string) ($_POST['entity_type'] ?? ''), '');
$entityId = (int) ($_POST['entity_id'] ?? 0);

$result = document_store_upload($_FILES['document_file'] ?? [], $documentType);

if (!($result['success'] ?? false) || !empty($result['skipped'])) {
    json_response([
        'status' => 'error',
        'message' => (string) ($result['message'] ?? documents_lang('upload_error')),
    ], 422);
}

$documentId = (int) ($result['document_id'] ?? 0);
if ($entityType !== '' && $entityId > 0) {
    document_link_existing($documentId, $entityType, $entityId, $documentType);
}

kirpi_audit_log('document_upload', 'documents', [
    'document_id' => $documentId,
    'document_type' => $documentType,
    'entity_type' => $entityType !== '' ? $entityType : null,
    'entity_id' => $entityId > 0 ? $entityId : null,
], 'document', $documentId, 'success');

json_response([
    'status' => 'success',
    'message' => documents_lang('upload_success'),
    'reload_page' => true,
]);
