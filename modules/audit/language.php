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
                'system_management' => 'Sistem Yonetimi',
                'audit_log' => 'Audit Log',
                'table_missing' => 'Audit log tablosu kurulu degil. Kurulum icin setup veya db:install calistirin.',
                'filters' => 'Filtreler',
                'status' => 'Status',
                'all' => 'Tum',
                'module' => 'Module',
                'action' => 'Action',
                'user_id' => 'User ID',
                'records' => 'Audit Kayitlari',
                'table_waiting' => 'Audit tablosu hazir oldugunda liste burada gorunecek.',
                'table_missing_short' => 'Audit log tablosu henuz kurulu degil.',
                'date' => 'Tarih',
                'user' => 'Kullanici',
                'route' => 'Route',
                'ip' => 'IP',
                'detail' => 'Detay',
                'no_records' => 'Kayit bulunamadi.',
                'view' => 'Gor',
                'failed' => 'failed',
                'load_error' => 'Audit kayitlari yuklenirken bir hata olustu.',
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
