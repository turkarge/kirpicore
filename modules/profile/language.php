<?php

if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

function profile_lang(string $key, ?string $default = null): string
{
    static $dictionary = null;

    if ($dictionary === null) {
        $dictionary = [
            'tr' => [
                // Hata ve Durum MesajlarÄ±
                'forbidden_title' => '403 - Yetkisiz EriÅŸim',
                'forbidden_message' => 'Profil bilgilerine eriÅŸilemedi.',
                'load_error_title' => '500 - Profil YÃ¼klenemedi',
                'load_error_message' => 'Profil verileri yÃ¼klenirken bir hata oluÅŸtu.',
                'no_role' => 'Rol Yok',
                'active' => 'Aktif',
                'passive' => 'Pasif',

                // Profil YÃ¶netimi
                'my_account' => 'HesabÄ±m',
                'profile' => 'Profil',
                'nav_user_menu' => 'Kullanici Menusu',
                'user_fallback' => 'Kullanici',
                'profile_info' => 'Profil Bilgileri',
                'name_surname' => 'Ad Soyad',
                'email' => 'E-posta',
                'new_password' => 'Yeni Åifre',
                'new_password_repeat' => 'Yeni Åifre Tekrar',
                'password_placeholder' => 'BoÅŸ bÄ±rakÄ±lÄ±rsa deÄŸiÅŸmez',
                'password_hint' => 'Åifre deÄŸiÅŸtirmek istemiyorsanÄ±z boÅŸ bÄ±rakÄ±n.',
                'avatar' => 'Profil GÃ¶rseli',
                'avatar_hint' => 'JPG, PNG veya WEBP. Maksimum 2 MB.',
                'update_profile' => 'Profili GÃ¼ncelle',

                // Oturum Kilitleme (Lock) AyarlarÄ±
                'lock_key_title' => 'Oturum Kilitleme Key',
                'lock_enabled' => 'Oturum kilitleme aktif',
                'lock_hint' => "Navbar'daki user-key ikonu ile ekranÄ± kilitleyebilirsiniz.",
                'new_key' => 'Yeni Key (4 hane)',
                'key_repeat' => 'Key Tekrar',
                'key_placeholder' => 'Ã–rnek: 1234',
                'save_key' => 'Key AyarÄ±nÄ± Kaydet',

                // API Token YÃ¶netimi (Super Admin)
                'api_tokens' => 'API Tokenleri',
                'api_token_management' => 'API Token YÃ¶netimi (Super Admin)',
                'token_once_warning' => 'Bu token sadece bir kez gÃ¶sterilir. GÃ¼venli bir yerde saklayÄ±n.',
                'token' => 'Token',
                'token_name' => 'Token Name',
                'expires_at' => 'Expires At',
                'unlimited' => 'SÄ±nÄ±rsÄ±z',
                'scopes' => 'Scopes',
                'validity' => 'GeÃ§erlilik',
                'scope' => 'Scope',
                'all_permissions' => 'TÃ¼m Yetki (*)',
                'profile_only' => 'Sadece Profil',
                'users_read' => 'Users Read',
                'users_manage' => 'Users Manage',
                'create_api_token' => 'API Token OluÅŸtur',

                // API Token Tablo BaÅŸlÄ±klarÄ±
                'created' => 'Created',
                'last_used' => 'Last Used',
                'expires' => 'Expires',
                'status' => 'Status',
                'revoked' => 'Revoked',
                'expired' => 'Expired',
                'active_en' => 'Active',

                // Aksiyonlar ve Kopyalama
                'copy' => 'Kopyala',
                'copy_title' => 'Kopyala',
                'revoke' => 'Revoke',
                'revoke_confirm' => 'Bu API token iptal edilecek. Emin misiniz?',
                'copy_disabled_title' => 'GÃ¼venlik nedeniyle sadece bu oturumda oluÅŸturulan tokenlar kopyalanabilir',
                'copy_not_allowed' => 'Bu token bu oturumda kopyalanamaz.',
                'copy_success' => 'Token panoya kopyalandÄ±.',
                'copy_error' => 'Token kopyalanamadÄ±.',

                // Hata ve BaÅŸarÄ± Bildirimleri (Profil & Ayarlar)
                'csrf_failed' => 'GÃ¼venlik doÄŸrulamasÄ± baÅŸarÄ±sÄ±z oldu.',
                'invalid_session' => 'GeÃ§ersiz kullanÄ±cÄ± oturumu.',
                'required_fields' => 'Ad soyad ve e-posta alanlarÄ± zorunludur.',
                'invalid_email' => 'GeÃ§erli bir e-posta adresi girin.',
                'password_min' => 'Yeni ÅŸifre en az 6 karakter olmalÄ±dÄ±r.',
                'password_mismatch' => 'Yeni ÅŸifreler uyuÅŸmuyor.',
                'user_not_found' => 'KullanÄ±cÄ± bulunamadÄ±.',
                'email_in_use' => 'Bu e-posta adresi baÅŸka bir kullanÄ±cÄ± tarafÄ±ndan kullanÄ±lÄ±yor.',
                'profile_updated' => 'Profil baÅŸarÄ±yla gÃ¼ncellendi.',
                'profile_update_error' => 'Profil gÃ¼ncellenirken bir hata oluÅŸtu.',
                'valid_session_required' => 'GeÃ§erli kullanÄ±cÄ± oturumu bulunamadÄ±.',
                'lock_infra_missing' => 'Oturum kilitleme altyapÄ±sÄ± hazÄ±r deÄŸil. Ayarlar > Eksikleri Kur Ã§alÄ±ÅŸtÄ±rÄ±n.',
                'key_format_error' => 'Key sadece rakam olmalÄ± ve 4 hane olmalÄ±dÄ±r.',
                'key_repeat_error' => 'Key tekrar alanÄ± uyuÅŸmuyor.',
                'key_required_for_enable' => 'Oturum kilitlemeyi aÃ§mak iÃ§in Ã¶nce bir key tanÄ±mlamalÄ±sÄ±nÄ±z.',
                'lock_settings_updated' => 'Oturum kilitleme ayarlarÄ± gÃ¼ncellendi.',
                'settings_update_error' => 'Ayarlar gÃ¼ncellenirken bir hata oluÅŸtu.',

                // Hata ve BaÅŸarÄ± Bildirimleri (API Token)
                'api_disabled_warning' => 'API ÅŸu an Ayarlar ekranÄ±ndan kapatÄ±ldÄ±.',
                'api_table_warning' => '`api_tokens` tablosu hazÄ±r deÄŸil. Ayarlar > Eksikleri Kur Ã§alÄ±ÅŸtÄ±rÄ±n.',
                'no_tokens' => 'API token kaydÄ± yok.',
                'super_admin_only_create' => 'Sadece Super Admin API token oluÅŸturabilir.',
                'api_disabled_token' => 'API devre dÄ±ÅŸÄ± olduÄŸu iÃ§in token oluÅŸturulamadÄ±.',
                'api_table_not_ready' => 'API token tablosu hazÄ±r deÄŸil. Ã–nce Eksikleri Kur Ã§alÄ±ÅŸtÄ±rÄ±n.',
                'token_create_failed' => 'API token oluÅŸturulamadÄ±.',
                'token_created_once' => 'API token oluÅŸturuldu. Profil sayfasÄ±nda bir kez gÃ¶sterilecek.',
                'token_create_error' => 'API token oluÅŸturulurken bir hata oluÅŸtu.',
                'super_admin_only_manage' => 'Sadece Super Admin API token yÃ¶netebilir.',
                'invalid_token_record' => 'GeÃ§ersiz token kaydÄ±.',
                'token_table_not_ready' => 'API token tablosu hazÄ±r deÄŸil.',
                'token_not_found_or_revoked' => 'Token bulunamadÄ± veya zaten iptal edilmiÅŸ.',
                'token_revoked' => 'API token iptal edildi.',
                'token_revoke_error' => 'Token iptal edilirken bir hata oluÅŸtu.',
            ],
            'en' => [
                'forbidden_title' => '403 - Unauthorized Access',
                'forbidden_message' => 'Profile information could not be accessed.',
                'load_error_title' => '500 - Profile Load Failed',
                'load_error_message' => 'An error occurred while loading profile data.',
                'no_role' => 'No Role',
                'active' => 'Active',
                'passive' => 'Passive',
                'my_account' => 'My Account',
                'profile' => 'Profile',
                'nav_user_menu' => 'User Menu',
                'user_fallback' => 'User',
                'profile_info' => 'Profile Information',
                'api_tokens' => 'API Tokens',
                'name_surname' => 'Full Name',
                'email' => 'Email',
                'new_password' => 'New Password',
                'new_password_repeat' => 'Repeat New Password',
                'password_placeholder' => 'Leave blank to keep unchanged',
                'password_hint' => 'Leave blank if you do not want to change password.',
                'avatar' => 'Profile Image',
                'avatar_hint' => 'JPG, PNG or WEBP. Maximum 2 MB.',
                'update_profile' => 'Update Profile',
                'api_token_management' => 'API Token Management (Super Admin)',
                'token_once_warning' => 'This token is shown only once. Store it securely.',
                'token' => 'Token',
                'copy' => 'Copy',
                'copy_title' => 'Copy',
                'token_name' => 'Token Name',
                'expires_at' => 'Expires At',
                'unlimited' => 'Unlimited',
                'scopes' => 'Scopes',
                'validity' => 'Validity',
                'scope' => 'Scope',
                'all_permissions' => 'Full Access (*)',
                'profile_only' => 'Profile Only',
                'users_read' => 'Users Read',
                'users_manage' => 'Users Manage',
                'create_api_token' => 'Create API Token',
                'api_disabled_warning' => 'API is currently disabled from Settings.',
                'api_table_warning' => '`api_tokens` table is not ready. Run Settings > Install Missing.',
                'no_tokens' => 'No API tokens found.',
                'created' => 'Created',
                'last_used' => 'Last Used',
                'expires' => 'Expires',
                'status' => 'Status',
                'revoked' => 'Revoked',
                'expired' => 'Expired',
                'active_en' => 'Active',
                'copy_disabled_title' => 'For security, only tokens created in this session can be copied',
                'revoke_confirm' => 'This API token will be revoked. Continue?',
                'revoke' => 'Revoke',
                'lock_key_title' => 'Session Lock Key',
                'lock_enabled' => 'Session lock enabled',
                'lock_hint' => 'You can lock the screen with the user-key icon in navbar.',
                'new_key' => 'New Key (4 digits)',
                'key_repeat' => 'Repeat Key',
                'key_placeholder' => 'Example: 1234',
                'save_key' => 'Save Key Settings',
                'copy_not_allowed' => 'This token cannot be copied in this session.',
                'copy_success' => 'Token copied to clipboard.',
                'copy_error' => 'Token could not be copied.',
                'csrf_failed' => 'Security validation failed.',
                'invalid_session' => 'Invalid user session.',
                'required_fields' => 'Full name and email are required.',
                'invalid_email' => 'Enter a valid email address.',
                'password_min' => 'New password must be at least 6 characters.',
                'password_mismatch' => 'New passwords do not match.',
                'user_not_found' => 'User not found.',
                'email_in_use' => 'This email is already used by another user.',
                'profile_updated' => 'Profile updated successfully.',
                'profile_update_error' => 'An error occurred while updating profile.',
                'valid_session_required' => 'Valid user session not found.',
                'lock_infra_missing' => 'Session lock infrastructure is not ready. Run Settings > Install Missing.',
                'key_format_error' => 'Key must be numeric and exactly 4 digits.',
                'key_repeat_error' => 'Key repeat does not match.',
                'key_required_for_enable' => 'You must define a key before enabling session lock.',
                'lock_settings_updated' => 'Session lock settings updated.',
                'settings_update_error' => 'An error occurred while updating settings.',
                'super_admin_only_create' => 'Only Super Admin can create API tokens.',
                'api_disabled_token' => 'API is disabled, token could not be created.',
                'api_table_not_ready' => 'API token table is not ready. Run Install Missing first.',
                'token_create_failed' => 'API token could not be created.',
                'token_created_once' => 'API token created. It will be shown once on profile page.',
                'token_create_error' => 'An error occurred while creating API token.',
                'super_admin_only_manage' => 'Only Super Admin can manage API tokens.',
                'invalid_token_record' => 'Invalid token record.',
                'token_table_not_ready' => 'API token table is not ready.',
                'token_not_found_or_revoked' => 'Token not found or already revoked.',
                'token_revoked' => 'API token revoked.',
                'token_revoke_error' => 'An error occurred while revoking token.',
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

