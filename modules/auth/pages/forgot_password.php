<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

if (is_user_logged_in()) {
    redirect(base_url(APP_DEFAULT_ROUTE));
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?php echo e(app_name()); ?> - Şifremi Unuttum</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="<?php echo asset_url('css/tabler.min.css'); ?>" rel="stylesheet">
    <link href="<?php echo asset_url('css/tabler-icons.min.css'); ?>" rel="stylesheet">
    <link href="<?php echo asset_url('css/app.css'); ?>" rel="stylesheet">
</head>
<body class="d-flex flex-column">
<div class="page page-center">
    <div class="container container-tight py-4">
        <div class="text-center mb-4">
            <h1 class="navbar-brand navbar-brand-autodark">
                <?php echo e(app_name()); ?>
            </h1>
        </div>

        <form class="card card-md" autocomplete="off" novalidate>
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Şifrenizi mi unuttunuz?</h2>
                <p class="text-secondary mb-4">
                    E-posta adresinizi girin. Şifre sıfırlama sürecini sonraki adımda bağlayacağız.
                </p>

                <div class="mb-3">
                    <label class="form-label">E-posta adresi</label>
                    <input type="email" class="form-control" placeholder="ornek@alanadi.com">
                </div>

                <div class="form-footer">
                    <button type="button" class="btn btn-primary w-100" disabled>
                        Sıfırlama Bağlantısı Gönder
                    </button>
                </div>
            </div>
        </form>

        <div class="text-center text-secondary mt-3">
            <a href="<?php echo base_url('auth/login'); ?>">Giriş ekranına dön</a>
        </div>
    </div>
</div>
</body>
</html>
