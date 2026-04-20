<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

$modules = kirpi_list_modules();
$requiredByMap = [];

foreach ($modules as $candidateModule) {
    $candidateKey = (string) ($candidateModule['key'] ?? '');
    if ($candidateKey === '') {
        continue;
    }

    if (!array_key_exists($candidateKey, $requiredByMap)) {
        $requiredByMap[$candidateKey] = [];
    }
}

foreach ($modules as $candidateModule) {
    if (empty($candidateModule['enabled'])) {
        continue;
    }

    $ownerKey = (string) ($candidateModule['key'] ?? '');
    $requires = array_map('strval', (array) ($candidateModule['requires'] ?? []));

    foreach ($requires as $requiredKey) {
        if ($requiredKey === '' || !array_key_exists($requiredKey, $requiredByMap)) {
            continue;
        }

        $requiredByMap[$requiredKey][] = $ownerKey;
    }
}

foreach ($requiredByMap as $moduleKey => $dependents) {
    $requiredByMap[$moduleKey] = array_values(array_unique($dependents));
}
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Sistem Yonetimi</div>
                <h2 class="page-title">Modul Yonetimi</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <?php if (!kirpi_modules_registry_ready()): ?>
            <div class="alert alert-warning">
                <code>app_modules</code> tablosu hazir degil. Ayarlar ekranindan <strong>Eksikleri Kur</strong> calistirin.
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Moduller</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-striped mb-0">
                    <thead>
                    <tr>
                        <th>Key</th>
                        <th>Ad</th>
                        <th>Versiyon</th>
                        <th>Sira</th>
                        <th>Bagimlilik</th>
                        <th>Kullanan Moduller</th>
                        <th>Tip</th>
                        <th>Durum</th>
                        <th class="w-1">Islem</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($modules)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-secondary py-4">Modul bulunamadi.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($modules as $module): ?>
                            <?php
                            $moduleKey = (string) ($module['key'] ?? '');
                            $isCore = !empty($module['core']);
                            $isEnabled = !empty($module['enabled']);
                            $requires = array_map('strval', (array) ($module['requires'] ?? []));
                            $requiredBy = array_map('strval', (array) ($requiredByMap[$moduleKey] ?? []));
                            $hasDisableBlock = !$isCore && $isEnabled && !empty($requiredBy);
                            ?>
                            <tr>
                                <td><code><?php echo e($moduleKey); ?></code></td>
                                <td><?php echo e((string) ($module['name'] ?? $moduleKey)); ?></td>
                                <td><?php echo e((string) ($module['version'] ?? '1.0.0')); ?></td>
                                <td><?php echo (int) ($module['load_order'] ?? 100); ?></td>
                                <td>
                                    <?php if (empty($requires)): ?>
                                        <span class="text-secondary">-</span>
                                    <?php else: ?>
                                        <code><?php echo e(implode(', ', $requires)); ?></code>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (empty($requiredBy)): ?>
                                        <span class="text-secondary">-</span>
                                    <?php else: ?>
                                        <code><?php echo e(implode(', ', $requiredBy)); ?></code>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $isCore ? 'bg-blue-lt' : 'bg-azure-lt'; ?>">
                                        <?php echo $isCore ? 'Core' : 'Plugin'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $isEnabled ? 'bg-green-lt' : 'bg-red-lt'; ?>">
                                        <?php echo $isEnabled ? 'Aktif' : 'Pasif'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($isCore): ?>
                                        <span class="text-secondary small">Kilitle</span>
                                    <?php else: ?>
                                        <form
                                            id="module-toggle-form-<?php echo e($moduleKey); ?>"
                                            action="<?php echo base_url('settings/actions/module-toggle'); ?>"
                                            method="post"
                                            data-ajax="true"
                                            class="d-none"
                                        >
                                            <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">
                                            <input type="hidden" name="module_key" value="<?php echo e($moduleKey); ?>">
                                            <input type="hidden" name="is_enabled" value="<?php echo $isEnabled ? '0' : '1'; ?>">
                                        </form>
                                        <a
                                            href="#"
                                            class="btn btn-sm <?php echo $isEnabled ? 'btn-outline-danger' : 'btn-outline-success'; ?> <?php echo $hasDisableBlock ? 'disabled' : ''; ?>"
                                            <?php if (!$hasDisableBlock): ?>
                                                data-confirm="<?php echo $isEnabled ? 'Bu modul devre disi birakilacak. Emin misiniz?' : 'Bu modul aktif edilecek. Emin misiniz?'; ?>"
                                                data-form="module-toggle-form-<?php echo e($moduleKey); ?>"
                                            <?php endif; ?>
                                            title="<?php echo $hasDisableBlock ? e('Disable engelli. Aktif bagimli moduller: ' . implode(', ', $requiredBy)) : ''; ?>"
                                        >
                                            <?php echo $hasDisableBlock ? 'Bagimli Modul Var' : ($isEnabled ? 'Disable' : 'Enable'); ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
