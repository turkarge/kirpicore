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
$discovery = kirpi_ai_discover_schema([
    'include_sensitive' => false,
    'limit' => 10,
]);
$discoveryEntities = (array) ($discovery['entities'] ?? []);
$discoveryMeta = (array) ($discovery['meta'] ?? []);
$searchQuery = trim((string) ($_GET['q'] ?? ''));
$searchResult = $searchQuery !== ''
    ? kirpi_ai_search_schema($searchQuery, ['limit' => 10])
    : ['status' => 'success', 'results' => [], 'meta' => ['result_count' => 0]];
$searchResults = (array) ($searchResult['results'] ?? []);

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
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle"><?php echo e(ai_lang('system_management')); ?></div>
                <h2 class="page-title"><?php echo e(ai_lang('kirpi_intelligence')); ?></h2>
                <div class="text-secondary mt-1"><?php echo e(ai_lang('subtitle')); ?></div>
            </div>
            <?php if (check_permission('ai.schema.manage') || check_permission('ai.audit.view')): ?>
                <div class="col-auto ms-auto d-print-none d-flex gap-2">
                    <?php if (check_permission('ai.audit.view')): ?>
                        <a href="<?php echo base_url('ai/audit'); ?>" class="btn btn-outline-secondary">
                            <?php echo e(ai_lang('view_audit')); ?>
                        </a>
                    <?php endif; ?>
                    <?php if (check_permission('ai.schema.manage')): ?>
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
                    <?php echo e(ai_lang('sensitive_hidden')); ?>
                </div>
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
                    <div class="text-secondary small mt-1"><?php echo e(ai_lang('metadata_search_detail')); ?></div>
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
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($searchResults)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-4">
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
                                ?>
                                <tr>
                                    <td><?php echo (int) ($result['score'] ?? 0); ?></td>
                                    <td><code><?php echo e((string) ($result['module'] ?? '')); ?></code></td>
                                    <td><?php echo e((string) ($result['entity'] ?? '')); ?></td>
                                    <td><code><?php echo e((string) ($result['table'] ?? '')); ?></code></td>
                                    <td><?php echo e(implode(', ', $matchedFields)); ?></td>
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
