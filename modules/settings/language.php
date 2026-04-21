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
                'system_management' => 'Sistem YÃ¶netimi',
                'settings' => 'Ayarlar',
                'session_data' => 'Session Verileri',
                'settings_table_missing' => 'Ayarlar tablosu kurulu deÄŸil. Kurulum iÃ§in setup veya db:install Ã§alÄ±ÅŸtÄ±rÄ±n.',
                'system_check' => 'Sistem Kontrol ve Eksik Kurulum',

                // Kurulum ve Schema
                'required_total_tables' => 'Beklenen Toplam Tablo',
                'missing_table' => 'Eksik Tablo',
                'missing_index_expected' => 'Eksik Ä°ndeks / Beklenen',
                'missing_column_expected' => 'Eksik Kolon / Beklenen',
                'install_missing' => 'Eksikleri Kur',
                'missing_schema_files' => 'Eksik tablo bulunan schema dosyalarÄ±:',
                'missing_indexes' => 'Eksik indeksler:',
                'missing_columns' => 'Eksik kolonlar:',
                'no_missing_schema' => 'Eksik tablo, kolon veya indeks yok. Sistem zaten tam.',
                'missing_installed' => 'Eksik tablo, kolon ve indeksler baÅŸarÄ±yla kuruldu.',
                'still_missing' => 'Kurulum denendi ancak halen eksikler var. LoglarÄ± kontrol edin.',
                'install_missing_error' => 'Eksik tablolar kurulurken bir hata oluÅŸtu.',

                // Uygulama AyarlarÄ±
                'application' => 'Uygulama',
                'application_name' => 'Uygulama AdÄ±',
                'api_status' => 'API Durumu',
                'rest_api_active' => 'REST API aktif',
                'api_disabled_hint' => 'KapalÄ± olduÄŸunda /api/v1/* endpointleri 503 dÃ¶ner.',
                'mail' => 'Mail',
                'mail_password_placeholder' => 'DeÄŸiÅŸtirmek iÃ§in yeni ÅŸifre girin',
                'password_defined' => 'Parola tanÄ±mlÄ± (gÃ¼venlik iÃ§in gÃ¶sterilmiyor).',
                'password_missing' => 'Parola tanÄ±mlÄ± deÄŸil.',
                'save_settings' => 'AyarlarÄ± Kaydet',

                // ModÃ¼l YÃ¶netimi
                'module_management' => 'ModÃ¼l YÃ¶netimi',
                'app_modules_missing' => 'app_modules tablosu hazÄ±r deÄŸil. Ayarlar ekranÄ±ndan Eksikleri Kur Ã§alÄ±ÅŸtÄ±rÄ±n.',
                'modules' => 'ModÃ¼ller',
                'name' => 'Ad',
                'version' => 'Versiyon',
                'order' => 'SÄ±ra',
                'dependency' => 'BaÄŸÄ±mlÄ±lÄ±k',
                'dependent_modules' => 'Kullanan ModÃ¼ller',
                'type' => 'Tip',
                'status' => 'Durum',
                'operation' => 'Ä°ÅŸlem',
                'module_not_found' => 'ModÃ¼l bulunamadÄ±.',
                'core' => 'Core',
                'plugin' => 'Plugin',
                'active' => 'Aktif',
                'passive' => 'Pasif',
                'locked' => 'Kilitle',
                'disable_confirm' => 'Bu modÃ¼l devre dÄ±ÅŸÄ± bÄ±rakÄ±lacak. Emin misiniz?',
                'enable_confirm' => 'Bu modÃ¼l aktif edilecek. Emin misiniz?',
                'disable_blocked_title_prefix' => 'Disable engelli. Aktif baÄŸÄ±mlÄ± modÃ¼ller: ',
                'dependent_module_exists' => 'BaÄŸÄ±mlÄ± ModÃ¼l Var',
                'disable' => 'Disable',
                'enable' => 'Enable',

                // API Test
                'api_test_center' => 'API Test Merkezi',
                'method' => 'Method',
                'endpoint' => 'Endpoint',
                'bearer_token' => 'Bearer Token',
                'json_body' => 'JSON Body (POST/PATCH/DELETE)',
                'send_request' => 'Ä°stek GÃ¶nder',
                'result' => 'SonuÃ§',
                'ready' => 'HazÄ±r',
                'request_not_sent' => 'HenÃ¼z istek gÃ¶nderilmedi.',
                'endpoint_empty_warning' => 'Endpoint boÅŸ olamaz.',
                'sending' => 'Ä°stek gÃ¶nderiliyor...',
                'pending' => 'Bekleniyor',
                'error' => 'Hata',
                'invalid_json_prefix' => 'JSON Body geÃ§ersiz: ',

                // Modallar ve Genel Mesajlar
                'session_modal_title' => 'Session Verileri',
                'session_mask_info' => 'Hassas alanlar otomatik olarak maskelenir.',
                'close' => 'Kapat',
                'csrf_failed' => 'GÃ¼venlik doÄŸrulamasÄ± baÅŸarÄ±sÄ±z oldu.',
                'settings_table_not_ready' => 'Ayarlar tablosu henÃ¼z kurulu deÄŸil.',

                // Hata MesajlarÄ±
                'app_name_required' => 'Uygulama adÄ± boÅŸ olamaz.',
                'mail_port_invalid' => 'MAIL_PORT sayÄ±sal ve pozitif olmalÄ±dÄ±r.',
                'mail_encryption_invalid' => 'MAIL_ENCRYPTION geÃ§ersiz.',
                'mail_from_invalid' => 'MAIL_FROM_ADDRESS geÃ§ersiz bir e-posta adresi.',
                'settings_updated' => 'Ayarlar baÅŸarÄ±yla gÃ¼ncellendi.',
                'settings_update_error' => 'Ayarlar gÃ¼ncellenirken bir hata oluÅŸtu.',
                'module_key_required' => 'ModÃ¼l anahtarÄ± zorunludur.',
                'module_update_failed' => 'ModÃ¼l durumu gÃ¼ncellenemedi.',
                'module_updated' => 'ModÃ¼l durumu gÃ¼ncellendi.',
                                'menu_management' => 'Menu Yonetimi',
                'menu_management_note' => 'Menu yapisi module.json icindeki menu tanimlarindan otomatik uretilir.',
                'fixed_menu_items' => 'Sabit Menu Ogeleri',
                'top_menu_items' => 'Ust Menu Ogeleri',
                'management_menu_items' => 'Yonetim Menu Ogeleri',
                'module' => 'Modul',
                'placement' => 'Yerlesim',
                'group' => 'Grup',
                'route' => 'Route',
                'description' => 'Aciklama',
                'no_menu_item' => 'Menu ogesi bulunamadi.',
                'menu_fixed_dashboard' => 'Her zaman ilk sirada yer alir.',
                'menu_fixed_management' => 'Her zaman son sirada yer alir.',
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
                                'menu_management' => 'Menu Management',
                'menu_management_note' => 'Menu structure is generated automatically from module.json menu definitions.',
                'fixed_menu_items' => 'Fixed Menu Items',
                'top_menu_items' => 'Top Menu Items',
                'management_menu_items' => 'Management Menu Items',
                'module' => 'Module',
                'placement' => 'Placement',
                'group' => 'Group',
                'route' => 'Route',
                'description' => 'Description',
                'no_menu_item' => 'No menu item found.',
                'menu_fixed_dashboard' => 'Always appears first.',
                'menu_fixed_management' => 'Always appears last.',
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

