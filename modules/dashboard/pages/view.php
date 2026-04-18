<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Kirpi Core</div>
                <h2 class="page-title">Dashboard</h2>
                <div class="text-secondary mt-1">
                    Çekirdek yapı başarıyla ayağa kalktı.
                </div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="#" class="btn btn-primary btn-modal-trigger" data-url="/ajax/dashboard/about"
                        data-size="modal-lg">
                        Hakkında
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-deck row-cards">

            <div class="col-sm-6 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader">Uygulama</div>
                        <div class="h1 mb-3"><?php echo e(app_name()); ?></div>
                        <div class="text-secondary">
                            Temel çekirdek başarıyla çalışıyor.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader">Ortam</div>
                        <div class="h1 mb-3"><?php echo e(APP_ENV); ?></div>
                        <div class="text-secondary">
                            Debug: <?php echo APP_DEBUG ? 'Açık' : 'Kapalı'; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader">Varsayılan Rota</div>
                        <div class="h1 mb-3">dashboard/view</div>
                        <div class="text-secondary">
                            Route çözümleyici aktif.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Sistem Kontrol Listesi</h3>
                    </div>
                    <div class="list-group list-group-flush">

                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="status-dot status-dot-animated bg-green d-block"></span>
                                </div>
                                <div class="col text-truncate">
                                    <span class="text-body d-block">Front controller çalışıyor</span>
                                    <div class="d-block text-secondary text-truncate mt-n1">
                                        index.php isteği başarıyla yönlendiriyor.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="status-dot status-dot-animated bg-green d-block"></span>
                                </div>
                                <div class="col text-truncate">
                                    <span class="text-body d-block">Layout sistemi çalışıyor</span>
                                    <div class="d-block text-secondary text-truncate mt-n1">
                                        header / footer / nav başarılı şekilde yükleniyor.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="status-dot status-dot-animated bg-green d-block"></span>
                                </div>
                                <div class="col text-truncate">
                                    <span class="text-body d-block">Sayfa script çözümleme aktif</span>
                                    <div class="d-block text-secondary text-truncate mt-n1">
                                        modules/dashboard/scripts/view.js otomatik yükleniyor.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="status-dot status-dot-animated bg-green d-block"></span>
                                </div>
                                <div class="col text-truncate">
                                    <span class="text-body d-block">Assets başarıyla yüklendi</span>
                                    <div class="d-block text-secondary text-truncate mt-n1">
                                        Tabler 1.4.0 ve app assets aktif.
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>