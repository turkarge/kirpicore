<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

$user = current_user();
$userName = $user['name'] ?? 'Kullanıcı';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?php echo e(app_name()); ?> - Oturum Kilitli</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="<?php echo asset_url('css/tabler.min.css'); ?>" rel="stylesheet">
    <link href="<?php echo asset_url('css/tabler-icons.min.css'); ?>" rel="stylesheet">
    <link href="<?php echo asset_url('css/app.css'); ?>" rel="stylesheet">
</head>
<body class=" d-flex flex-column">
<div class="page page-center">
    <div class="container container-tight py-4">
        <div class="text-center mb-4">
            <div class="mb-3">
                <span class="avatar avatar-xl rounded">
                    <?php echo e(mb_strtoupper(mb_substr($userName, 0, 1))); ?>
                </span>
            </div>
            <h2 class="mb-1"><?php echo e($userName); ?></h2>
            <div class="text-secondary">Oturum kilitlendi</div>
        </div>

        <div class="card card-md">
            <div class="card-body">
                <div class="text-center mb-3">
                    Devam etmek için tekrar giriş yapın.
                </div>

                <div class="form-footer">
                    <a href="<?php echo base_url('auth/login'); ?>" class="btn btn-primary w-100">
                        Giriş Ekranına Git
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>