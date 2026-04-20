<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

$metrics = [
    'user_total' => 0,
    'user_active' => 0,
    'role_total' => 0,
    'notifications_unread' => 0,
    'api_24h_total' => 0,
    'throttle_active_blocks' => 0,
    'enabled_modules' => 0,
];

try {
    if (db_table_exists('users')) {
        $stmt = db()->query('SELECT COUNT(*) AS total, SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active_total FROM users');
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $metrics['user_total'] = (int) ($row['total'] ?? 0);
        $metrics['user_active'] = (int) ($row['active_total'] ?? 0);
    }

    if (db_table_exists('roles')) {
        $stmt = db()->query('SELECT COUNT(*) FROM roles');
        $metrics['role_total'] = (int) $stmt->fetchColumn();
    }

    $currentUser = current_user();
    if (!empty($currentUser['id'])) {
        $metrics['notifications_unread'] = get_unread_notifications_count((int) $currentUser['id']);
    }

    if (db_table_exists('api_request_logs')) {
        $stmt = db()->query("SELECT COUNT(*) FROM api_request_logs WHERE created_at >= (NOW() - INTERVAL 24 HOUR)");
        $metrics['api_24h_total'] = (int) $stmt->fetchColumn();
    }

    if (db_table_exists('request_throttles')) {
        $stmt = db()->query("SELECT COUNT(*) FROM request_throttles WHERE blocked_until IS NOT NULL AND blocked_until > NOW()");
        $metrics['throttle_active_blocks'] = (int) $stmt->fetchColumn();
    }
} catch (Throwable $e) {
    error_log('dashboard metrics error: ' . $e->getMessage());
}

$modules = function_exists('kirpi_list_modules') ? kirpi_list_modules() : [];
$metrics['enabled_modules'] = count(array_filter($modules, static fn(array $m): bool => !empty($m['enabled'])));

$uploadPath = BASE_PATH . '/uploads/avatars';
$apiEnabled = env_bool('API_ENABLED', true);
if (function_exists('kirpi_setting_bool')) {
    $apiEnabled = kirpi_setting_bool('api.enabled', $apiEnabled);
}

$checks = [
    [
        'ok' => true,
        'title' => 'Front controller',
        'detail' => 'index.php route akisi calisiyor.',
    ],
    [
        'ok' => db_table_exists('users'),
        'title' => 'Database schema',
        'detail' => db_table_exists('users') ? 'Temel tablolar ulasilabilir.' : 'Temel tablolar eksik gorunuyor.',
    ],
    [
        'ok' => is_dir($uploadPath) && is_writable($uploadPath),
        'title' => 'Upload klasoru',
        'detail' => (is_dir($uploadPath) && is_writable($uploadPath)) ? 'Avatar dizini yazilabilir.' : 'uploads/avatars yazma izni kontrol edilmeli.',
    ],
    [
        'ok' => $apiEnabled,
        'title' => 'API durumu',
        'detail' => $apiEnabled ? 'API aktif durumda.' : 'API kapali durumda.',
    ],
    [
        'ok' => function_exists('kirpi_throttle_enabled') ? kirpi_throttle_enabled() : false,
        'title' => 'Throttle korumasi',
        'detail' => (function_exists('kirpi_throttle_enabled') && kirpi_throttle_enabled()) ? 'Rate limit korumasi aktif.' : 'Throttle devre disi.',
    ],
];
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Kirpi Core</div>
                <h2 class="page-title">Dashboard</h2>
                <div class="text-secondary mt-1">
                    Core sistem ozeti ve canli saglik durumu.
                </div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="<?php echo base_url('health/view'); ?>" class="btn btn-outline-primary">Health Metrics</a>
                    <a href="<?php echo base_url('settings/view'); ?>" class="btn btn-primary">Ayarlar</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-deck row-cards mb-3">
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader">Kullanicilar</div>
                        <div class="h1 mb-2"><?php echo (int) $metrics['user_total']; ?></div>
                        <div class="text-secondary">Aktif: <?php echo (int) $metrics['user_active']; ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader">Roller</div>
                        <div class="h1 mb-2"><?php echo (int) $metrics['role_total']; ?></div>
                        <div class="text-secondary">Yetki yapisi hazir</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader">Okunmamis Bildirim</div>
                        <div class="h1 mb-2"><?php echo (int) $metrics['notifications_unread']; ?></div>
                        <div class="text-secondary">Aktif kullanici bazli</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader">Moduller</div>
                        <div class="h1 mb-2"><?php echo (int) $metrics['enabled_modules']; ?></div>
                        <div class="text-secondary">Aktif modul sayisi</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-deck row-cards mb-3">
            <div class="col-sm-6 col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader">API Cagri (24s)</div>
                        <div class="h1 mb-2"><?php echo (int) $metrics['api_24h_total']; ?></div>
                        <div class="text-secondary">Son 24 saatte toplam API istek sayisi</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader">Aktif Throttle Blok</div>
                        <div class="h1 mb-2"><?php echo (int) $metrics['throttle_active_blocks']; ?></div>
                        <div class="text-secondary">Rate limit nedeniyle gecici bloklanan anahtarlar</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Sistem Kontrol Listesi</h3>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($checks as $check): ?>
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="status-dot status-dot-animated <?php echo !empty($check['ok']) ? 'bg-green' : 'bg-red'; ?> d-block"></span>
                            </div>
                            <div class="col text-truncate">
                                <span class="text-body d-block"><?php echo e((string) ($check['title'] ?? 'Kontrol')); ?></span>
                                <div class="d-block text-secondary text-truncate mt-n1">
                                    <?php echo e((string) ($check['detail'] ?? '')); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
