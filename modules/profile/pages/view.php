<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

$currentUser = current_user();
$profile = null;

if (!$currentUser || !isset($currentUser['id'])) {
    display_error_page(
        '403 - Yetkisiz Erişim',
        'Profil bilgilerine erişilemedi.',
        403,
        true
    );
}

try {
    $stmt = db()->prepare("
        SELECT
            u.id,
            u.name,
            u.email,
            u.avatar,
            u.is_active,
            r.name AS role_name
        FROM users u
        LEFT JOIN roles r ON r.id = u.role_id
        WHERE u.id = :id
        LIMIT 1
    ");
    $stmt->execute([
        ':id' => (int) $currentUser['id'],
    ]);

    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$profile) {
        throw new RuntimeException('Profil bulunamadı.');
    }
} catch (Throwable $e) {
    error_log('profile page error: ' . $e->getMessage());

    display_error_page(
        '500 - Profil Yüklenemedi',
        'Profil verileri yüklenirken bir hata oluştu.',
        500,
        true
    );
}

$avatarUrl = !empty($profile['avatar'])
    ? base_url('uploads/avatars/' . ltrim($profile['avatar'], '/'))
    : null;

$initial = mb_strtoupper(mb_substr($profile['name'] ?? 'U', 0, 1));
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Hesabım</div>
                <h2 class="page-title">Profil</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row g-4">
            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-body text-center">
                        <?php if ($avatarUrl): ?>
                            <span
                                class="avatar avatar-xl mb-3"
                                style="width: 96px; height: 96px; background-image: url('<?php echo e($avatarUrl); ?>')"
                            ></span>
                        <?php else: ?>
                            <span class="avatar avatar-xl mb-3" style="width: 96px; height: 96px;">
                                <?php echo e($initial); ?>
                            </span>
                        <?php endif; ?>

                        <h3 class="m-0 mb-1"><?php echo e($profile['name']); ?></h3>
                        <div class="text-secondary"><?php echo e($profile['email']); ?></div>

                        <div class="mt-3">
                            <span class="badge bg-blue-lt"><?php echo e($profile['role_name'] ?: 'Rol Yok'); ?></span>
                            <span class="badge <?php echo (int) $profile['is_active'] === 1 ? 'bg-green-lt' : 'bg-red-lt'; ?>">
                                <?php echo (int) $profile['is_active'] === 1 ? 'Aktif' : 'Pasif'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Profil Bilgileri</h3>
                    </div>

                    <form
                        id="profile-update-form"
                        action="<?php echo base_url('profile/actions/update'); ?>"
                        method="post"
                        enctype="multipart/form-data"
                        data-ajax="true"
                    >
                        <div class="card-body">
                            <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label form-required">Ad Soyad</label>
                                    <input
                                        type="text"
                                        name="name"
                                        class="form-control"
                                        value="<?php echo e($profile['name']); ?>"
                                        required
                                    >
                                </div>

                                <div class="col-12">
                                    <label class="form-label form-required">E-posta</label>
                                    <input
                                        type="email"
                                        name="email"
                                        class="form-control"
                                        value="<?php echo e($profile['email']); ?>"
                                        required
                                    >
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Yeni Şifre</label>
                                    <input
                                        type="password"
                                        name="password"
                                        class="form-control"
                                        placeholder="Boş bırakılırsa değişmez"
                                    >
                                    <small class="form-hint">Şifre değiştirmek istemiyorsanız boş bırakın.</small>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Yeni Şifre Tekrar</label>
                                    <input
                                        type="password"
                                        name="password_confirm"
                                        class="form-control"
                                        placeholder="Boş bırakılırsa değişmez"
                                    >
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Profil Görseli</label>
                                    <input
                                        type="file"
                                        name="avatar"
                                        class="form-control"
                                        accept=".jpg,.jpeg,.png,.webp"
                                    >
                                    <small class="form-hint">JPG, PNG veya WEBP. Maksimum 2 MB.</small>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer text-end">
                            <button type="submit" class="btn btn-primary">
                                Profili Güncelle
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
