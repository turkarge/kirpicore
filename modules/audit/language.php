<?php

if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

function audit_lang(string $key, ?string $default = null): string
{
    static $dictionary = null;

    if ($dictionary === null) {
        $dictionary = [
            'tr' => [
                // Genel Başlıklar
                'system_management' => 'Sistem Yönetimi',
                'audit_log' => 'Audit Log',
                'records' => 'Audit Kayıtları',

                // Filtreleme ve Arama
                'filters' => 'Filtreler',
                'status' => 'Status',
                'all' => 'Tüm',
                'module' => 'Module',
                'action' => 'Action',
                'user_id' => 'User ID',

                // Tablo Başlıkları
                'date' => 'Tarih',
                'user' => 'Kullanıcı',
                'route' => 'Route',
                'ip' => 'IP',
                'detail' => 'Detay',
                'view' => 'Gör',
                'no_records' => 'Kayıt bulunamadı.',

                // Durum ve Hata Mesajları
                'table_missing' => 'Audit log tablosu kurulu değil. Kurulum için setup veya db:install çalıştırın.',
                'table_missing_short' => 'Audit log tablosu henüz kurulu değil.',
                'table_waiting' => 'Audit tablosu hazır olduğunda liste burada görünecek.',
                'load_error' => 'Audit kayıtları yüklenirken bir hata oluştu.',
                'failed' => 'failed',
            ],
            'en' => [
                'system_management' => 'System Management',
                'audit_log' => 'Audit Log',
                'table_missing' => 'Audit log table is not installed. Run setup or db:install.',
                'filters' => 'Filters',
                'status' => 'Status',
                'all' => 'All',
                'module' => 'Module',
                'action' => 'Action',
                'user_id' => 'User ID',
                'records' => 'Audit Records',
                'table_waiting' => 'The list will appear here when audit table is ready.',
                'table_missing_short' => 'Audit log table is not installed yet.',
                'date' => 'Date',
                'user' => 'User',
                'route' => 'Route',
                'ip' => 'IP',
                'detail' => 'Detail',
                'no_records' => 'No records found.',
                'view' => 'View',
                'failed' => 'failed',
                'load_error' => 'An error occurred while loading audit records.',
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
