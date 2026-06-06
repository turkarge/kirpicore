<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

require_once BASE_PATH . '/modules/ai/language.php';

$schemaReady = kirpi_ai_schema_registry_ready();
$auditReady = kirpi_ai_audit_table_ready();
$modelsReady = kirpi_ai_models_table_ready();
$manifestCount = kirpi_ai_schema_manifest_count();
$entities = kirpi_ai_list_schema_entities(25);
$adapters = kirpi_ai_model_adapters();
$filterOptions = kirpi_ai_schema_filter_options();
$latestSync = kirpi_ai_latest_schema_sync();
$canManageSchema = check_permission('ai.schema.manage');
$qualityReport = $canManageSchema ? kirpi_ai_schema_quality_report(20) : null;
$qualityMeta = is_array($qualityReport) ? (array) ($qualityReport['meta'] ?? []) : [];
$qualityWarnings = is_array($qualityReport) ? (array) ($qualityReport['warnings'] ?? []) : [];
$discoveryFilters = [
    'module' => trim((string) ($_GET['module'] ?? '')),
    'entity' => trim((string) ($_GET['entity'] ?? '')),
    'table' => trim((string) ($_GET['table'] ?? '')),
    'permission' => trim((string) ($_GET['permission'] ?? '')),
    'search' => trim((string) ($_GET['discovery_q'] ?? '')),
    'filterable_only' => (string) ($_GET['filterable_only'] ?? '') === '1',
    'include_sensitive' => $canManageSchema && (string) ($_GET['include_sensitive'] ?? '') === '1',
    'limit' => (int) ($_GET['limit'] ?? 25),
];
$discovery = kirpi_ai_discover_schema([
    'include_sensitive' => $discoveryFilters['include_sensitive'],
    'filterable_only' => $discoveryFilters['filterable_only'],
    'search' => $discoveryFilters['search'],
    'module' => $discoveryFilters['module'],
    'entity' => $discoveryFilters['entity'],
    'table' => $discoveryFilters['table'],
    'permission' => $discoveryFilters['permission'],
    'limit' => $discoveryFilters['limit'] > 0 ? $discoveryFilters['limit'] : 25,
]);
$discoveryEntities = (array) ($discovery['entities'] ?? []);
$discoveryMeta = (array) ($discovery['meta'] ?? []);
$searchQuery = trim((string) ($_GET['q'] ?? ''));
$searchResult = $searchQuery !== ''
    ? kirpi_ai_search_schema($searchQuery, ['limit' => 10])
    : ['status' => 'success', 'results' => [], 'meta' => ['result_count' => 0]];
$searchResults = (array) ($searchResult['results'] ?? []);
$schemaExportParams = [
    'module' => $discoveryFilters['module'],
    'entity' => $discoveryFilters['entity'],
    'table' => $discoveryFilters['table'],
    'permission' => $discoveryFilters['permission'],
    'discovery_q' => $discoveryFilters['search'],
    'filterable_only' => $discoveryFilters['filterable_only'] ? '1' : '',
    'include_sensitive' => $discoveryFilters['include_sensitive'] ? '1' : '',
    'limit' => max(1, min(200, (int) $discoveryFilters['limit'])),
];
$schemaExportParams = array_filter($schemaExportParams, static fn ($value): bool => (string) $value !== '');
$schemaExportUrl = static function (string $format) use ($schemaExportParams): string {
    return base_url('ai/actions/export-schema?' . http_build_query(array_merge($schemaExportParams, [
        'format' => $format,
    ])));
};
$qualityExportUrl = static function (string $format): string {
    return base_url('ai/actions/export-quality?' . http_build_query([
        'format' => $format,
        'limit' => 500,
    ]));
};

