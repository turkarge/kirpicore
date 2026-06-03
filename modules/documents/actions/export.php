<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

require_once BASE_PATH . '/modules/documents/language.php';

if (!documents_tables_ready()) {
    http_response_code(404);
    echo documents_lang('tables_missing');
    exit;
}

$format = trim((string) ($_GET['format'] ?? 'csv'));

$stmt = db()->query("
    SELECT
        d.id,
        d.document_type,
        d.original_name,
        d.mime_type,
        d.file_size,
        d.created_at,
        u.name AS uploaded_by_name,
        COUNT(dl.id) AS link_count
    FROM documents d
    LEFT JOIN users u ON u.id = d.uploaded_by_user_id
    LEFT JOIN document_links dl ON dl.document_id = d.id
    GROUP BY d.id, d.document_type, d.original_name, d.mime_type, d.file_size, d.created_at, u.name
    ORDER BY d.id DESC
    LIMIT 5000
");

$rows = [];
while ($document = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $rows[] = [
        (int) ($document['id'] ?? 0),
        (string) ($document['document_type'] ?? ''),
        (string) ($document['original_name'] ?? ''),
        (string) ($document['mime_type'] ?? ''),
        (int) ($document['file_size'] ?? 0),
        documents_format_size((int) ($document['file_size'] ?? 0)),
        (string) ($document['uploaded_by_name'] ?? ''),
        (int) ($document['link_count'] ?? 0),
        kirpi_format_datetime((string) ($document['created_at'] ?? '')),
    ];
}

kirpi_export_response($format, 'documents-' . date('Ymd-His'), [
    'ID',
    documents_lang('document_type'),
    documents_lang('original_name'),
    documents_lang('mime_type'),
    documents_lang('file_size') . ' (bytes)',
    documents_lang('file_size'),
    documents_lang('uploaded_by'),
    'Link',
    documents_lang('created_at'),
], $rows);
