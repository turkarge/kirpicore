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
                // --- 1. Genel Başlıklar ve Sistem Yönetimi ---
                'system_management' => 'Sistem Yönetimi',
                'brand'             => 'Kirpi Core',
                'dashboard'         => 'Dashboard',
                'summary'           => 'Core sistem özeti ve canlı sağlık durumu.',
                'health_metrics'    => 'Health + Metrics',
                'settings'          => 'Ayarlar',
                'users'             => 'Kullanıcılar',
                'roles'             => 'Roller',
                'roles_hint'        => 'Yetki yapısı hazır',
                'active_prefix'     => 'Aktif: ',
                'modules'           => 'Modüller',
                'active_module_count' => 'Aktif modül sayısı',

                // --- 2. API ve Metrics ---
                'v1_title'               => 'KirpiCore API v1',
                'api_calls_24h'          => 'API Çağrı (24s)',
                'api_calls_24h_hint'     => 'Son 24 saatte toplam API istek sayısı',
                'active_throttle_blocks' => 'Aktif Throttle Blok',
                'throttle_blocks_hint'   => 'Rate limit nedeniyle geçici bloklanan anahtarlar',
                'api_status'             => 'API durumu',
                'api_on'                 => 'API aktif durumda.',
                'api_off'                => 'API kapalı durumda.',
                'throttle_protection'    => 'Throttle koruması',
                'throttle_on'            => 'Rate limit koruması aktif.',
                'throttle_off'           => 'Throttle devre dışı.',

                // --- 3. Kullanıcı ve Profil ---
                'my_account'          => 'Hesabım',
                'profile'             => 'Profil',
                'profile_info'        => 'Profil Bilgileri',
                'api_tokens'          => 'API Tokenleri',
                'name_surname'        => 'Ad Soyad',
                'email'               => 'E-posta',
                'new_password'        => 'Yeni Şifre',
                'new_password_repeat' => 'Yeni Şifre Tekrar',
                'avatar'              => 'Profil Görseli',
                'update_profile'      => 'Profili Güncelle',

                // --- 4. Bildirimler ve İletişim ---
                'communication_center' => 'İletişim Merkezi',
                'notifications'        => 'Bildirimler',
                'unread_notifications' => 'Okunmamış Bildirim',
                'mark_all_read'        => 'Tümünü Okundu Yap',
                'mail_center'          => 'Mail Merkezi',
                'mail_test_status'     => 'Mail Test ve Durum',
                'mail_configuration'   => 'Mail Konfigürasyonu',

                // --- 5. Backup, Restore ve Queue ---
                'backup_restore'   => 'Backup / Restore',
                'jobs_queue'       => 'Jobs Queue',
                'queue_operations' => 'Queue İşlemleri',

                // --- 6. Ortak Tablo ve Durum Metinleri ---
                'date'       => 'Tarih',
                'status'     => 'Durum',
                'detail'     => 'Detay',
                'actions'    => 'İşlemler',
                'no_records' => 'Kayıt bulunamadı.',
                'save'       => 'Kaydet',
                'delete'     => 'Sil',
                'cancel'     => 'İptal',
                'close'      => 'Kapat',

                // --- 7. Hata ve Uyarı Mesajları ---
                'csrf_failed'    => 'Güvenlik doğrulaması başarısız oldu.',
                'table_missing'  => 'Gerekli tablo kurulu değil. Kurulum için setup veya db:install çalıştırın.',
                'required_fields' => 'Zorunlu alanları doldurun.',
                'invalid_request' => 'Geçersiz istek.',
                'error_occurred' => 'Bir hata oluştu.',
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
