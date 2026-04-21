<?php

if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

function queue_lang(string $key, ?string $default = null): string
{
    static $dictionary = null;

    if ($dictionary === null) {
        $dictionary = [
            'tr' => [
                'system_management' => 'Sistem Yonetimi',
                'jobs_queue' => 'Jobs Queue',
                'table_missing' => 'Queue tablosu kurulu degil. Kurulum icin setup veya db:install calistirin.',
                'queued' => 'Queued',
                'processing' => 'Processing',
                'completed' => 'Completed',
                'failed' => 'Failed',
                'queue_operations' => 'Queue Islemleri',
                'test_mail_recipient' => 'Test Mail Alici',
                'enqueue_mail_job' => 'Mail Job Kuyruga Ekle',
                'worker_run_once' => 'Worker Run Once',
                'retry_failed_jobs' => 'Failed Joblari Retry',
                'last_50_jobs' => 'Son 50 Job',
                'queue' => 'Queue',
                'type' => 'Type',
                'attempts' => 'Attempts',
                'status' => 'Status',
                'error' => 'Error',
                'date' => 'Tarih',
                'no_records' => 'Kayit bulunamadi.',
                'csrf_failed' => 'Guvenlik dogrulamasi basarisiz oldu.',
                'table_not_ready' => 'Queue tablosu henuz kurulu degil.',
                'invalid_email' => 'Gecerli bir e-posta adresi girin.',
                'test_subject' => 'Kirpi Queue Test Mail',
                'test_body_html' => '<p>Bu e-posta queue uzerinden gonderilmistir.</p>',
                'enqueue_success_prefix' => 'Mail job kuyruga eklendi. Job ID: ',
                'work_failed_default' => 'Queue job failed.',
                'work_processed_prefix' => 'Queue job calistirildi. Job ID: ',
                'queue_idle' => 'Queue idle.',
                'retry_success_prefix' => 'Retry icin guncellenen failed job sayisi: ',
            ],
            'en' => [
                'system_management' => 'System Management',
                'jobs_queue' => 'Jobs Queue',
                'table_missing' => 'Queue table is not installed. Run setup or db:install.',
                'queued' => 'Queued',
                'processing' => 'Processing',
                'completed' => 'Completed',
                'failed' => 'Failed',
                'queue_operations' => 'Queue Operations',
                'test_mail_recipient' => 'Test Mail Recipient',
                'enqueue_mail_job' => 'Enqueue Mail Job',
                'worker_run_once' => 'Worker Run Once',
                'retry_failed_jobs' => 'Retry Failed Jobs',
                'last_50_jobs' => 'Last 50 Jobs',
                'queue' => 'Queue',
                'type' => 'Type',
                'attempts' => 'Attempts',
                'status' => 'Status',
                'error' => 'Error',
                'date' => 'Date',
                'no_records' => 'No records found.',
                'csrf_failed' => 'Security validation failed.',
                'table_not_ready' => 'Queue table is not installed yet.',
                'invalid_email' => 'Please enter a valid email address.',
                'test_subject' => 'Kirpi Queue Test Mail',
                'test_body_html' => '<p>This email was sent via queue.</p>',
                'enqueue_success_prefix' => 'Mail job enqueued. Job ID: ',
                'work_failed_default' => 'Queue job failed.',
                'work_processed_prefix' => 'Queue job processed. Job ID: ',
                'queue_idle' => 'Queue idle.',
                'retry_success_prefix' => 'Failed jobs updated for retry: ',
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
