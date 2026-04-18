<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    display_error_page(
        '404 - Rol Bulunamadı',
        'Geçersiz rol ID.',
        404,
        true
    );
}

$role = null;
$assignedPermissions = [];
$permissionCatalog = kirpi_core_permission_catalog();
$permissionSchemaReady = db_table_exists('permissions') && db_table_exists('role_permissions');
$isSuperAdminRole = false;

try {
    $stmt = db()->prepare("
        SELECT id, name, is_active
        FROM roles
        WHERE id = :id
        LIMIT 1
    ");
    $stmt->execute([
        ':id' => $id,
    ]);

    $role = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$role) {
        throw new RuntimeException('Rol bulunamadı.');
    }

    $isSuperAdminRole = ($role['name'] ?? '') === 'Super Admin';

    if ($permissionSchemaReady) {
        $assignedPermissions = get_role_permission_slugs((int) $role['id']);
    }
} catch (Throwable $e) {
    error_log('roles permissions page error: ' . $e->getMessage());

    display_error_page(
        '500 - Rol Verileri Yüklenemedi',
        'Rol yetkileri yüklenirken bir hata oluştu.',
        500,
        true
    );
}
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Rol Yönetimi</div>
                <h2 class="page-title">İzin Matrisi</h2>
                <div class="text-secondary mt-1">
                    Rol: <?php echo e($role['name']); ?>
                </div>
            </div>

            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="<?php echo base_url('roles/view'); ?>" class="btn">
                        Geri Dön
                    </a>
                    <?php if (!$isSuperAdminRole && $permissionSchemaReady): ?>
                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            id="roles-permissions-select-all"
                        >
                            Tümünü Seç
                        </button>
                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            id="roles-permissions-clear-all"
                        >
                            Tümünü Kaldır
                        </button>
                    <?php endif; ?>
                    <?php if (!$isSuperAdminRole && $permissionSchemaReady): ?>
                        <button
                            type="submit"
                            form="roles-permissions-form"
                            class="btn btn-primary"
                        >
                            Kaydet
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <?php if (!$permissionSchemaReady): ?>
            <div class="alert alert-warning">
                Permission tabloları henüz kurulu değil. Önce
                <code>database/permissions.sql</code> dosyasını çalıştırın veya
                <code>php shell.php db:permissions:install</code> komutunu kullanın.
            </div>
        <?php elseif ($isSuperAdminRole): ?>
            <div class="alert alert-info">
                Super Admin rolü tüm yetkilere doğrudan sahiptir. Bu rol için izin ataması yapılmaz.
            </div>
        <?php endif; ?>

        <form
            id="roles-permissions-form"
            action="<?php echo base_url('roles/actions/permissions-update'); ?>"
            method="post"
            data-ajax="true"
        >
            <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">
            <input type="hidden" name="id" value="<?php echo (int) $role['id']; ?>">

            <div class="card">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-striped">
                        <thead>
                            <tr>
                                <th style="min-width: 220px;">Modül</th>
                                <th>İzinler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($permissionCatalog as $groupKey => $group): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?php echo e($group['title'] ?? $groupKey); ?></div>
                                        <div class="text-secondary"><?php echo e($groupKey); ?></div>
                                        <?php if (!$isSuperAdminRole && $permissionSchemaReady): ?>
                                            <label class="form-check mt-3 mb-0">
                                                <input
                                                    type="checkbox"
                                                    class="form-check-input roles-permissions-group-toggle"
                                                    data-group="<?php echo e($groupKey); ?>"
                                                >
                                                <span class="form-check-label">Tümünü seç</span>
                                            </label>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="row g-3" data-permission-group="<?php echo e($groupKey); ?>">
                                            <?php foreach (($group['permissions'] ?? []) as $permission): ?>
                                                <div class="col-12 col-lg-6">
                                                    <label class="form-check">
                                                        <input
                                                            type="checkbox"
                                                            name="permission_slugs[]"
                                                            value="<?php echo e($permission['slug']); ?>"
                                                            class="form-check-input"
                                                            <?php echo in_array($permission['slug'], $assignedPermissions, true) ? 'checked' : ''; ?>
                                                            <?php echo (!$permissionSchemaReady || $isSuperAdminRole) ? 'disabled' : ''; ?>
                                                        >
                                                        <span class="form-check-label">
                                                            <?php echo e($permission['name']); ?>
                                                        </span>
                                                        <span class="form-check-description d-block text-secondary">
                                                            <?php echo e($permission['slug']); ?>
                                                        </span>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>
</div>
