<?php

if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

function health_lang(string $key, ?string $default = null): string
{
    static $dictionary = null;

    if ($dictionary === null) {
        $dictionary = [
            'tr' => [
                'system_management' => 'Sistem Yonetimi',
                'health_metrics' => 'Health + Metrics',
                'system_matrix' => 'Sistem Matrix',
                'last_check' => 'Last Check',
                'component' => 'Bilesen',
                'status' => 'Status',
                'latency' => 'Latency',
                'detail' => 'Detay',
                'db_connection_ok' => 'Baglanti basarili',
                'db_query_failed' => 'DB sorgusu basarisiz',
                'queue_table_missing' => 'Queue tablosu yok',
                'queue_metrics_unreadable' => 'Queue metrikleri okunamadi',
                'mail_host_empty' => 'MAIL_HOST bos',
                'mail_host_defined_prefix' => 'SMTP host tanimli: ',
                'backup_table_missing' => 'Backup tablosu yok',
                'backup_metrics_unreadable' => 'Backup metrikleri okunamadi',
                'disk_unreadable' => 'Disk bilgisi okunamadi',
                'throttle_disabled_or_missing' => 'Throttle devre disi veya tablo yok',
                'throttle_metrics_unreadable' => 'Throttle metrikleri okunamadi',
            ],
            'en' => [
                'system_management' => 'System Management',
                'health_metrics' => 'Health + Metrics',
                'system_matrix' => 'System Matrix',
                'last_check' => 'Last Check',
                'component' => 'Component',
                'status' => 'Status',
                'latency' => 'Latency',
                'detail' => 'Detail',
                'db_connection_ok' => 'Connection successful',
                'db_query_failed' => 'Database query failed',
                'queue_table_missing' => 'Queue table is missing',
                'queue_metrics_unreadable' => 'Queue metrics could not be read',
                'mail_host_empty' => 'MAIL_HOST is empty',
                'mail_host_defined_prefix' => 'SMTP host defined: ',
                'backup_table_missing' => 'Backup table is missing',
                'backup_metrics_unreadable' => 'Backup metrics could not be read',
                'disk_unreadable' => 'Disk info could not be read',
                'throttle_disabled_or_missing' => 'Throttle is disabled or table is missing',
                'throttle_metrics_unreadable' => 'Throttle metrics could not be read',
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
