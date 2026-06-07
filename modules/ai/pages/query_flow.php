<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

require_once BASE_PATH . '/modules/ai/language.php';

$question = trim((string) ($_GET['question'] ?? ''));
$limit = max(1, min(20, (int) ($_GET['limit'] ?? 5)));
$modelAdapter = trim((string) ($_GET['model_adapter'] ?? 'mock-sql-generator'));
$generateCandidate = (string) ($_GET['generate_candidate'] ?? '') === '1';
$plan = $question !== '' ? kirpi_ai_build_query_plan($question, ['limit' => $limit]) : null;
$allowedTables = is_array($plan) ? (array) ($plan['allowed_tables'] ?? []) : [];
$allowedFields = is_array($plan) ? (array) ($plan['allowed_fields'] ?? []) : [];
$candidate = null;
$preview = null;
$guard = null;
$explain = [];

if ($generateCandidate && $question !== '' && !empty($allowedTables)) {
    $candidate = kirpi_ai_generate_sql_candidate($question, [
        'allowed_tables' => $allowedTables,
        'allowed_fields' => $allowedFields,
    ], $modelAdapter);

    $candidateSql = trim((string) ($candidate['candidate_sql'] ?? ''));
    if ($candidateSql !== '') {
        $preview = kirpi_ai_preview_sql($candidateSql, [
            'planner_question' => $question,
            'allowed_tables' => $allowedTables,
            'allowed_fields' => $allowedFields,
            'audit' => true,
        ]);
        $guard = is_array($preview) ? ($preview['guard'] ?? null) : null;
        $explain = is_array($preview) ? (array) ($preview['explain'] ?? []) : [];
    }
}

$adapters = kirpi_ai_model_adapters();
$adapterOptions = [
    'mock-sql-generator' => ai_lang('mock_sql_generator'),
];
foreach ($adapters as $adapter) {
    $key = trim((string) ($adapter['adapter_key'] ?? ''));
    if ($key !== '') {
        $adapterOptions[$key] = $key . ' / ' . (string) ($adapter['model_name'] ?? '');
    }
}

$renderBadges = static function (array $items, string $class = 'bg-secondary-lt'): void {
    foreach ($items as $item) {
        $value = trim((string) $item);
        if ($value === '') {
            continue;
        }
        ?>
        <span class="badge <?php echo e($class); ?> me-1 mb-1"><?php echo e($value); ?></span>
        <?php
    }
};

