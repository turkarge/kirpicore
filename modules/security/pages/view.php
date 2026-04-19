<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

$checks = [];

$checks[] = [
    'name' => 'Uygulama ortami',
    'value' => APP_ENV,
    'ok' => APP_ENV === 'production',
    'hint' => 'APP_ENV production olmalidir.',
];

$checks[] = [
    'name' => 'Debug modu',
    'value' => APP_DEBUG ? 'true' : 'false',
    'ok' => APP_DEBUG === false,
    'hint' => 'Production ortaminda APP_DEBUG false olmalidir.',
];

$checks[] = [
    'name' => 'Proxy guven',
    'value' => APP_TRUST_PROXY ? 'true' : 'false',
    'ok' => APP_TRUST_PROXY === true,
    'hint' => 'Reverse proxy kullaniminda APP_TRUST_PROXY true onerilir.',
];

$checks[] = [
    'name' => 'Web setup',
    'value' => env_bool('AUTO_WEB_SETUP', true) ? 'enabled' : 'disabled',
    'ok' => env_bool('AUTO_WEB_SETUP', true) === false,
    'hint' => 'Kurulumdan sonra AUTO_WEB_SETUP=false yapin.',
];

$setupKey = (string) env('SETUP_KEY', '');
$checks[] = [
    'name' => 'Setup key',
    'value' => $setupKey !== '' ? 'configured' : 'empty',
    'ok' => $setupKey !== '',
    'hint' => 'SETUP_KEY bos olmamalidir.',
];

$checks[] = [
    'name' => 'Session secure cookie',
    'value' => ini_get('session.cookie_secure') === '1' ? 'enabled' : 'disabled',
    'ok' => ini_get('session.cookie_secure') === '1',
    'hint' => 'HTTPS icin session.cookie_secure=1 olmalidir.',
];

$checks[] = [
    'name' => 'Session samesite',
    'value' => (string) ini_get('session.cookie_samesite'),
    'ok' => strtolower((string) ini_get('session.cookie_samesite')) === 'lax',
    'hint' => 'session.cookie_samesite=Lax onerilir.',
];

$dirChecks = [];
$paths = [
    'uploads' => BASE_PATH . '/uploads',
    'uploads/avatars' => BASE_PATH . '/uploads/avatars',
    'logs' => BASE_PATH . '/logs',
    'storage' => BASE_PATH . '/storage',
];

foreach ($paths as $label => $path) {
    $exists = is_dir($path);
    $writable = $exists ? is_writable($path) : false;
    $perm = file_exists($path) ? substr(sprintf('%o', fileperms($path)), -4) : '----';

    $dirChecks[] = [
        'name' => $label,
        'path' => $path,
        'exists' => $exists,
        'writable' => $writable,
        'perm' => $perm,
    ];
}

$dbTables = [];
try {
    $stmt = db()->query("SHOW TABLES");
    $rows = $stmt->fetchAll(PDO::FETCH_NUM) ?: [];
    foreach ($rows as $row) {
        if (isset($row[0])) {
            $dbTables[] = (string) $row[0];
        }
    }
} catch (Throwable $e) {
    $dbTables = [];
}
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Sistem Yonetimi</div>
                <h2 class="page-title">Guvenlik Izleme</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Guvenlik Kontrolleri</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-striped">
                    <thead>
                    <tr>
                        <th>Kontrol</th>
                        <th>Deger</th>
                        <th>Durum</th>
                        <th>Not</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($checks as $check): ?>
                        <tr>
                            <td><?php echo e($check['name']); ?></td>
                            <td><code><?php echo e((string) $check['value']); ?></code></td>
                            <td>
                                <?php if ($check['ok']): ?>
                                    <span class="badge bg-green-lt">OK</span>
                                <?php else: ?>
                                    <span class="badge bg-red-lt">Uyari</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-secondary"><?php echo e($check['hint']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Dosya ve Klasor Izinleri</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-striped">
                    <thead>
                    <tr>
                        <th>Klasor</th>
                        <th>Yol</th>
                        <th>Var mi</th>
                        <th>Yazilabilir mi</th>
                        <th>Perm</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($dirChecks as $d): ?>
                        <tr>
                            <td><?php echo e($d['name']); ?></td>
                            <td><code><?php echo e($d['path']); ?></code></td>
                            <td><?php echo $d['exists'] ? '<span class="badge bg-green-lt">Evet</span>' : '<span class="badge bg-red-lt">Hayir</span>'; ?></td>
                            <td><?php echo $d['writable'] ? '<span class="badge bg-green-lt">Evet</span>' : '<span class="badge bg-red-lt">Hayir</span>'; ?></td>
                            <td><code><?php echo e($d['perm']); ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Veritabani Tablolari</h3>
            </div>
            <div class="card-body">
                <?php if (empty($dbTables)): ?>
                    <div class="text-secondary">Tablo bulunamadi veya veritabani okunamadi.</div>
                <?php else: ?>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($dbTables as $table): ?>
                            <span class="badge bg-blue-lt"><?php echo e($table); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
