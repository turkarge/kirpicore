<?php

if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

function security_lang(string $key, ?string $default = null): string
{
    static $dictionary = null;

    if ($dictionary === null) {
        $dictionary = [
            'tr' => [
                'check_app_env_name' => 'Uygulama ortami',
                'check_app_env_hint' => 'APP_ENV production olmalidir.',
                'check_debug_name' => 'Debug modu',
                'check_debug_hint' => 'Production ortaminda APP_DEBUG false olmalidir.',
                'check_proxy_name' => 'Proxy guven',
                'check_proxy_hint' => 'Reverse proxy kullaniminda APP_TRUST_PROXY true onerilir.',
                'check_web_setup_name' => 'Web setup',
                'check_web_setup_hint' => 'Kurulumdan sonra AUTO_WEB_SETUP=false yapin.',
                'check_setup_key_name' => 'Setup key',
                'check_setup_key_hint' => 'SETUP_KEY bos olmamalidir.',
                'check_session_secure_name' => 'Session secure cookie',
                'check_session_secure_hint' => 'HTTPS icin session.cookie_secure=1 olmalidir.',
                'check_session_samesite_name' => 'Session samesite',
                'check_session_samesite_hint' => 'session.cookie_samesite=Lax onerilir.',
                'enabled' => 'enabled',
                'disabled' => 'disabled',
                'configured' => 'configured',
                'empty' => 'empty',
                'page_pretitle' => 'Sistem Yonetimi',
                'page_title' => 'Guvenlik Izleme',
                'security_checks_title' => 'Guvenlik Kontrolleri',
                'col_check' => 'Kontrol',
                'col_value' => 'Deger',
                'col_status' => 'Durum',
                'col_note' => 'Not',
                'status_warn' => 'Uyari',
                'dirs_title' => 'Dosya ve Klasor Izinleri',
                'col_folder' => 'Klasor',
                'col_path' => 'Yol',
                'col_exists' => 'Var mi',
                'col_writable' => 'Yazilabilir mi',
                'col_perm' => 'Perm',
                'yes' => 'Evet',
                'no' => 'Hayir',
                'db_tables_title' => 'Veritabani Tablolari',
                'db_empty' => 'Tablo bulunamadi veya veritabani okunamadi.',
            ],
            'en' => [
                'check_app_env_name' => 'Application environment',
                'check_app_env_hint' => 'APP_ENV should be production.',
                'check_debug_name' => 'Debug mode',
                'check_debug_hint' => 'APP_DEBUG should be false in production.',
                'check_proxy_name' => 'Trusted proxy',
                'check_proxy_hint' => 'APP_TRUST_PROXY=true is recommended behind reverse proxy.',
                'check_web_setup_name' => 'Web setup',
                'check_web_setup_hint' => 'Set AUTO_WEB_SETUP=false after installation.',
                'check_setup_key_name' => 'Setup key',
                'check_setup_key_hint' => 'SETUP_KEY should not be empty.',
                'check_session_secure_name' => 'Session secure cookie',
                'check_session_secure_hint' => 'session.cookie_secure=1 should be set for HTTPS.',
                'check_session_samesite_name' => 'Session samesite',
                'check_session_samesite_hint' => 'session.cookie_samesite=Lax is recommended.',
                'enabled' => 'enabled',
                'disabled' => 'disabled',
                'configured' => 'configured',
                'empty' => 'empty',
                'page_pretitle' => 'System Management',
                'page_title' => 'Security Monitor',
                'security_checks_title' => 'Security Checks',
                'col_check' => 'Check',
                'col_value' => 'Value',
                'col_status' => 'Status',
                'col_note' => 'Note',
                'status_warn' => 'Warning',
                'dirs_title' => 'File and Folder Permissions',
                'col_folder' => 'Folder',
                'col_path' => 'Path',
                'col_exists' => 'Exists',
                'col_writable' => 'Writable',
                'col_perm' => 'Perm',
                'yes' => 'Yes',
                'no' => 'No',
                'db_tables_title' => 'Database Tables',
                'db_empty' => 'No table found or database could not be read.',
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
