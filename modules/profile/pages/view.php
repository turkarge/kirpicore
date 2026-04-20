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
$isSuperAdmin = ((string) ($profile['role_name'] ?? '')) === 'Super Admin';
$apiTokenOnce = $_SESSION['profile_api_token_once'] ?? null;
if (isset($_SESSION['profile_api_token_once'])) {
    unset($_SESSION['profile_api_token_once']);
}
$apiEnabled = api_is_enabled();
$apiTokenTableReady = api_token_table_ready();
$apiTokenRows = $isSuperAdmin ? api_list_tokens_for_user((int) ($profile['id'] ?? 0), 100) : [];
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
                <?php if ($isSuperAdmin): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">API Token Yonetimi (Super Admin)</h3>
                        </div>
                        <div class="card-body">
                            <?php if (is_array($apiTokenOnce) && !empty($apiTokenOnce['token'])): ?>
                                <div class="alert alert-warning">
                                    <div class="fw-bold mb-2">Bu token sadece bir kez gosterilir. Guvenli bir yerde saklayin.</div>
                                    <div class="mb-2">
                                        <label class="form-label mb-1">Token</label>
                                        <input
                                            type="text"
                                            class="form-control w-100 js-token-copy"
                                            readonly
                                            title="Kopyalamak icin tiklayin"
                                            value="<?php echo e((string) ($apiTokenOnce['token'] ?? '')); ?>"
                                        >
                                    </div>
                                    <div class="text-secondary small">
                                        Token Name: <?php echo e((string) ($apiTokenOnce['token_name'] ?? '-')); ?> |
                                        Expires At: <?php echo !empty($apiTokenOnce['is_unlimited']) ? 'Sinirsiz' : e((string) ($apiTokenOnce['expires_at'] ?? '-')); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <form action="<?php echo base_url('profile/actions/create-api-token'); ?>" method="post">
                                <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">
                                <div class="row g-3">
                                    <div class="col-12 col-md-8">
                                        <label class="form-label">Token Name</label>
                                        <input type="text" name="token_name" class="form-control" placeholder="ornek: postman" value="profile-token" <?php echo (!$apiEnabled || !$apiTokenTableReady) ? 'disabled' : ''; ?>>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">Gecerlilik</label>
                                        <select name="ttl_option" class="form-select" <?php echo (!$apiEnabled || !$apiTokenTableReady) ? 'disabled' : ''; ?>>
                                            <option value="24h">24 Saat</option>
                                            <option value="1_month" selected>1 Ay</option>
                                            <option value="3_months">3 Ay</option>
                                            <option value="6_months">6 Ay</option>
                                            <option value="1_year">1 Yil</option>
                                            <option value="unlimited">Sinirsiz</option>
                                        </select>
                                    </div>
                                    <div class="col-12 d-flex align-items-end">
                                        <button type="submit" class="btn btn-outline-primary w-100" <?php echo (!$apiEnabled || !$apiTokenTableReady) ? 'disabled' : ''; ?>>API Token Olustur</button>
                                    </div>
                                </div>
                            </form>

                            <?php if (!$apiEnabled): ?>
                                <div class="alert alert-warning mt-3 mb-0">API su an Ayarlar ekranindan kapatildi.</div>
                            <?php endif; ?>

                            <?php if (!$apiTokenTableReady): ?>
                                <div class="alert alert-warning mt-3 mb-0">`api_tokens` tablosu hazir degil. Ayarlar > Eksikleri Kur calistirin.</div>
                            <?php endif; ?>

                            <hr class="my-4">
                            <div class="table-responsive">
                                <table class="table table-vcenter card-table table-striped mb-0">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Created</th>
                                        <th>Last Used</th>
                                        <th>Expires</th>
                                        <th>Status</th>
                                        <th class="w-1"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($apiTokenRows)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-secondary py-4">API token kaydi yok.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($apiTokenRows as $tokenRow): ?>
                                            <?php
                                            $isRevoked = !empty($tokenRow['revoked_at']);
                                            $expiresAtRaw = (string) ($tokenRow['expires_at'] ?? '');
                                            $isUnlimitedToken = $expiresAtRaw !== '' && strtotime($expiresAtRaw) !== false && strtotime($expiresAtRaw) >= strtotime('2099-01-01 00:00:00');
                                            $isExpired = !$isRevoked && !$isUnlimitedToken && $expiresAtRaw !== '' && strtotime($expiresAtRaw) !== false && strtotime($expiresAtRaw) < time();
                                            $statusLabel = $isRevoked ? 'Revoked' : ($isExpired ? 'Expired' : 'Active');
                                            $statusClass = $isRevoked ? 'bg-red-lt' : ($isExpired ? 'bg-yellow-lt' : 'bg-green-lt');
                                            $tokenId = (int) ($tokenRow['id'] ?? 0);
                                            ?>
                                            <tr>
                                                <td><?php echo $tokenId; ?></td>
                                                <td><?php echo e((string) ($tokenRow['token_name'] ?? 'default')); ?></td>
                                                <td><?php echo e((string) ($tokenRow['created_at'] ?? '-')); ?></td>
                                                <td><?php echo e((string) ($tokenRow['last_used_at'] ?? '-')); ?></td>
                                                <td><?php echo e($isUnlimitedToken ? 'Sinirsiz' : ($expiresAtRaw !== '' ? $expiresAtRaw : '-')); ?></td>
                                                <td><span class="badge <?php echo e($statusClass); ?>"><?php echo e($statusLabel); ?></span></td>
                                                <td>
                                                    <?php if (!$isRevoked && !$isExpired): ?>
                                                        <form id="profile-revoke-token-form-<?php echo $tokenId; ?>" action="<?php echo base_url('profile/actions/revoke-api-token'); ?>" method="post" class="d-none">
                                                            <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">
                                                            <input type="hidden" name="token_id" value="<?php echo $tokenId; ?>">
                                                        </form>
                                                        <a href="#" class="btn btn-sm btn-outline-danger" data-confirm="Bu API token iptal edilecek. Emin misiniz?" data-form="profile-revoke-token-form-<?php echo $tokenId; ?>">Revoke</a>
                                                    <?php else: ?>
                                                        <span class="text-secondary small">-</span>
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
                <?php endif; ?>

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

<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".js-token-copy").forEach(function (input) {
        input.addEventListener("click", async function () {
            const value = input.value || "";
            if (!value) {
                return;
            }

            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(value);
                } else {
                    input.select();
                    document.execCommand("copy");
                }

                if (window.KirpiCore && typeof window.KirpiCore.toast === "function") {
                    window.KirpiCore.toast("Token panoya kopyalandi.", "success");
                }
            } catch (error) {
                if (window.KirpiCore && typeof window.KirpiCore.toast === "function") {
                    window.KirpiCore.toast("Token kopyalanamadi.", "error");
                }
            }
        });
    });
});
</script>
