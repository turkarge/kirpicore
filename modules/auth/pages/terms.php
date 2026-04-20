<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?php echo e(app_name()); ?> - Kullanım Şartları</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="<?php echo asset_url('css/tabler.min.css'); ?>" rel="stylesheet">
    <link href="<?php echo asset_url('css/tabler-icons.min.css'); ?>" rel="stylesheet">
    <link href="<?php echo asset_url('css/app.css'); ?>" rel="stylesheet">
</head>
<body>
<div class="page">
    <div class="page-wrapper">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <h2 class="page-title">Kullanım Şartları</h2>
                        <div class="text-secondary mt-1"><?php echo e(app_name()); ?></div>
                    </div>
                    <div class="col-auto ms-auto">
                        <a href="<?php echo base_url('auth/login'); ?>" class="btn btn-primary">Girişe Dön</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-xl">
                <div class="card">
                    <div class="card-body">
                        <h3>1. Genel Hükümler</h3>
                        <p>
                            Bu uygulamayı kullanan tüm kullanıcılar, sistemin güvenli ve yetkili kullanımından sorumludur.
                        </p>

                        <h3>2. Hesap Güvenliği</h3>
                        <p>
                            Kullanıcılar, oturum bilgilerini korumakla yükümlüdür. Yetkisiz erişim şüphesi halinde sistem yöneticisine bilgi verilmelidir.
                        </p>

                        <h3>3. Veri Kullanımı</h3>
                        <p>
                            Sistem üzerinde oluşturulan, görüntülenen veya işlenen tüm veriler kurum politikalarına ve ilgili mevzuata uygun şekilde kullanılmalıdır.
                        </p>

                        <h3>4. Son Hüküm</h3>
                        <p class="mb-0">
                            Bu metin başlangıç sürümüdür. Nihai kullanım şartları daha sonra uygulamaya özel şekilde genişletilebilir.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo kirpi_analytics_snippet(); ?>
</body>
</html>
