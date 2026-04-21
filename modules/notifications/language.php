<?php

if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

function notifications_lang(string $key, ?string $default = null): string
{
    static $dictionary = null;

    if ($dictionary === null) {
        $dictionary = [
            'tr' => [
                // Genel BaÅŸlÄ±klar
                'communication_center' => 'Ä°letiÅŸim Merkezi',
                'notifications' => 'Bildirimler',
                'settings' => 'Ayarlar',

                // Bildirim Listesi
                'search_placeholder' => 'BaÅŸlÄ±k veya mesaj ara...',
                'all_statuses' => 'TÃ¼m Durumlar',
                'status_unread' => 'OkunmadÄ±',
                'status_read' => 'Okundu',
                'table_notification' => 'Bildirim',
                'table_channel' => 'Kanal',
                'table_status' => 'Durum',
                'table_date' => 'Tarih',
                'no_records' => 'KayÄ±t bulunamadÄ±.',
                'mark_read' => 'Okundu Yap',
                'mark_all_read' => 'TÃ¼mÃ¼nÃ¼ Okundu Yap',

                // Bildirim AyarlarÄ±
                'settings_center' => 'Bildirim Merkezi',
                'settings_title' => 'Bildirim AyarlarÄ±',
                'back_to_list' => 'Listeye DÃ¶n',
                'email_enabled' => 'E-posta bildirimleri aÃ§Ä±k olsun',
                'in_app_enabled' => 'Uygulama iÃ§i bildirimler aÃ§Ä±k olsun',
                'save_settings' => 'AyarlarÄ± Kaydet',
                'default_channel' => 'in_app',

                // Hata ve Bilgilendirme MesajlarÄ±
                'tables_missing' => 'Bildirim tablolarÄ± henÃ¼z kurulu deÄŸil. Ã–nce modules/notifications/database/schema.sql dosyasÄ±nÄ± Ã§alÄ±ÅŸtÄ±rÄ±n.',
                'table_missing_short' => 'Bildirim tablosu henÃ¼z kurulu deÄŸil.',
                'table_waiting' => 'Bildirim tablosu hazÄ±r olduÄŸunda liste burada gÃ¶rÃ¼necek.',
                'settings_table_missing' => 'Bildirim ayarlarÄ± tablosu henÃ¼z kurulu deÄŸil. Ã–nce database/notifications.sql dosyasÄ±nÄ± Ã§alÄ±ÅŸtÄ±rÄ±n.',
                'table_not_ready' => 'Bildirim tablosu henÃ¼z kurulu deÄŸil.',
                'settings_table_not_ready' => 'Bildirim ayarlarÄ± tablosu henÃ¼z kurulu deÄŸil.',

                // Aksiyon ve Hata Bildirimleri
                'csrf_failed' => 'GÃ¼venlik doÄŸrulamasÄ± baÅŸarÄ±sÄ±z oldu.',
                'invalid_request' => 'GeÃ§ersiz istek.',
                'invalid_session' => 'GeÃ§ersiz kullanÄ±cÄ± oturumu.',
                'mark_read_success' => 'Bildirim okundu olarak iÅŸaretlendi.',
                'mark_read_error' => 'Bildirim gÃ¼ncellenirken bir hata oluÅŸtu.',
                'mark_all_read_success' => 'TÃ¼m bildirimler okundu olarak iÅŸaretlendi.',
                'mark_all_read_error' => 'Bildirimler gÃ¼ncellenirken bir hata oluÅŸtu.',
                'settings_update_success' => 'Bildirim ayarlarÄ± baÅŸarÄ±yla gÃ¼ncellendi.',
                'settings_update_error' => 'Bildirim ayarlarÄ± gÃ¼ncellenirken bir hata oluÅŸtu.',
                'list_load_error' => 'Bildirim listesi yÃ¼klenirken bir hata oluÅŸtu.',
                'nav_bell_aria' => 'Bildirimleri goster',
                'nav_new_badge' => 'Yeni',
                'nav_empty' => 'Henuz bildiriminiz bulunmuyor.',
                'nav_view_all' => 'Tum bildirimleri gor',
            ],
            'en' => [
                'communication_center' => 'Communication Center',
                'notifications' => 'Notifications',
                'settings' => 'Settings',
                'mark_all_read' => 'Mark All as Read',
                'tables_missing' => 'Notification tables are not installed yet. Run modules/notifications/database/schema.sql first.',
                'search_placeholder' => 'Search title or message...',
                'all_statuses' => 'All Statuses',
                'status_unread' => 'Unread',
                'status_read' => 'Read',
                'table_waiting' => 'The list will appear here once the notifications table is ready.',
                'settings_center' => 'Notification Center',
                'settings_title' => 'Notification Settings',
                'back_to_list' => 'Back to List',
                'settings_table_missing' => 'Notification settings table is not installed yet. Run database/notifications.sql first.',
                'email_enabled' => 'Enable email notifications',
                'in_app_enabled' => 'Enable in-app notifications',
                'save_settings' => 'Save Settings',
                'table_missing_short' => 'Notification table is not installed yet.',
                'table_notification' => 'Notification',
                'table_channel' => 'Channel',
                'table_status' => 'Status',
                'table_date' => 'Date',
                'no_records' => 'No records found.',
                'default_channel' => 'in_app',
                'mark_read' => 'Mark as Read',
                'csrf_failed' => 'Security validation failed.',
                'invalid_request' => 'Invalid request.',
                'invalid_session' => 'Invalid user session.',
                'table_not_ready' => 'Notification table is not installed yet.',
                'settings_table_not_ready' => 'Notification settings table is not installed yet.',
                'mark_read_success' => 'Notification marked as read.',
                'mark_read_error' => 'An error occurred while updating notification.',
                'mark_all_read_success' => 'All notifications marked as read.',
                'mark_all_read_error' => 'An error occurred while updating notifications.',
                'settings_update_success' => 'Notification settings updated successfully.',
                'settings_update_error' => 'An error occurred while updating notification settings.',
                'list_load_error' => 'An error occurred while loading notifications list.',
                'nav_bell_aria' => 'Show notifications',
                'nav_new_badge' => 'New',
                'nav_empty' => 'You have no notifications yet.',
                'nav_view_all' => 'View all notifications',
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