$cards = [
    [
        'title' => ai_lang('schema_registry'),
        'detail' => ai_lang('schema_registry_detail'),
        'value' => kirpi_ai_schema_count(),
        'label' => ai_lang('active_entities'),
        'ready' => $schemaReady,
    ],
    [
        'title' => ai_lang('schema_registry'),
        'detail' => ai_lang('read_only_notice'),
        'value' => kirpi_ai_field_count(),
        'label' => ai_lang('active_fields'),
        'ready' => $schemaReady,
    ],
    [
        'title' => ai_lang('metadata_index'),
        'detail' => ai_lang('metadata_index_detail'),
        'value' => kirpi_ai_schema_index_count(),
        'label' => ai_lang('index_records'),
        'ready' => kirpi_ai_schema_index_ready(),
    ],
    [
        'title' => ai_lang('ai_audit_log'),
        'detail' => ai_lang('ai_audit_log_detail'),
        'value' => kirpi_ai_audit_count(),
        'label' => ai_lang('audit_records'),
        'ready' => $auditReady,
    ],
    [
        'title' => ai_lang('model_adapters'),
        'detail' => ai_lang('model_adapters_detail'),
        'value' => count($adapters),
        'label' => ai_lang('adapter_count'),
        'ready' => $modelsReady,
    ],
];

$statusBadge = static function (bool $ready): string {
    return $ready ? 'bg-green-lt' : 'bg-red-lt';
};

