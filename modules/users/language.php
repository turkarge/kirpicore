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
                'system_management' => 'Sistem Yonetimi',
                'users' => 'Kullanicilar',
                'new_user' => 'Yeni Kullanici',
                'search_placeholder' => 'Ad, e-posta veya rol ara...',
                'all_roles' => 'Tum Roller',
                'all_statuses' => 'Tum Durumlar',
                'active' => 'Aktif',
                'inactive' => 'Pasif',
                'status_inactive_suffix' => ' (Pasif)',
                'table_user' => 'Kullanici',
                'table_role' => 'Rol',
                'table_status' => 'Durum',
                'table_created_at' => 'Olusturulma',
                'no_records' => 'Kayit bulunamadi.',
                'edit' => 'Duzenle',
                'session' => 'Oturum',
                'key' => 'Key',
                'cancel' => 'Iptal',
                'save' => 'Kaydet',
                'update' => 'Guncelle',
                'edit_user' => 'Kullanici Duzenle',
                'invalid_user_id' => 'Gecersiz kullanici ID.',
                'user_data_load_error' => 'Kullanici verileri yuklenemedi.',
                'name_surname' => 'Ad Soyad',
                'email' => 'E-posta',
                'password' => 'Sifre',
                'password_repeat' => 'Sifre Tekrar',
                'new_password' => 'Yeni Sifre',
                'new_password_repeat' => 'Yeni Sifre Tekrar',
                'password_optional_placeholder' => 'Bos birakilirsa degismez',
                'password_optional_hint' => 'Sifreyi degistirmek istemiyorsaniz bos birakin.',
                'profile_image' => 'Profil Gorseli',
                'profile_image_hint' => 'JPG, PNG veya WEBP. Maksimum 2 MB.',
                'profile_image_replace_hint' => 'Yeni gorsel secerseniz mevcut gorselin yerine gecer.',
                'user_active_switch' => 'Kullanici aktif olsun',
                'role' => 'Rol',
                'select_role' => 'Rol Secin',
                'only_active_roles_hint' => 'Yalnizca aktif roller listelenir.',
                'passive_role_info_hint' => 'Pasif roller yeni atama icin listelenmez. Mevcut pasif rol yalnizca bilgilendirme icin gosterilir.',
                'lock_enabled' => 'Lock Aktif',
                'lock_disabled' => 'Lock Pasif',
                'drop_session' => 'Oturumu Dusur',
                'reset_key' => 'Key Sifirla',
                'drop_session_confirm' => 'Bu kullanicinin aktif oturumlari sonlandirilacak. Emin misiniz?',
                'reset_key_confirm' => 'Bu kullanicinin lock key ayari sifirlanacak ve oturum kilitleme pasif olacak. Emin misiniz?',
                'reset_key_list_confirm' => 'Bu kullanicinin lock key ayari sifirlanacak. Emin misiniz?',
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
