<?php

if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

function users_lang(string $key, ?string $default = null): string
{
    static $dictionary = null;

    if ($dictionary === null) {
        $dictionary = [
            'tr' => [
                // Genel Başlıklar ve Yönetim
                'system_management' => 'Sistem Yönetimi',
                'users' => 'Kullanıcılar',
                'new_user' => 'Yeni Kullanıcı',
                'edit_user' => 'Kullanıcı Düzenle',

                // Arama ve Filtreleme
                'search_placeholder' => 'Ad, e-posta veya rol ara...',
                'all_roles' => 'Tüm Roller',
                'all_statuses' => 'Tüm Durumlar',
                'active' => 'Aktif',
                'inactive' => 'Pasif',
                'status_inactive_suffix' => ' (Pasif)',

                // Tablo Başlıkları
                'table_user' => 'Kullanıcı',
                'table_role' => 'Rol',
                'table_status' => 'Durum',
                'table_created_at' => 'Oluşturulma',
                'no_records' => 'Kayıt bulunamadı.',

                // Form Alanları ve İpuçları
                'name_surname' => 'Ad Soyad',
                'email' => 'E-posta',
                'password' => 'Şifre',
                'password_repeat' => 'Şifre Tekrar',
                'new_password' => 'Yeni Şifre',
                'new_password_repeat' => 'Yeni Şifre Tekrar',
                'password_optional_placeholder' => 'Boş bırakılırsa değişmez',
                'password_optional_hint' => 'Şifreyi değiştirmek istemiyorsanız boş bırakın.',
                'profile_image' => 'Profil Görseli',
                'profile_image_hint' => 'JPG, PNG veya WEBP. Maksimum 2 MB.',
                'profile_image_replace_hint' => 'Yeni görsel seçerseniz mevcut görselin yerine geçer.',
                'user_active_switch' => 'Kullanıcı aktif olsun',
                'role' => 'Rol',
                'select_role' => 'Rol Seçin',
                'only_active_roles_hint' => 'Yalnızca aktif roller listelenir.',
                'passive_role_info_hint' => 'Pasif roller yeni atama için listelenmez. Mevcut pasif rol yalnızca bilgilendirme için gösterilir.',

                // Oturum ve Güvenlik Ayarları
                'session' => 'Oturum',
                'key' => 'Key',
                'lock_enabled' => 'Lock Aktif',
                'lock_disabled' => 'Lock Pasif',
                'drop_session' => 'Oturumu Sonlandır',
                'reset_key' => 'Key Sıfırlama',

                // Onay Mesajları
                'drop_session_confirm' => 'Bu kullanıcının aktif oturumları sonlandırılacak. Emin misiniz?',
                'reset_key_confirm' => 'Bu kullanıcının lock key ayarı sıfırlanacak ve oturum kilitleme pasif olacak. Emin misiniz?',
                'reset_key_list_confirm' => 'Bu kullanıcının lock key ayarı sıfırlanacak. Emin misiniz?',

                // Aksiyonlar
                'edit' => 'Düzenle',
                'cancel' => 'İptal',
                'save' => 'Kaydet',
                'update' => 'Güncelle',

                // Hata Mesajları
                'invalid_user_id' => 'Geçersiz kullanıcı ID.',
                'user_data_load_error' => 'Kullanıcı verileri yüklenemedi.',
            ],
            'en' => [
                'system_management' => 'System Management',
                'users' => 'Users',
                'new_user' => 'New User',
                'search_placeholder' => 'Search by name, email or role...',
                'all_roles' => 'All Roles',
                'all_statuses' => 'All Statuses',
                'active' => 'Active',
                'inactive' => 'Inactive',
                'status_inactive_suffix' => ' (Inactive)',
                'table_user' => 'User',
                'table_role' => 'Role',
                'table_status' => 'Status',
                'table_created_at' => 'Created At',
                'no_records' => 'No records found.',
                'edit' => 'Edit',
                'session' => 'Session',
                'key' => 'Key',
                'cancel' => 'Cancel',
                'save' => 'Save',
                'update' => 'Update',
                'edit_user' => 'Edit User',
                'invalid_user_id' => 'Invalid user ID.',
                'user_data_load_error' => 'User data could not be loaded.',
                'name_surname' => 'Full Name',
                'email' => 'Email',
                'password' => 'Password',
                'password_repeat' => 'Password Repeat',
                'new_password' => 'New Password',
                'new_password_repeat' => 'Repeat New Password',
                'password_optional_placeholder' => 'Leave empty to keep unchanged',
                'password_optional_hint' => 'Leave empty if you do not want to change the password.',
                'profile_image' => 'Profile Image',
                'profile_image_hint' => 'JPG, PNG or WEBP. Maximum 2 MB.',
                'profile_image_replace_hint' => 'If you upload a new image, it will replace the current one.',
                'user_active_switch' => 'Set user active',
                'role' => 'Role',
                'select_role' => 'Select Role',
                'only_active_roles_hint' => 'Only active roles are listed.',
                'passive_role_info_hint' => 'Inactive roles are not listed for new assignments. Current inactive role is shown for information only.',
                'lock_enabled' => 'Lock Enabled',
                'lock_disabled' => 'Lock Disabled',
                'drop_session' => 'Drop Session',
                'reset_key' => 'Reset Key',
                'drop_session_confirm' => 'Active sessions for this user will be terminated. Continue?',
                'reset_key_confirm' => 'User lock key will be reset and session lock will be disabled. Continue?',
                'reset_key_list_confirm' => 'User lock key will be reset. Continue?',
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