$stepBadge = static function (bool $done): string {
    return $done ? 'bg-green-lt' : 'bg-secondary-lt';
};
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle"><?php echo e(ai_lang('kirpi_intelligence')); ?></div>
                <h2 class="page-title"><?php echo e(ai_lang('query_flow')); ?></h2>
                <div class="text-secondary mt-1"><?php echo e(ai_lang('query_flow_detail')); ?></div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="<?php echo base_url('ai/view'); ?>" class="btn btn-outline-secondary">
                    <?php echo e(ai_lang('back_to_ai')); ?>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><?php echo e(ai_lang('query_flow')); ?></h3>
            </div>
            <div class="card-body">
                <form method="get" action="<?php echo base_url('ai/query-flow'); ?>">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label"><?php echo e(ai_lang('question')); ?></label>
                            <textarea name="question" rows="3" class="form-control" placeholder="<?php echo e(ai_lang('question_placeholder')); ?>"><?php echo e($question); ?></textarea>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label"><?php echo e(ai_lang('model_adapter')); ?></label>
                            <select name="model_adapter" class="form-select">
                                <?php foreach ($adapterOptions as $key => $label): ?>
                                    <option value="<?php echo e((string) $key); ?>" <?php echo (string) $key === $modelAdapter ? 'selected' : ''; ?>>
                                        <?php echo e((string) $label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 col-lg-2">
                            <label class="form-label"><?php echo e(ai_lang('limit')); ?></label>
                            <input type="number" min="1" max="20" name="limit" class="form-control" value="<?php echo e((string) $limit); ?>">
                        </div>
                        <div class="col-12 col-lg-auto d-flex align-items-end">
                            <div class="btn-list">
                                <button type="submit" class="btn btn-primary"><?php echo e(ai_lang('build_plan')); ?></button>
                                <button type="submit" name="generate_candidate" value="1" class="btn btn-outline-primary">
                                    <?php echo e(ai_lang('run_query_flow')); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row row-cards mt-1">
            <div class="col-md">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader">1. <?php echo e(ai_lang('query_planner')); ?></div>
                        <span class="badge <?php echo $stepBadge(is_array($plan)); ?>"><?php echo e(is_array($plan) ? ai_lang('status_ready') : ai_lang('status_missing')); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader">2. <?php echo e(ai_lang('guard_context')); ?></div>
                        <span class="badge <?php echo $stepBadge(!empty($allowedTables)); ?>"><?php echo (int) count($allowedTables); ?> <?php echo e(ai_lang('allowed_tables')); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader">3. <?php echo e(ai_lang('sql_candidate')); ?></div>
                        <span class="badge <?php echo $stepBadge(is_array($candidate)); ?>"><?php echo e((string) ($candidate['status'] ?? ai_lang('status_missing'))); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader">4. <?php echo e(ai_lang('sql_preview')); ?></div>
                        <span class="badge <?php echo $stepBadge(is_array($preview)); ?>"><?php echo e((string) ($preview['decision'] ?? ai_lang('status_missing'))); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader">5. <?php echo e(ai_lang('explain_gate')); ?></div>
                        <span class="badge <?php echo (($explain['status'] ?? '') === 'success') ? 'bg-green-lt' : 'bg-red-lt'; ?>">
                            <?php echo e((string) ($explain['reason'] ?? ($explain['status'] ?? ai_lang('status_missing')))); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($plan === null): ?>
            <div class="alert alert-info mt-3"><?php echo e(ai_lang('no_question')); ?></div>
        <?php else: ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><?php echo e(ai_lang('guard_context')); ?></h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-4">
                            <div class="text-secondary small mb-1"><?php echo e(ai_lang('allowed_tables')); ?></div>
                            <?php $renderBadges($allowedTables); ?>
                            <?php if (empty($allowedTables)): ?><span class="text-secondary">-</span><?php endif; ?>
                        </div>
                        <div class="col-lg-8">
                            <div class="text-secondary small mb-1"><?php echo e(ai_lang('allowed_fields')); ?></div>
                            <?php if (empty($allowedFields)): ?>
                                <span class="text-secondary">-</span>
                            <?php else: ?>
                                <?php foreach ($allowedFields as $table => $fields): ?>
                                    <div class="mb-2">
                                        <code><?php echo e((string) $table); ?></code>
                                        <span class="text-secondary">:</span>
                                        <?php $renderBadges((array) $fields); ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (is_array($candidate)): ?>
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title"><?php echo e(ai_lang('sql_candidate')); ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="text-secondary small"><?php echo e(ai_lang('model_adapter')); ?></div>
                                <code><?php echo e((string) ($candidate['model_adapter'] ?? '-')); ?></code>
                            </div>
                            <div class="col-md-3">
                                <div class="text-secondary small"><?php echo e(ai_lang('generation_mode')); ?></div>
                                <code><?php echo e((string) ($candidate['generation_mode'] ?? '-')); ?></code>
                            </div>
                            <div class="col-md-3">
                                <div class="text-secondary small"><?php echo e(ai_lang('confidence')); ?></div>
                                <?php echo (int) round(((float) ($candidate['confidence'] ?? 0)) * 100); ?>%
                            </div>
                            <div class="col-md-3">
                                <div class="text-secondary small"><?php echo e(ai_lang('execution')); ?></div>
                                <span class="badge bg-red-lt"><?php echo e(ai_lang('disabled')); ?></span>
                            </div>
                            <div class="col-12">
                                <div class="text-secondary small mb-1"><?php echo e(ai_lang('candidate_sql')); ?></div>
                                <pre class="bg-body-tertiary p-3 rounded"><code><?php echo e((string) ($candidate['candidate_sql'] ?? '')); ?></code></pre>
                            </div>
                            <?php if (!empty($candidate['warnings'])): ?>
                                <div class="col-12">
                                    <div class="text-secondary small mb-1"><?php echo e(ai_lang('candidate_warnings')); ?></div>
                                    <?php $renderBadges((array) ($candidate['warnings'] ?? []), 'bg-yellow-lt'); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (is_array($preview)): ?>
                <?php $guardAllowed = !empty($guard['allowed']); ?>
                <div class="row row-cards mt-1">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="subheader"><?php echo e(ai_lang('guard_result')); ?></div>
                                <span class="badge <?php echo $guardAllowed ? 'bg-green-lt' : 'bg-red-lt'; ?>">
                                    <?php echo e($guardAllowed ? ai_lang('allowed') : ai_lang('blocked')); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="subheader"><?php echo e(ai_lang('execution')); ?></div>
                                <span class="badge bg-red-lt"><?php echo e(ai_lang('disabled')); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="subheader"><?php echo e(ai_lang('explain')); ?></div>
                                <code><?php echo e((string) ($explain['reason'] ?? ($explain['status'] ?? '-'))); ?></code>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="subheader"><?php echo e(ai_lang('data_read')); ?></div>
                                <span class="badge bg-green-lt"><?php echo e(ai_lang('no')); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title"><?php echo e(ai_lang('audit_chain')); ?></h3>
                    </div>
                    <div class="card-body">
                        <?php $renderBadges(['query_plan_preview', 'sql_candidate_generate', 'sql_preview_check'], 'bg-blue-lt'); ?>
                        <?php if (!empty($explain['enabled'])): ?>
                            <?php $renderBadges(['sql_explain_check'], 'bg-blue-lt'); ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
