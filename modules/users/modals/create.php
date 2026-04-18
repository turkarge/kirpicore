<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

$roles = [];

try {
    $roles = get_roles_for_select(null, true);
} catch (Throwable $e) {
    error_log('users create modal roles error: ' . $e->getMessage());
}
?>

<div class="modal-header">
    <h5 class="modal-title">Yeni Kullanıcı</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<form
    id="users-create-form"
    action="<?php echo base_url('users/actions/create'); ?>"
    method="post"
    enctype="multipart/form-data"
    data-ajax="true"
    data-close-modal="true"
>
    <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">

        <div id="users-create-alert-area"></div>

        <div class="row g-3">
            <div class="col-12 col-md-8">
                <label class="form-label form-required">Ad Soyad</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label">Rol</label>
                <select name="role_id" class="form-select">
                    <option value="">Rol Seçin</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo (int)$role['id']; ?>">
                            <?php echo e($role['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-hint">Yalnızca aktif roller listelenir.</small>
            </div>

            <div class="col-12">
                <label class="form-label form-required">E-posta</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label form-required">Şifre</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label form-required">Şifre Tekrar</label>
                <input type="password" name="password_confirm" class="form-control" required>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label">Profil Görseli</label>
                <input type="file" name="avatar" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                <small class="form-hint">JPG, PNG veya WEBP. Maksimum 2 MB.</small>
            </div>

            <div class="col-12 col-md-6 d-flex align-items-end">
                <label class="form-check form-switch m-0">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" checked>
                    <span class="form-check-label">Kullanıcı aktif olsun</span>
                </label>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn me-auto" data-bs-dismiss="modal">İptal</button>
        <button type="submit" class="btn btn-primary" id="users-create-submit-button">Kaydet</button>
    </div>
</form>
