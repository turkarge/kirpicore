<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}
?>

<div class="modal-header">
    <h5 class="modal-title">Yeni Rol</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<form
    id="roles-create-form"
    action="<?php echo base_url('roles/actions/create'); ?>"
    method="post"
    data-ajax="true"
    data-close-modal="true"
>
    <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">

        <div class="mb-3">
            <label class="form-label form-required">Rol Adı</label>
            <input
                type="text"
                name="name"
                class="form-control"
                maxlength="100"
                required
            >
            <small class="form-hint">Örnek: İçerik Editörü, Operasyon, Super Admin</small>
        </div>

        <div>
            <label class="form-check form-switch m-0">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    class="form-check-input"
                    checked
                >
                <span class="form-check-label">Rol aktif olsun</span>
            </label>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn me-auto" data-bs-dismiss="modal">İptal</button>
        <button type="submit" class="btn btn-primary">Kaydet</button>
    </div>
</form>
