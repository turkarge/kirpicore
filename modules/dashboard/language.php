<?php

if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

function dashboard_lang(string $key, ?string $default = null): string
{
    static $dictionary = null;

    if ($dictionary === null) {
        $dictionary = [
            'tr' => [
                'brand' => 'Kirpi Core',
                'dashboard' => 'Dashboard',
                'summary' => 'Core sistem ozeti ve canli saglik durumu.',
                'health_metrics' => 'Health Metrics',
                'settings' => 'Ayarlar',
                'users' => 'Kullanicilar',
                'active_prefix' => 'Aktif: ',
                'roles' => 'Roller',
                'roles_hint' => 'Yetki yapisi hazir',
                'unread_notifications' => 'Okunmamis Bildirim',
                'user_based_active' => 'Aktif kullanici bazli',
                'modules' => 'Moduller',
                'active_module_count' => 'Aktif modul sayisi',
                'api_calls_24h' => 'API Cagri (24s)',
                'api_calls_24h_hint' => 'Son 24 saatte toplam API istek sayisi',
                'active_throttle_blocks' => 'Aktif Throttle Blok',
                'throttle_blocks_hint' => 'Rate limit nedeniyle gecici bloklanan anahtarlar',
                'system_checklist' => 'Sistem Kontrol Listesi',
                'check_default' => 'Kontrol',
                'front_controller' => 'Front controller',
                'front_controller_ok' => 'index.php route akisi calisiyor.',
                'database_schema' => 'Database schema',
                'database_ok' => 'Temel tablolar ulasilabilir.',
                'database_missing' => 'Temel tablolar eksik gorunuyor.',
                'upload_folder' => 'Upload klasoru',
                'upload_ok' => 'Avatar dizini yazilabilir.',
                'upload_warn' => 'uploads/avatars yazma izni kontrol edilmeli.',
                'api_status' => 'API durumu',
                'api_on' => 'API aktif durumda.',
                'api_off' => 'API kapali durumda.',
                'throttle_protection' => 'Throttle korumasi',
                'throttle_on' => 'Rate limit korumasi aktif.',
                'throttle_off' => 'Throttle devre disi.',
                'about_title' => 'Kirpi Core Hakkinda',
                'about_app' => 'Uygulama',
                'about_env' => 'Ortam',
                'about_debug' => 'Debug',
                'about_debug_on' => 'Acik',
                'about_debug_off' => 'Kapali',
                'about_description' => 'Aciklama',
                'about_text' => 'Kirpi Core; moduler, hizli gelistirilebilir ve tekrar kullanilabilir PHP uygulamalari uretmek icin hazirlanmis cekirdek uygulama yapisidir.',
                'close' => 'Kapat',
            ],
            'en' => [
                'brand' => 'Kirpi Core',
                'dashboard' => 'Dashboard',
                'summary' => 'Core system summary and live health status.',
                'health_metrics' => 'Health Metrics',
                'settings' => 'Settings',
                'users' => 'Users',
                'active_prefix' => 'Active: ',
                'roles' => 'Roles',
                'roles_hint' => 'Permission structure ready',
                'unread_notifications' => 'Unread Notifications',
                'user_based_active' => 'Active user based',
                'modules' => 'Modules',
                'active_module_count' => 'Active module count',
                'api_calls_24h' => 'API Calls (24h)',
                'api_calls_24h_hint' => 'Total API requests in the last 24 hours',
                'active_throttle_blocks' => 'Active Throttle Blocks',
                'throttle_blocks_hint' => 'Temporarily blocked keys due to rate limits',
                'system_checklist' => 'System Checklist',
                'check_default' => 'Check',
                'front_controller' => 'Front controller',
                'front_controller_ok' => 'index.php route flow is working.',
                'database_schema' => 'Database schema',
                'database_ok' => 'Core tables are reachable.',
                'database_missing' => 'Core tables appear to be missing.',
                'upload_folder' => 'Upload folder',
                'upload_ok' => 'Avatar directory is writable.',
                'upload_warn' => 'Check write permission for uploads/avatars.',
                'api_status' => 'API status',
                'api_on' => 'API is enabled.',
                'api_off' => 'API is disabled.',
                'throttle_protection' => 'Throttle protection',
                'throttle_on' => 'Rate limit protection is enabled.',
                'throttle_off' => 'Throttle is disabled.',
                'about_title' => 'About Kirpi Core',
                'about_app' => 'Application',
                'about_env' => 'Environment',
                'about_debug' => 'Debug',
                'about_debug_on' => 'On',
                'about_debug_off' => 'Off',
                'about_description' => 'Description',
                'about_text' => 'Kirpi Core is a core application structure built for modular, rapidly developable, and reusable PHP applications.',
                'close' => 'Close',
            ],
        ];
    }

    $locale = strtolower((string) env('APP_LOCALE', 'tr'));
    if (!isset($dictionary[$locale])) {
        $locale = 'tr';
    }

    if (isset($dictionary[$locale][$key])) {
        return $dictionary[$locale][$key];
    }

    if (isset($dictionary['tr'][$key])) {
        return $dictionary['tr'][$key];
    }

    return $default ?? $key;
}