$renderSelect = static function (string $name, string $label, array $options, string $selected): void {
    ?>
    <div class="col-12 col-md-6 col-lg-3">
        <label class="form-label"><?php echo e($label); ?></label>
        <select name="<?php echo e($name); ?>" class="form-select">
            <option value=""><?php echo e(ai_lang('all')); ?></option>
            <?php foreach ($options as $option): ?>
                <option value="<?php echo e((string) $option); ?>" <?php echo (string) $option === $selected ? 'selected' : ''; ?>>
                    <?php echo e((string) $option); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php
};
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle"><?php echo e(ai_lang('system_management')); ?></div>
                <h2 class="page-title"><?php echo e(ai_lang('kirpi_intelligence')); ?></h2>
                <div class="text-secondary mt-1"><?php echo e(ai_lang('subtitle')); ?></div>
            </div>
            <?php if ($canManageSchema || check_permission('ai.audit.view')): ?>
                <div class="col-auto ms-auto d-print-none d-flex gap-2">
                    <?php if (check_permission('ai.audit.view')): ?>
                        <a href="<?php echo base_url('ai/audit'); ?>" class="btn btn-outline-secondary">
                            <?php echo e(ai_lang('view_audit')); ?>
                        </a>
                    <?php endif; ?>
                    <?php if ($canManageSchema): ?>
                        <form action="<?php echo base_url('ai/actions/sync-schema'); ?>" method="post" data-ajax="true">
                            <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">
                            <button type="submit" class="btn btn-outline-primary">
                                <?php echo e(ai_lang('sync_schema')); ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <?php if (!$schemaReady): ?>
            <div class="alert alert-warning">
                <?php echo e(ai_lang('schema_missing')); ?>
            </div>
        <?php endif; ?>

        <?php if (!$modelsReady): ?>
            <div class="alert alert-warning">
                <?php echo e(ai_lang('adapter_missing')); ?>
            </div>
        <?php endif; ?>

        <div class="row row-cards">
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader"><?php echo e(ai_lang('schema_manifests')); ?></div>
                        <div class="h1 mb-1 mt-3"><?php echo (int) $manifestCount; ?></div>
                        <div class="text-secondary"><?php echo e(ai_lang('schema_manifests')); ?></div>
                        <div class="text-secondary small mt-3"><?php echo e(ai_lang('schema_registry_detail')); ?></div>
                    </div>
                </div>
            </div>
            <?php foreach ($cards as $card): ?>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="subheader"><?php echo e((string) $card['title']); ?></div>
                                <span class="badge <?php echo $statusBadge((bool) $card['ready']); ?>">
                                    <?php echo e((bool) $card['ready'] ? ai_lang('status_ready') : ai_lang('status_missing')); ?>
                                </span>
                            </div>
                            <div class="h1 mb-1 mt-3"><?php echo (int) $card['value']; ?></div>
                            <div class="text-secondary"><?php echo e((string) $card['label']); ?></div>
                            <div class="text-secondary small mt-3"><?php echo e((string) $card['detail']); ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($latestSync !== null): ?>
            <?php
            $syncDetails = (array) ($latestSync['details'] ?? []);
            $syncFiles = array_slice((array) ($syncDetails['files'] ?? []), 0, 8);
            $syncErrors = (array) ($syncDetails['errors'] ?? []);
            ?>
            <div class="card mt-3">
                <div class="card-header">
                    <div>
                        <h3 class="card-title"><?php echo e(ai_lang('latest_schema_sync')); ?></h3>
                        <div class="text-secondary small mt-1">
                            <?php echo e((string) ($latestSync['created_at'] ?? '-')); ?>
                            &middot;
                            <?php echo e(ai_lang('status')); ?>:
                            <strong><?php echo e((string) ($latestSync['status'] ?? '-')); ?></strong>
                            &middot;
                            <?php echo e(ai_lang('entities')); ?>:
                            <strong><?php echo (int) ($syncDetails['entity_count'] ?? 0); ?></strong>
                            &middot;
                            <?php echo e(ai_lang('fields')); ?>:
                            <strong><?php echo (int) ($syncDetails['field_count'] ?? 0); ?></strong>
                        </div>
                    </div>
                </div>
                <?php if (!empty($syncFiles) || !empty($syncErrors)): ?>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table table-striped mb-0">
                            <thead>
                            <tr>
                                <th><?php echo e(ai_lang('manifest_file')); ?></th>
                                <th><?php echo e(ai_lang('entities')); ?></th>
                                <th><?php echo e(ai_lang('fields')); ?></th>
                                <th><?php echo e(ai_lang('status')); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($syncFiles as $file): ?>
                                <tr>
                                    <td><code><?php echo e((string) ($file['file'] ?? '')); ?></code></td>
                                    <td><?php echo (int) ($file['entities'] ?? 0); ?></td>
                                    <td><?php echo (int) ($file['fields'] ?? 0); ?></td>
                                    <td><span class="badge bg-green-lt"><?php echo e(ai_lang('status_ready')); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php foreach ($syncErrors as $error): ?>
                                <tr>
                                    <td><code><?php echo e((string) ($error['file'] ?? '')); ?></code></td>
                                    <td colspan="2"><?php echo e((string) ($error['entity'] ?? '-')); ?></td>
                                    <td><span class="badge bg-red-lt"><?php echo e((string) ($error['message'] ?? 'error')); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($canManageSchema && is_array($qualityReport)): ?>
            <?php $qualityByModule = array_slice((array) ($qualityMeta['by_module'] ?? []), 0, 8, true); ?>
            <div class="card mt-3">
                <div class="card-header">
                    <div>
                        <h3 class="card-title"><?php echo e(ai_lang('schema_quality')); ?></h3>
                        <div class="text-secondary small mt-1">
                            <?php echo e(ai_lang('quality_warnings')); ?>:
                            <strong><?php echo (int) ($qualityMeta['warning_count'] ?? 0); ?></strong>
                            &middot;
                            <?php echo e(ai_lang('quality_errors')); ?>:
                            <strong><?php echo (int) ($qualityMeta['error_count'] ?? 0); ?></strong>
                        </div>
                    </div>
                    <div class="card-actions">
                        <div class="btn-list">
                            <a href="<?php echo e($qualityExportUrl('json')); ?>" class="btn btn-sm btn-outline-secondary">JSON</a>
                            <a href="<?php echo e($qualityExportUrl('csv')); ?>" class="btn btn-sm btn-outline-secondary">CSV</a>
                            <a href="<?php echo e($qualityExportUrl('xls')); ?>" class="btn btn-sm btn-outline-secondary">XLS</a>
                        </div>
                    </div>
                </div>
                <?php if (!empty($qualityByModule)): ?>
                    <div class="card-body border-bottom">
                        <div class="row g-2">
                            <?php foreach ($qualityByModule as $moduleKey => $moduleQuality): ?>
                                <div class="col-6 col-md-3 col-xl-2">
                                    <div class="border rounded-2 p-2">
                                        <div><code><?php echo e((string) $moduleKey); ?></code></div>
                                        <div class="text-secondary small">
                                            <?php echo (int) ($moduleQuality['warning_count'] ?? 0); ?>
                                            <?php echo e(ai_lang('quality_warnings')); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-striped mb-0">
                        <thead>
                        <tr>
                            <th><?php echo e(ai_lang('severity')); ?></th>
                            <th><?php echo e(ai_lang('quality_code')); ?></th>
                            <th><?php echo e(ai_lang('module')); ?></th>
                            <th><?php echo e(ai_lang('entity')); ?></th>
                            <th><?php echo e(ai_lang('field_name')); ?></th>
                            <th><?php echo e(ai_lang('quality_message')); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($qualityWarnings)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">
                                    <?php echo e(ai_lang('no_quality_warnings')); ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($qualityWarnings as $warning): ?>
                                <tr>
                                    <td>
                                        <span class="badge <?php echo (string) ($warning['severity'] ?? '') === 'error' ? 'bg-red-lt' : 'bg-yellow-lt'; ?>">
                                            <?php echo e((string) ($warning['severity'] ?? 'warning')); ?>
                                        </span>
                                    </td>
                                    <td><code><?php echo e((string) ($warning['code'] ?? '')); ?></code></td>
                                    <td><code><?php echo e((string) ($warning['module'] ?? '')); ?></code></td>
                                    <td><?php echo e((string) ($warning['entity'] ?? '')); ?></td>
                                    <td><?php echo e((string) ($warning['field'] ?? '-')); ?></td>
                                    <td><?php echo e((string) ($warning['message'] ?? '')); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title"><?php echo e(ai_lang('latest_entities')); ?></h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-striped mb-0">
                    <thead>
                    <tr>
                        <th><?php echo e(ai_lang('module')); ?></th>
                        <th><?php echo e(ai_lang('entity')); ?></th>
                        <th><?php echo e(ai_lang('table')); ?></th>
                        <th><?php echo e(ai_lang('fields')); ?></th>
                        <th><?php echo e(ai_lang('permission')); ?></th>
                        <th><?php echo e(ai_lang('updated_at')); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($entities)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">
                                <?php echo e(ai_lang('no_schema')); ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($entities as $entity): ?>
                            <tr>
                                <td><code><?php echo e((string) ($entity['module_key'] ?? '')); ?></code></td>
                                <td><?php echo e((string) ($entity['entity_key'] ?? '')); ?></td>
                                <td><code><?php echo e((string) ($entity['table_name'] ?? '')); ?></code></td>
                                <td><?php echo (int) ($entity['field_count'] ?? 0); ?></td>
                                <td>
                                    <?php if (trim((string) ($entity['permission_slug'] ?? '')) === ''): ?>
                                        <span class="text-secondary">-</span>
                                    <?php else: ?>
                                        <code><?php echo e((string) $entity['permission_slug']); ?></code>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e((string) ($entity['updated_at'] ?? '-')); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><?php echo e(ai_lang('discovery_preview')); ?></h3>
                    <div class="text-secondary small mt-1"><?php echo e(ai_lang('discovery_preview_detail')); ?></div>
                </div>
                <div class="card-actions text-secondary">
                    <?php echo e(ai_lang('visible_entities')); ?>:
                    <strong><?php echo (int) ($discoveryMeta['entity_count'] ?? 0); ?></strong>
                    &middot;
                    <?php echo e(ai_lang('visible_fields')); ?>:
                    <strong><?php echo (int) ($discoveryMeta['field_count'] ?? 0); ?></strong>
                    &middot;
                    <?php echo e(!empty($discoveryMeta['include_sensitive']) ? ai_lang('sensitive_visible') : ai_lang('sensitive_hidden')); ?>
                    <div class="btn-list justify-content-end mt-2">
                        <a href="<?php echo e($schemaExportUrl('json')); ?>" class="btn btn-sm btn-outline-secondary">
                            JSON
                        </a>
                        <a href="<?php echo e($schemaExportUrl('csv')); ?>" class="btn btn-sm btn-outline-secondary">
                            CSV
                        </a>
                        <a href="<?php echo e($schemaExportUrl('xls')); ?>" class="btn btn-sm btn-outline-secondary">
                            XLS
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body border-bottom">
                <form method="get" action="<?php echo base_url('ai/view'); ?>">
                    <input type="hidden" name="q" value="<?php echo e($searchQuery); ?>">
                    <div class="row g-2">
                        <?php $renderSelect('module', ai_lang('module'), (array) ($filterOptions['modules'] ?? []), $discoveryFilters['module']); ?>
                        <?php $renderSelect('entity', ai_lang('entity'), (array) ($filterOptions['entities'] ?? []), $discoveryFilters['entity']); ?>
                        <?php $renderSelect('table', ai_lang('table'), (array) ($filterOptions['tables'] ?? []), $discoveryFilters['table']); ?>
                        <?php $renderSelect('permission', ai_lang('permission'), (array) ($filterOptions['permissions'] ?? []), $discoveryFilters['permission']); ?>
                        <div class="col-12 col-md-6 col-lg-4">
                            <label class="form-label"><?php echo e(ai_lang('search_query')); ?></label>
                            <input
                                type="search"
                                name="discovery_q"
                                class="form-control"
                                value="<?php echo e((string) $discoveryFilters['search']); ?>"
                                placeholder="<?php echo e(ai_lang('search_placeholder')); ?>"
                            >
                        </div>
                        <div class="col-12 col-md-6 col-lg-2">
                            <label class="form-label"><?php echo e(ai_lang('limit')); ?></label>
                            <input
                                type="number"
                                min="1"
                                max="200"
                                name="limit"
                                class="form-control"
                                value="<?php echo e((string) max(1, min(200, (int) $discoveryFilters['limit']))); ?>"
                            >
                        </div>
                        <div class="col-12 col-lg-3 d-flex align-items-end gap-3">
                            <label class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="filterable_only" value="1" <?php echo $discoveryFilters['filterable_only'] ? 'checked' : ''; ?>>
                                <span class="form-check-label"><?php echo e(ai_lang('filterable_only')); ?></span>
                            </label>
                            <?php if ($canManageSchema): ?>
                                <label class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="include_sensitive" value="1" <?php echo $discoveryFilters['include_sensitive'] ? 'checked' : ''; ?>>
                                    <span class="form-check-label"><?php echo e(ai_lang('include_sensitive')); ?></span>
                                </label>
                            <?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-auto d-flex align-items-end gap-2 ms-lg-auto">
                            <a href="<?php echo base_url('ai/view'); ?>" class="btn btn-outline-secondary">
                                <?php echo e(ai_lang('clear_filters')); ?>
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <?php echo e(ai_lang('filter')); ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-striped mb-0">
                    <thead>
                    <tr>
                        <th><?php echo e(ai_lang('module')); ?></th>
                        <th><?php echo e(ai_lang('entity')); ?></th>
                        <th><?php echo e(ai_lang('table')); ?></th>
                        <th><?php echo e(ai_lang('permission')); ?></th>
                        <th><?php echo e(ai_lang('visible_field_list')); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($discoveryEntities)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">
                                <?php echo e(ai_lang('no_discovery_schema')); ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($discoveryEntities as $entity): ?>
                            <?php
                            $fields = array_map(static fn (array $field): string => (string) ($field['name'] ?? ''), (array) ($entity['fields'] ?? []));
                            $fields = array_values(array_filter($fields, static fn (string $field): bool => $field !== ''));
                            ?>
                            <tr>
                                <td><code><?php echo e((string) ($entity['module'] ?? '')); ?></code></td>
                                <td><?php echo e((string) ($entity['entity'] ?? '')); ?></td>
                                <td><code><?php echo e((string) ($entity['table'] ?? '')); ?></code></td>
                                <td>
                                    <?php if (trim((string) ($entity['permission'] ?? '')) === ''): ?>
                                        <span class="text-secondary">-</span>
                                    <?php else: ?>
                                        <code><?php echo e((string) $entity['permission']); ?></code>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e(implode(', ', $fields)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><?php echo e(ai_lang('metadata_search')); ?></h3>
                    <div class="text-secondary small mt-1">
                        <?php echo e(ai_lang('metadata_search_detail')); ?>
                        <?php if ($searchQuery !== ''): ?>
                            &middot;
                            <?php echo e(ai_lang('search_mode')); ?>:
                            <strong><?php echo e((string) ($searchResult['meta']['mode'] ?? '')); ?></strong>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form method="get" action="<?php echo base_url('ai/view'); ?>">
                    <div class="row g-2">
                        <div class="col-12 col-md">
                            <label class="form-label"><?php echo e(ai_lang('search_query')); ?></label>
                            <input
                                type="search"
                                name="q"
                                class="form-control"
                                value="<?php echo e($searchQuery); ?>"
                                placeholder="<?php echo e(ai_lang('search_placeholder')); ?>"
                            >
                        </div>
                        <div class="col-12 col-md-auto d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <?php echo e(ai_lang('search')); ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <?php if ($searchQuery !== ''): ?>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-striped mb-0">
                        <thead>
                        <tr>
                            <th><?php echo e(ai_lang('score')); ?></th>
                            <th><?php echo e(ai_lang('module')); ?></th>
                            <th><?php echo e(ai_lang('entity')); ?></th>
                            <th><?php echo e(ai_lang('table')); ?></th>
                            <th><?php echo e(ai_lang('matched_fields')); ?></th>
                            <th><?php echo e(ai_lang('match_reason')); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($searchResults)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">
                                    <?php echo e(ai_lang('no_search_results')); ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($searchResults as $result): ?>
                                <?php
                                $matchedFields = array_map(
                                    static fn (array $field): string => (string) ($field['name'] ?? ''),
                                    (array) ($result['matched_fields'] ?? [])
                                );
                                $matchedFields = array_values(array_filter($matchedFields, static fn (string $field): bool => $field !== ''));
                                $matchedTerms = array_map('strval', (array) ($result['matched_terms'] ?? []));
                                $matchedSources = array_map(
                                    static function (array $source): string {
                                        $type = (string) ($source['type'] ?? '');
                                        $token = (string) ($source['token'] ?? '');
                                        return trim($type . ':' . $token, ':');
                                    },
                                    array_slice((array) ($result['matched_sources'] ?? []), 0, 3)
                                );
                                $matchedSources = array_values(array_filter($matchedSources, static fn (string $source): bool => $source !== ''));
                                ?>
                                <tr>
                                    <td><?php echo (int) ($result['score'] ?? 0); ?></td>
                                    <td><code><?php echo e((string) ($result['module'] ?? '')); ?></code></td>
                                    <td><?php echo e((string) ($result['entity'] ?? '')); ?></td>
                                    <td><code><?php echo e((string) ($result['table'] ?? '')); ?></code></td>
                                    <td><?php echo e(implode(', ', $matchedFields)); ?></td>
                                    <td>
                                        <?php if (!empty($matchedTerms)): ?>
                                            <div><?php echo e(ai_lang('matched_terms')); ?>: <code><?php echo e(implode(', ', $matchedTerms)); ?></code></div>
                                        <?php endif; ?>
                                        <?php if (!empty($matchedSources)): ?>
                                            <div class="text-secondary small"><?php echo e(implode(' | ', $matchedSources)); ?></div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
