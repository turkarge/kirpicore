<?php

if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

function settings_lang(string $key, ?string $default = null): string
{
    static $dictionary = null;

    if ($dictionary === null) {
        $dictionary = [
            'tr' => [
                // Sistem ve Ayarlar
                'system_management' => 'Sistem Yönetimi',
                'settings' => 'Ayarlar',
                'session_data' => 'Session Verileri',
                'settings_table_missing' => 'Ayarlar tablosu kurulu değil. Kurulum için setup veya db:install çalıştırın.',
                'system_check' => 'Sistem Kontrol ve Eksik Kurulum',

                // Kurulum ve Schema
                'required_total_tables' => 'Beklenen Toplam Tablo',
                'missing_table' => 'Eksik Tablo',
                'missing_index_expected' => 'Eksik İndeks / Beklenen',
                'missing_column_expected' => 'Eksik Kolon / Beklenen',
                'install_missing' => 'Eksikleri Kur',
                'missing_schema_files' => 'Eksik tablo bulunan schema dosyaları:',
                'missing_indexes' => 'Eksik indeksler:',
                'missing_columns' => 'Eksik kolonlar:',
                'no_missing_schema' => 'Eksik tablo, kolon veya indeks yok. Sistem zaten tam.',
                'missing_installed' => 'Eksik tablo, kolon ve indeksler başarıyla kuruldu.',
                'still_missing' => 'Kurulum denendi ancak halen eksikler var. Logları kontrol edin.',
                'install_missing_error' => 'Eksik tablolar kurulurken bir hata oluştu.',

                // Uygulama Ayarları
                'application' => 'Uygulama',
                'application_name' => 'Uygulama Adı',
                'api_status' => 'API Durumu',
                'rest_api_active' => 'REST API aktif',
                'api_disabled_hint' => 'Kapalı olduğunda /api/v1/* endpointleri 503 döner.',
                'mail' => 'Mail',
                'mail_password_placeholder' => 'Değiştirmek için yeni şifre girin',
                'password_defined' => 'Parola tanımlı (güvenlik için gösterilmiyor).',
                'password_missing' => 'Parola tanımlı değil.',
                'save_settings' => 'Ayarları Kaydet',

                // Modül Yönetimi
                'module_management' => 'Modül Yönetimi',
                'app_modules_missing' => 'app_modules tablosu hazır değil. Ayarlar ekranından Eksikleri Kur çalıştırın.',
                'modules' => 'Modüller',
                'name' => 'Ad',
                'version' => 'Versiyon',
                'order' => 'Sıra',
                'dependency' => 'Bağımlılık',
                'dependent_modules' => 'Kullanan Modüller',
                'type' => 'Tip',
                'status' => 'Durum',
                'operation' => 'İşlem',
                'module_not_found' => 'Modül bulunamadı.',
                'core' => 'Core',
                'plugin' => 'Plugin',
                'active' => 'Aktif',
                'passive' => 'Pasif',
                'locked' => 'Kilitle',
                'disable_confirm' => 'Bu modül devre dışı bırakılacak. Emin misiniz?',
                'enable_confirm' => 'Bu modül aktif edilecek. Emin misiniz?',
                'disable_blocked_title_prefix' => 'Disable engelli. Aktif bağımlı modüller: ',
                'dependent_module_exists' => 'Bağımlı Modül Var',
                'disable' => 'Disable',
                'enable' => 'Enable',

                // API Test
                'api_test_center' => 'API Test Merkezi',
                'method' => 'Method',
                'endpoint' => 'Endpoint',
                'bearer_token' => 'Bearer Token',
                'json_body' => 'JSON Body (POST/PATCH/DELETE)',
                'send_request' => 'İstek Gönder',
                'result' => 'Sonuç',
                'ready' => 'Hazır',
                'request_not_sent' => 'Henüz istek gönderilmedi.',
                'endpoint_empty_warning' => 'Endpoint boş olamaz.',
                'sending' => 'İstek gönderiliyor...',
                'pending' => 'Bekleniyor',
                'error' => 'Hata',
                'invalid_json_prefix' => 'JSON Body geçersiz: ',

                // Modallar ve Genel Mesajlar
                'session_modal_title' => 'Session Verileri',
                'session_mask_info' => 'Hassas alanlar otomatik olarak maskelenir.',
                'close' => 'Kapat',
                'csrf_failed' => 'Güvenlik doğrulaması başarısız oldu.',
                'settings_table_not_ready' => 'Ayarlar tablosu henüz kurulu değil.',

                // Hata Mesajları
                'app_name_required' => 'Uygulama adı boş olamaz.',
                'mail_port_invalid' => 'MAIL_PORT sayısal ve pozitif olmalıdır.',
                'mail_encryption_invalid' => 'MAIL_ENCRYPTION geçersiz.',
                'mail_from_invalid' => 'MAIL_FROM_ADDRESS geçersiz bir e-posta adresi.',
                'settings_updated' => 'Ayarlar başarıyla güncellendi.',
                'settings_update_error' => 'Ayarlar güncellenirken bir hata oluştu.',
                'module_key_required' => 'Modül anahtarı zorunludur.',
                'module_update_failed' => 'Modül durumu güncellenemedi.',
                'module_updated' => 'Modül durumu güncellendi.',
                'module_update_error' => 'Modül durumu güncellenirken bir hata oluştu.',
            ],
            'en' => [
                'system_management' => 'System Management',
                'settings' => 'Settings',
                'session_data' => 'Session Data',
                'settings_table_missing' => 'Settings table is not installed. Run setup or db:install.',
                'system_check' => 'System Check and Missing Install',
                'required_total_tables' => 'Required Total Tables',
                'missing_table' => 'Missing Table',
                'missing_index_expected' => 'Missing Index / Required',
                'missing_column_expected' => 'Missing Column / Required',
                'install_missing' => 'Install Missing',
                'missing_schema_files' => 'Schema files with missing tables:',
                'missing_indexes' => 'Missing indexes:',
                'missing_columns' => 'Missing columns:',
                'application' => 'Application',
                'application_name' => 'Application Name',
                'api_status' => 'API Status',
                'rest_api_active' => 'REST API active',
                'api_disabled_hint' => 'When disabled, /api/v1/* endpoints return 503.',
                'mail' => 'Mail',
                'mail_password_placeholder' => 'Enter new password to change',
                'password_defined' => 'Password is set (hidden for security).',
                'password_missing' => 'Password is not set.',
                'save_settings' => 'Save Settings',
                'module_management' => 'Module Management',
                'app_modules_missing' => 'app_modules table is not ready. Run Install Missing from settings page.',
                'modules' => 'Modules',
                'name' => 'Name',
                'version' => 'Version',
                'order' => 'Order',
                'dependency' => 'Dependency',
                'dependent_modules' => 'Dependent Modules',
                'type' => 'Type',
                'status' => 'Status',
                'operation' => 'Operation',
                'module_not_found' => 'No module found.',
                'core' => 'Core',
                'plugin' => 'Plugin',
                'active' => 'Active',
                'passive' => 'Passive',
                'locked' => 'Locked',
                'disable_confirm' => 'This module will be disabled. Continue?',
                'enable_confirm' => 'This module will be enabled. Continue?',
                'disable_blocked_title_prefix' => 'Disable blocked. Active dependent modules: ',
                'dependent_module_exists' => 'Has Dependents',
                'disable' => 'Disable',
                'enable' => 'Enable',
                'api_test_center' => 'API Test Center',
                'method' => 'Method',
                'endpoint' => 'Endpoint',
                'bearer_token' => 'Bearer Token',
                'json_body' => 'JSON Body (POST/PATCH/DELETE)',
                'send_request' => 'Send Request',
                'result' => 'Result',
                'ready' => 'Ready',
                'request_not_sent' => 'No request sent yet.',
                'endpoint_empty_warning' => 'Endpoint cannot be empty.',
                'sending' => 'Sending request...',
                'pending' => 'Pending',
                'error' => 'Error',
                'invalid_json_prefix' => 'Invalid JSON Body: ',
                'session_modal_title' => 'Session Data',
                'session_mask_info' => 'Sensitive fields are masked automatically.',
                'close' => 'Close',
                'csrf_failed' => 'Security validation failed.',
                'settings_table_not_ready' => 'Settings table is not installed yet.',
                'app_name_required' => 'Application name cannot be empty.',
                'mail_port_invalid' => 'MAIL_PORT must be numeric and positive.',
                'mail_encryption_invalid' => 'MAIL_ENCRYPTION is invalid.',
                'mail_from_invalid' => 'MAIL_FROM_ADDRESS is not a valid email address.',
                'settings_updated' => 'Settings updated successfully.',
                'settings_update_error' => 'An error occurred while updating settings.',
                'module_key_required' => 'Module key is required.',
                'module_update_failed' => 'Module status could not be updated.',
                'module_updated' => 'Module status updated.',
                'module_update_error' => 'An error occurred while updating module status.',
                'no_missing_schema' => 'No missing tables, columns, or indexes. System is complete.',
                'missing_installed' => 'Missing tables, columns, and indexes installed successfully.',
                'still_missing' => 'Installation attempted but some items are still missing. Check logs.',
                'install_missing_error' => 'An error occurred while installing missing schema.',
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
