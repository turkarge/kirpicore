<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

if (!is_user_logged_in()) {
    redirect(base_url('auth/login'));
}

if (!kirpi_session_lock_state()) {
    redirect(base_url(APP_DEFAULT_ROUTE));
}

$user = current_user();
$userName = (string) ($user['name'] ?? 'Kullanici');
$userRole = (string) ($user['role_name'] ?? '');
$initial = mb_strtoupper(mb_substr($userName, 0, 1));
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?php echo e(app_name()); ?> - Oturum Kilidi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="<?php echo asset_url('css/tabler.min.css'); ?>" rel="stylesheet">
    <link href="<?php echo asset_url('css/tabler-icons.min.css'); ?>" rel="stylesheet">
    <link href="<?php echo asset_url('css/app.css'); ?>" rel="stylesheet">
    <link href="<?php echo asset_url('css/toastr.min.css'); ?>" rel="stylesheet">
</head>
<body class="d-flex flex-column">
<script>
window.KIRPI_CONFIG = {
    baseUrl: "<?php echo e(BASE_URL); ?>",
    csrfToken: "<?php echo e(get_csrf_token()); ?>"
};
</script>

<div class="page page-center">
    <div class="container container-tight py-4">
        <div class="text-center mb-4">
            <span class="avatar avatar-xl mb-3"><?php echo e($initial); ?></span>
            <h2 class="mb-1"><?php echo e($userName); ?></h2>
            <?php if ($userRole !== ''): ?>
                <div class="text-secondary mb-2"><?php echo e($userRole); ?></div>
            <?php endif; ?>
            <div class="text-secondary">Oturum kilitlendi. Devam etmek icin key girin.</div>
        </div>

        <div class="card card-md">
            <div class="card-body">
                <form action="<?php echo base_url('auth/actions/unlock'); ?>" method="post" data-ajax="true">
                    <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">

                    <div class="mb-3">
                        <label class="form-label form-required">Key (4-6 haneli)</label>
                        <input
                            type="password"
                            name="lock_pin"
                            class="form-control"
                            inputmode="numeric"
                            pattern="\\d{4,6}"
                            minlength="4"
                            maxlength="6"
                            placeholder="••••"
                            autocomplete="off"
                            required
                        >
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Kilidi Ac</button>
                    </div>
                </form>

                <form action="<?php echo base_url('auth/actions/logout'); ?>" method="post" data-ajax="true" class="mt-3">
                    <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">
                    <button type="submit" class="btn btn-outline-secondary w-100">Farkli hesap ile giris yap</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo asset_url('js/jquery-3.7.1.min.js'); ?>"></script>
<script src="<?php echo asset_url('js/bootstrap.bundle.min.js'); ?>"></script>
<script src="<?php echo asset_url('js/tabler.min.js'); ?>"></script>
<script src="<?php echo asset_url('js/toastr.min.js'); ?>"></script>
<script src="<?php echo asset_url('js/app.js'); ?>"></script>
</body>
</html>
