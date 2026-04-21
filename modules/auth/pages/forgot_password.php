<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

require_once BASE_PATH . '/modules/auth/language.php';

if (is_user_logged_in()) {
    redirect(base_url(APP_DEFAULT_ROUTE));
}
?>
<!DOCTYPE html>
<html lang="<?php echo e(strtolower((string) env('APP_LOCALE', 'tr'))); ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo e(app_name()); ?> - <?php echo e(auth_lang('forgot_title')); ?></title>
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
                <h2 class="card-title text-center mb-4"><?php echo e(auth_lang('forgot_heading')); ?></h2>
                <p class="text-secondary mb-4">
                    <?php echo e(auth_lang('forgot_description')); ?>
                </p>

                <div class="mb-3">
                    <label class="form-label"><?php echo e(auth_lang('email')); ?></label>
                    <input type="email" class="form-control" placeholder="<?php echo e(auth_lang('email_placeholder')); ?>">
                </div>

                <div class="form-footer">
                    <button type="button" class="btn btn-primary w-100" disabled>
                        <?php echo e(auth_lang('forgot_send')); ?>
                    </button>
                </div>
            </div>
        </form>

        <div class="text-center text-secondary mt-3">
            <a href="<?php echo base_url('auth/login'); ?>"><?php echo e(auth_lang('back_to_login')); ?></a>
        </div>
    </div>
</div>
<!-- Cloudflare Web Analytics --><script defer src='https://static.cloudflareinsights.com/beacon.min.js' data-cf-beacon='{"token": "7356366510c54c86a154d277ed978201"}'></script><!-- End Cloudflare Web Analytics -->
</body>
</html>
