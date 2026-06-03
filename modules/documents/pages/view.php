<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

require_once BASE_PATH . '/modules/documents/language.php';

$tableReady = documents_tables_ready();
$documents = [];

if ($tableReady) {
    try {
        $stmt = db()->query("
            SELECT d.id, d.document_type, d.original_name, d.mime_type, d.file_size, d.created_at, u.name AS uploaded_by_name,
                   COUNT(dl.id) AS link_count
            FROM documents d
            LEFT JOIN users u ON u.id = d.uploaded_by_user_id
            LEFT JOIN document_links dl ON dl.document_id = d.id
            GROUP BY d.id, d.document_type, d.original_name, d.mime_type, d.file_size, d.created_at, u.name
            ORDER BY d.id DESC
            LIMIT 100
        ");
        $documents = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        error_log('documents view list error: ' . $e->getMessage());
        $documents = [];
    }
}
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle"><?php echo e(documents_lang('documents')); ?></div>
                <h2 class="page-title"><?php echo e(documents_lang('page_title')); ?></h2>
                <div class="text-secondary mt-1"><?php echo e(documents_lang('page_hint')); ?></div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <?php if (!$tableReady): ?>
            <div class="alert alert-warning"><?php echo e(documents_lang('tables_missing')); ?></div>
        <?php else: ?>
            <?php if (check_permission('documents.upload')): ?>
                <div class="card mb-4">
                    <form action="<?php echo base_url('documents/actions/upload'); ?>" method="post" enctype="multipart/form-data" data-ajax="true">
                        <div class="card-header">
                            <h3 class="card-title"><?php echo e(documents_lang('upload_document')); ?></h3>
                        </div>
                        <div class="card-body">
                            <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">
                            <div class="row g-3">
                                <div class="col-12 col-lg-3">
                                    <label class="form-label form-required"><?php echo e(documents_lang('document_type')); ?></label>
                                    <input type="text" name="document_type" class="form-control" required value="attachment">
                                    <small class="form-hint"><?php echo e(documents_lang('type_hint')); ?></small>
                                </div>
                                <div class="col-12 col-lg-3">
                                    <label class="form-label"><?php echo e(documents_lang('entity_type')); ?></label>
                                    <input type="text" name="entity_type" class="form-control">
                                    <small class="form-hint"><?php echo e(documents_lang('entity_hint')); ?></small>
                                </div>
                                <div class="col-12 col-lg-2">
                                    <label class="form-label"><?php echo e(documents_lang('entity_id')); ?></label>
                                    <input type="number" name="entity_id" class="form-control" min="1">
                                </div>
                                <div class="col-12 col-lg-4">
                                    <label class="form-label form-required"><?php echo e(documents_lang('file')); ?></label>
                                    <input type="file" name="document_file" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <button type="submit" class="btn btn-primary"><?php echo e(documents_lang('save')); ?></button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo e(documents_lang('document_list')); ?></h3>
                    <div class="card-actions">
                        <div class="btn-list">
                            <a href="<?php echo base_url('documents/actions/export?format=csv'); ?>" class="btn btn-outline-secondary">
                                <i class="ti ti-file-type-csv"></i>
                                <?php echo e(documents_lang('export_csv')); ?>
                            </a>
                            <a href="<?php echo base_url('documents/actions/export?format=xls'); ?>" class="btn btn-outline-secondary">
                                <i class="ti ti-file-spreadsheet"></i>
                                <?php echo e(documents_lang('export_excel')); ?>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-striped">
                        <thead>
                        <tr>
                            <th><?php echo e(documents_lang('document_type')); ?></th>
                            <th><?php echo e(documents_lang('original_name')); ?></th>
                            <th><?php echo e(documents_lang('mime_type')); ?></th>
                            <th><?php echo e(documents_lang('file_size')); ?></th>
                            <th><?php echo e(documents_lang('uploaded_by')); ?></th>
                            <th><?php echo e(documents_lang('created_at')); ?></th>
                            <th><?php echo e(documents_lang('actions')); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($documents)): ?>
                            <tr>
                                <td colspan="7" class="text-secondary"><?php echo e(documents_lang('no_records')); ?></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($documents as $document): ?>
                                <?php $documentId = (int) ($document['id'] ?? 0); ?>
                                <tr>
                                    <td><code><?php echo e((string) ($document['document_type'] ?? '')); ?></code></td>
                                    <td>
                                        <?php echo e((string) ($document['original_name'] ?? '')); ?>
                                        <?php if ((int) ($document['link_count'] ?? 0) > 0): ?>
                                            <span class="badge bg-blue-lt ms-2"><?php echo (int) ($document['link_count'] ?? 0); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><code><?php echo e((string) ($document['mime_type'] ?? '')); ?></code></td>
                                    <td><?php echo e(documents_format_size((int) ($document['file_size'] ?? 0))); ?></td>
                                    <td><?php echo e((string) ($document['uploaded_by_name'] ?? '-')); ?></td>
                                    <td><?php echo e(format_datetime((string) ($document['created_at'] ?? ''))); ?></td>
                                    <td>
                                        <div class="btn-list flex-nowrap">
                                            <a href="<?php echo base_url('documents/actions/download/' . $documentId); ?>" class="btn btn-sm btn-outline-primary">
                                                <?php echo e(documents_lang('download')); ?>
                                            </a>
                                            <?php if (check_permission('documents.manage')): ?>
                                                <form action="<?php echo base_url('documents/actions/delete'); ?>" method="post" data-ajax="true" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">
                                                    <input type="hidden" name="id" value="<?php echo $documentId; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="<?php echo e(documents_lang('delete')); ?>?">
                                                        <?php echo e(documents_lang('delete')); ?>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
