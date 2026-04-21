<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

require_once BASE_PATH . '/modules/mail/language.php';

$templates = [];
$tableReady = kirpi_mail_templates_table_ready();

if ($tableReady) {
    kirpi_mail_sync_system_templates();

    try {
        $stmt = db()->query("
            SELECT id, template_key, name, subject, html_body, is_active, is_system, updated_at
            FROM mail_templates
            ORDER BY is_system DESC, template_key ASC
        ");
        $templates = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        error_log('mail templates page error: ' . $e->getMessage());
        $templates = [];
    }
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/grapesjs@0.21.9/dist/css/grapes.min.css">

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle"><?php echo e(mail_lang('mail_center')); ?></div>
                <h2 class="page-title"><?php echo e(mail_lang('mail_templates')); ?></h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="<?php echo base_url('mail/test'); ?>" class="btn btn-outline-primary">
                    <?php echo e(mail_lang('back_to_mail_test')); ?>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <?php if (!$tableReady): ?>
            <div class="alert alert-warning">
                <?php echo e(mail_lang('template_tables_missing')); ?>
            </div>
        <?php else: ?>
            <div class="card mb-4">
                <form action="<?php echo base_url('mail/actions/template-create'); ?>" method="post" data-ajax="true">
                    <div class="card-header">
                        <h3 class="card-title"><?php echo e(mail_lang('new_template')); ?></h3>
                    </div>
                    <div class="card-body">
                        <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">
                        <div class="row g-3">
                            <div class="col-12 col-lg-4">
                                <label class="form-label"><?php echo e(mail_lang('template_key')); ?></label>
                                <input type="text" name="template_key" class="form-control" required>
                                <small class="text-secondary"><?php echo e(mail_lang('template_key_format')); ?></small>
                            </div>
                            <div class="col-12 col-lg-4">
                                <label class="form-label"><?php echo e(mail_lang('template_name')); ?></label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-12 col-lg-4">
                                <label class="form-label"><?php echo e(mail_lang('subject')); ?></label>
                                <input type="text" name="subject" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label"><?php echo e(mail_lang('html_body')); ?></label>
                                <textarea id="mail-template-create-html-body" name="html_body" rows="8" class="form-control js-mail-template-html" required></textarea>
                                <small class="text-secondary"><?php echo e(mail_lang('template_vars_hint')); ?></small>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm js-open-grapes-builder" data-textarea-id="mail-template-create-html-body">
                                        GrapesJS Editor
                                    </button>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-check">
                                    <input type="checkbox" class="form-check-input" name="is_active" value="1" checked>
                                    <span class="form-check-label"><?php echo e(mail_lang('is_active')); ?></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary"><?php echo e(mail_lang('create_template')); ?></button>
                    </div>
                </form>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo e(mail_lang('template_list')); ?></h3>
                </div>
                <div class="card-body">
                    <?php if (empty($templates)): ?>
                        <div class="text-secondary"><?php echo e(mail_lang('templates_empty')); ?></div>
                    <?php else: ?>
                        <div class="accordion" id="mail-template-accordion">
                            <?php foreach ($templates as $template): ?>
                                <?php
                                $templateId = (int) ($template['id'] ?? 0);
                                $isSystem = (int) ($template['is_system'] ?? 0) === 1;
                                $isActive = (int) ($template['is_active'] ?? 0) === 1;
                                $subjectRaw = (string) ($template['subject'] ?? '');
                                $bodyRaw = (string) ($template['html_body'] ?? '');
                                $placeholderList = array_values(array_unique(array_merge(
                                    kirpi_mail_extract_placeholders($subjectRaw),
                                    kirpi_mail_extract_placeholders($bodyRaw)
                                )));
                                sort($placeholderList);
                                $itemId = 'mail-template-' . $templateId;
                                ?>
                                <div class="accordion-item mb-3 border rounded">
                                    <h2 class="accordion-header" id="<?php echo e($itemId); ?>-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo e($itemId); ?>-body" aria-expanded="false" aria-controls="<?php echo e($itemId); ?>-body">
                                            <div class="w-100 d-flex align-items-center justify-content-between pe-3">
                                                <div>
                                                    <strong><?php echo e((string) ($template['name'] ?? '')); ?></strong>
                                                    <div class="text-secondary"><code><?php echo e((string) ($template['template_key'] ?? '')); ?></code></div>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <span class="badge <?php echo $isActive ? 'bg-green-lt' : 'bg-red-lt'; ?>"><?php echo e($isActive ? mail_lang('is_active') : mail_lang('missing')); ?></span>
                                                    <span class="badge <?php echo $isSystem ? 'bg-blue-lt' : 'bg-gray-lt'; ?>"><?php echo e($isSystem ? mail_lang('is_system') : mail_lang('custom')); ?></span>
                                                </div>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="<?php echo e($itemId); ?>-body" class="accordion-collapse collapse" aria-labelledby="<?php echo e($itemId); ?>-header" data-bs-parent="#mail-template-accordion">
                                        <div class="accordion-body">
                                            <form action="<?php echo base_url('mail/actions/template-update'); ?>" method="post" data-ajax="true" class="mb-3">
                                                <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">
                                                <input type="hidden" name="id" value="<?php echo $templateId; ?>">
                                                <div class="row g-3">
                                                    <div class="col-12 col-lg-4">
                                                        <label class="form-label"><?php echo e(mail_lang('template_key')); ?></label>
                                                        <input type="text" class="form-control" value="<?php echo e((string) ($template['template_key'] ?? '')); ?>" disabled>
                                                    </div>
                                                    <div class="col-12 col-lg-4">
                                                        <label class="form-label"><?php echo e(mail_lang('template_name')); ?></label>
                                                        <input type="text" name="name" class="form-control" required value="<?php echo e((string) ($template['name'] ?? '')); ?>">
                                                    </div>
                                                    <div class="col-12 col-lg-4">
                                                        <label class="form-label"><?php echo e(mail_lang('subject')); ?></label>
                                                        <input type="text" name="subject" class="form-control" required value="<?php echo e($subjectRaw); ?>">
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label"><?php echo e(mail_lang('html_body')); ?></label>
                                                        <?php $textareaId = 'mail-template-html-body-' . $templateId; ?>
                                                        <textarea id="<?php echo e($textareaId); ?>" name="html_body" rows="8" class="form-control js-mail-template-html" required><?php echo e($bodyRaw); ?></textarea>
                                                        <div class="mt-2">
                                                            <button type="button" class="btn btn-outline-secondary btn-sm js-open-grapes-builder" data-textarea-id="<?php echo e($textareaId); ?>">
                                                                GrapesJS Editor
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 d-flex align-items-center justify-content-between">
                                                        <label class="form-check">
                                                            <input type="checkbox" class="form-check-input" name="is_active" value="1" <?php echo $isActive ? 'checked' : ''; ?>>
                                                            <span class="form-check-label"><?php echo e(mail_lang('is_active')); ?></span>
                                                        </label>
                                                        <button type="submit" class="btn btn-primary"><?php echo e(mail_lang('update_template')); ?></button>
                                                    </div>
                                                </div>
                                            </form>

                                            <div class="mb-3">
                                                <div class="text-secondary mb-1"><?php echo e(mail_lang('placeholders')); ?></div>
                                                <?php if (empty($placeholderList)): ?>
                                                    <span class="text-secondary">-</span>
                                                <?php else: ?>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <?php foreach ($placeholderList as $placeholder): ?>
                                                            <span class="badge bg-azure-lt"><code>{{<?php echo e($placeholder); ?>}}</code></span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <?php if (!$isSystem): ?>
                                                <form action="<?php echo base_url('mail/actions/template-delete'); ?>" method="post" data-ajax="true">
                                                    <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">
                                                    <input type="hidden" name="id" value="<?php echo $templateId; ?>">
                                                    <button type="submit" class="btn btn-outline-danger" data-confirm="<?php echo e(mail_lang('delete_template')); ?>?">
                                                        <?php echo e(mail_lang('delete_template')); ?>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal modal-blur fade" id="grapesjs-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">GrapesJS Email Builder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2">
                <div id="grapesjs-editor" style="min-height:70vh;border:1px solid #e6e7e9;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?php echo e(mail_lang('cancel')); ?></button>
                <button type="button" class="btn btn-primary" id="grapesjs-apply"><?php echo e(mail_lang('save_template')); ?></button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/grapesjs@0.21.9/dist/grapes.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/grapesjs-preset-newsletter@1.0.2/dist/grapesjs-preset-newsletter.min.js"></script>
<script>
(function () {
    let grapesEditor = null;
    let currentTextarea = null;
    let modalInstance = null;

    function getModalInstance() {
        const modalEl = document.getElementById('grapesjs-modal');
        if (!modalEl || !(window.bootstrap && bootstrap.Modal)) {
            return null;
        }

        if (!modalInstance) {
            modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        }

        return modalInstance;
    }

    function initEditor() {
        if (grapesEditor) {
            return grapesEditor;
        }

        grapesEditor = grapesjs.init({
            container: '#grapesjs-editor',
            height: '70vh',
            fromElement: false,
            storageManager: false,
            plugins: ['gjs-preset-newsletter'],
            pluginsOpts: {
                'gjs-preset-newsletter': {}
            },
            panels: {
                defaults: []
            }
        });

        return grapesEditor;
    }

    function openBuilder(textarea) {
        if (!textarea) {
            return;
        }

        const editor = initEditor();
        currentTextarea = textarea;

        const rawHtml = textarea.value || '';
        editor.setComponents(rawHtml);
        editor.setStyle('');

        const instance = getModalInstance();
        if (instance) {
            instance.show();
        }
    }

    function applyBuilder() {
        if (!grapesEditor || !currentTextarea) {
            return;
        }

        const html = grapesEditor.getHtml();
        const css = grapesEditor.getCss();
        currentTextarea.value = css ? ('<style>' + css + '</style>' + html) : html;

        const instance = getModalInstance();
        if (instance) {
            instance.hide();
        }
    }

    document.addEventListener('click', function (event) {
        const trigger = event.target.closest('.js-open-grapes-builder');
        if (!trigger) {
            return;
        }

        event.preventDefault();
        const textareaId = trigger.getAttribute('data-textarea-id') || '';
        const textarea = document.getElementById(textareaId);
        if (!textarea) {
            return;
        }

        openBuilder(textarea);
    });

    const applyButton = document.getElementById('grapesjs-apply');
    if (applyButton) {
        applyButton.addEventListener('click', applyBuilder);
    }
})();
</script>
