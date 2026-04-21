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
                'mail_center' => 'Mail Merkezi',
                'mail_test_status' => 'Mail Test ve Durum',
                'mail_configuration' => 'Mail Konfigurasyonu',
                'mail_templates' => 'Mail Sablonlari',
                'manage_templates' => 'Sablonlari Yonet',
                'back_to_mail_test' => 'Mail Teste Don',

                'check' => 'Kontrol',
                'status' => 'Durum',
                'defined' => 'Tanimli',
                'empty' => 'Bos',
                'invalid' => 'Gecersiz',
                'valid' => 'Gecerli',
                'ready' => 'Hazir',
                'missing' => 'Eksik',
                'sent' => 'Gonderildi',
                'failed' => 'Basarisiz',
                'error' => 'Hata',
                'is_active' => 'Aktif',
                'is_system' => 'Sistem',
                'custom' => 'Ozel',

                'send_test_email' => 'Test E-posta Gonder',
                'recipient_email' => 'Alici E-posta',
                'subject' => 'Konu',
                'message' => 'Mesaj',
                'default_subject' => 'Kirpi Core Test Maili',
                'default_message' => 'Bu mesaj Kirpi Core mail modulu testi icin gonderilmistir.',
                'send_test_button' => 'Test Maili Gonder',

                'recent_mail_logs' => 'Son Mail Loglari',
                'date' => 'Tarih',
                'recipient' => 'Alici',
                'transport' => 'Transport',
                'no_mail_logs' => 'Henuz mail logu yok.',

                'new_template' => 'Yeni Sablon',
                'template_list' => 'Sablon Listesi',
                'template_key' => 'Sablon Key',
                'template_name' => 'Sablon Adi',
                'html_body' => 'HTML Icerik',
                'placeholders' => 'Placeholders',
                'save_template' => 'Sablonu Kaydet',
                'cancel' => 'Iptal',
                'create_template' => 'Sablon Olustur',
                'update_template' => 'Sablonu Guncelle',
                'delete_template' => 'Sablonu Sil',
                'templates_empty' => 'Henuz sablon kaydi yok.',
                'template_tables_missing' => 'mail_templates tablosu kurulu degil. Ayarlar > Eksikleri Kur calistirin.',
                'template_key_format' => 'Kucuk harf/rakam ve ._- kullanin. Ornek: auth.password_reset',
                'template_vars_hint' => 'Kullanilabilir degiskenler: {{app_name}}, {{app_url}}, {{year}} ve sablona ozel degiskenler.',

                'csrf_failed' => 'Guvenlik dogrulamasi basarisiz oldu.',
                'required_fields' => 'Alici e-posta, konu ve mesaj zorunludur.',
                'send_failed_default' => 'Test maili gonderilemedi.',
                'send_success_default' => 'Test maili gonderildi.',
                'template_required' => 'Sablon alanlari zorunludur.',
                'template_key_invalid' => 'Sablon key formati gecersiz.',
                'template_created' => 'Sablon olusturuldu.',
                'template_updated' => 'Sablon guncellendi.',
                'template_deleted' => 'Sablon silindi.',
                'template_not_found' => 'Sablon bulunamadi.',
                'template_delete_blocked' => 'Sistem sablonlari silinemez.',
                'template_save_error' => 'Sablon kaydedilirken bir hata olustu.',
                'template_duplicate_key' => 'Bu sablon key zaten kullaniliyor.',
            ],
            'en' => [
                'mail_center' => 'Mail Center',
                'mail_test_status' => 'Mail Test and Status',
                'mail_configuration' => 'Mail Configuration',
                'mail_templates' => 'Mail Templates',
                'manage_templates' => 'Manage Templates',
                'back_to_mail_test' => 'Back to Mail Test',

                'check' => 'Check',
                'status' => 'Status',
                'defined' => 'Defined',
                'empty' => 'Empty',
                'invalid' => 'Invalid',
                'valid' => 'Valid',
                'ready' => 'Ready',
                'missing' => 'Missing',
                'sent' => 'Sent',
                'failed' => 'Failed',
                'error' => 'Error',
                'is_active' => 'Active',
                'is_system' => 'System',
                'custom' => 'Custom',

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
                'no_mail_logs' => 'No mail logs yet.',

                'new_template' => 'New Template',
                'template_list' => 'Template List',
                'template_key' => 'Template Key',
                'template_name' => 'Template Name',
                'html_body' => 'HTML Content',
                'placeholders' => 'Placeholders',
                'save_template' => 'Save Template',
                'cancel' => 'Cancel',
                'create_template' => 'Create Template',
                'update_template' => 'Update Template',
                'delete_template' => 'Delete Template',
                'templates_empty' => 'No templates yet.',
                'template_tables_missing' => 'mail_templates table is not installed. Run Settings > Install Missing.',
                'template_key_format' => 'Use lowercase/alphanumeric and ._-. Example: auth.password_reset',
                'template_vars_hint' => 'Available variables: {{app_name}}, {{app_url}}, {{year}} and template-specific variables.',

                'csrf_failed' => 'Security validation failed.',
                'required_fields' => 'Recipient email, subject and message are required.',
                'send_failed_default' => 'Test email could not be sent.',
                'send_success_default' => 'Test email sent.',
                'template_required' => 'Template fields are required.',
                'template_key_invalid' => 'Template key format is invalid.',
                'template_created' => 'Template created.',
                'template_updated' => 'Template updated.',
                'template_deleted' => 'Template deleted.',
                'template_not_found' => 'Template not found.',
                'template_delete_blocked' => 'System templates cannot be deleted.',
                'template_save_error' => 'An error occurred while saving template.',
                'template_duplicate_key' => 'This template key is already in use.',
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
