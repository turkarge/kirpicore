<?php

if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

function mail_lang(string $key, ?string $default = null): string
{
    static $dictionary = null;

    if ($dictionary === null) {
        $dictionary = [
            'tr' => [
                // Genel Başlıklar
                'mail_center' => 'Mail Merkezi',
                'mail_test_status' => 'Mail Test ve Durum',
                'mail_configuration' => 'Mail Konfigürasyonu',

                // Durum ve Etiketler
                'check' => 'Kontrol',
                'status' => 'Durum',
                'defined' => 'Tanımlı',
                'empty' => 'Boş',
                'invalid' => 'Geçersiz',
                'valid' => 'Geçerli',
                'ready' => 'Hazır',
                'missing' => 'Eksik',
                'sent' => 'Gönderildi',
                'failed' => 'Başarısız',
                'error' => 'Hata',

                // Test Maili Formu
                'send_test_email' => 'Test E-posta Gönder',
                'recipient_email' => 'Alıcı E-posta',
                'subject' => 'Konu',
                'message' => 'Mesaj',
                'default_subject' => 'Kirpi Core Test Maili',
                'default_message' => 'Bu mesaj Kirpi Core mail modülü testi için gönderilmiştir.',
                'send_test_button' => 'Test Maili Gönder',

                // Loglar ve Tablo Başlıkları
                'recent_mail_logs' => 'Son Mail Logları',
                'date' => 'Tarih',
                'recipient' => 'Alıcı',
                'transport' => 'Transport',
                'no_mail_logs' => 'Henüz mail logu yok.',

                // Hata ve Başarı Mesajları
                'csrf_failed' => 'Güvenlik doğrulaması başarısız oldu.',
                'required_fields' => 'Alıcı e-posta, konu ve mesaj zorunludur.',
                'send_failed_default' => 'Test maili gönderilemedi.',
                'send_success_default' => 'Test maili gönderildi.',
            ],
            'en' => [
                'mail_center' => 'Mail Center',
                'mail_test_status' => 'Mail Test and Status',
                'mail_configuration' => 'Mail Configuration',
                'check' => 'Check',
                'status' => 'Status',
                'defined' => 'Defined',
                'empty' => 'Empty',
                'invalid' => 'Invalid',
                'valid' => 'Valid',
                'ready' => 'Ready',
                'missing' => 'Missing',
                'send_test_email' => 'Send Test Email',
                'recipient_email' => 'Recipient Email',
                'subject' => 'Subject',
                'message' => 'Message',
                'default_subject' => 'Kirpi Core Test Email',
                'default_message' => 'This message was sent for Kirpi Core mail module testing.',
                'send_test_button' => 'Send Test Email',
                'recent_mail_logs' => 'Recent Mail Logs',
                'date' => 'Date',
                'recipient' => 'Recipient',
                'transport' => 'Transport',
                'error' => 'Error',
                'no_mail_logs' => 'No mail logs yet.',
                'sent' => 'Sent',
                'failed' => 'Failed',
                'csrf_failed' => 'Security validation failed.',
                'required_fields' => 'Recipient email, subject and message are required.',
                'send_failed_default' => 'Test email could not be sent.',
                'send_success_default' => 'Test email sent.',
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
