<?php

if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

function auth_lang(string $key, ?string $default = null): string
{
    static $dictionary = null;

    if ($dictionary === null) {
        $dictionary = [
            'tr' => [
                // GiriÅŸ SayfasÄ±
                'login_title' => 'GiriÅŸ Yap',
                'login_heading' => 'HesabÄ±nÄ±za giriÅŸ yapÄ±n',
                'email' => 'E-posta adresi',
                'email_placeholder' => 'ornek@alanadi.com',
                'password' => 'Åifre',
                'forgot_password' => 'Åifremi unuttum',
                'password_placeholder' => 'Åifreniz',
                'show_password' => 'Åifreyi gÃ¶ster',
                'remember_me' => 'Bu cihazda beni hatÄ±rla',
                'login_button' => 'GiriÅŸ Yap',
                'login_other_account' => 'FarklÄ± hesap ile giriÅŸ yap',
                'terms_accept_prefix' => 'GiriÅŸ yaparak',
                'terms_accept_link' => 'kullanÄ±m ÅŸartlarÄ±nÄ±',
                'terms_accept_suffix' => 'kabul etmiÅŸ olursunuz.',

                // Åifremi Unuttum
                'forgot_title' => 'Åifremi Unuttum',
                'forgot_heading' => 'Åifrenizi mi unuttunuz?',
                'forgot_description' => 'E-posta adresinizi girin. Åifre sÄ±fÄ±rlama sÃ¼recini sonraki adÄ±mda baÄŸlayacaÄŸÄ±z.',
                'forgot_send' => 'SÄ±fÄ±rlama BaÄŸlantÄ±sÄ± GÃ¶nder',
                'forgot_email_sent' => 'EÄŸer e-posta kayÄ±tlÄ±ysa ÅŸifre sÄ±fÄ±rlama baÄŸlantÄ±sÄ± gÃ¶nderildi.',
                'forgot_email_send_error' => 'Åifre sÄ±fÄ±rlama e-postasÄ± gÃ¶nderilemedi. Mail ayarlarÄ±nÄ± kontrol edin.',
                'forgot_table_missing' => 'Åifre sÄ±fÄ±rlama altyapÄ±sÄ± hazÄ±r deÄŸil. Ayarlar > Eksikleri Kur Ã§alÄ±ÅŸtÄ±rÄ±n.',
                'back_to_login' => 'GiriÅŸ ekranÄ±na dÃ¶n',
                'reset_title' => 'Åifre SÄ±fÄ±rla',
                'reset_heading' => 'Yeni ÅŸifre belirleyin',
                'reset_token_missing' => 'SÄ±fÄ±rlama baÄŸlantÄ±sÄ± eksik veya geÃ§ersiz.',
                'reset_token_invalid' => 'SÄ±fÄ±rlama baÄŸlantÄ±sÄ± geÃ§ersiz veya sÃ¼resi dolmuÅŸ.',
                'reset_password' => 'Yeni Åifre',
                'reset_password_confirm' => 'Yeni Åifre Tekrar',
                'reset_submit' => 'Åifreyi GÃ¼ncelle',
                'reset_success' => 'Åifreniz gÃ¼ncellendi. GiriÅŸ yapabilirsiniz.',
                'reset_error' => 'Åifre sÄ±fÄ±rlanÄ±rken bir hata oluÅŸtu.',
                'password_min_6' => 'Åifre en az 6 karakter olmalÄ±dÄ±r.',
                'password_mismatch' => 'Åifre alanlarÄ± uyuÅŸmuyor.',

                // KullanÄ±m ÅartlarÄ±
                'terms_title' => 'KullanÄ±m ÅartlarÄ±',
                'back_to_login_button' => 'GiriÅŸe DÃ¶n',
                'terms_h1' => '1. Genel HÃ¼kÃ¼mler',
                'terms_p1' => 'Bu uygulamayÄ± kullanan tÃ¼m kullanÄ±cÄ±lar, sistemin gÃ¼venli ve yetkili kullanÄ±mÄ±ndan sorumludur.',
                'terms_h2' => '2. Hesap GÃ¼venliÄŸi',
                'terms_p2' => 'KullanÄ±cÄ±lar, oturum bilgilerini korumakla yÃ¼kÃ¼mlÃ¼dÃ¼r. Yetkisiz eriÅŸim ÅŸÃ¼phesi halinde sistem yÃ¶neticisine bilgi verilmelidir.',
                'terms_h3' => '3. Veri KullanÄ±mÄ±',
                'terms_p3' => 'Sistem Ã¼zerinde oluÅŸturulan, gÃ¶rÃ¼ntÃ¼lenen veya iÅŸlenen tÃ¼m veriler kurum politikalarÄ±na ve ilgili mevzuata uygun ÅŸekilde kullanÄ±lmalÄ±dÄ±r.',
                'terms_h4' => '4. Son HÃ¼kÃ¼m',
                'terms_p4' => 'Bu metin baÅŸlangÄ±Ã§ sÃ¼rÃ¼mÃ¼dÃ¼r. Nihai kullanÄ±m ÅŸartlarÄ± daha sonra uygulamaya Ã¶zel ÅŸekilde geniÅŸletilebilir.',

                // Oturum Kilidi (Lock)
                'lock_title' => 'Oturum Kilidi',
                'lock_info' => 'Oturum kilitlendi. Devam etmek iÃ§in key girin.',
                'lock_key_label' => 'Key (4 haneli)',
                'unlock_button' => 'Kilidi AÃ§',
                'nav_lock_session' => 'Oturumu Kilitle',
                'nav_logout' => 'Cikis',

                // Hata ve Bilgilendirme MesajlarÄ±
                'csrf_failed' => 'GÃ¼venlik doÄŸrulamasÄ± baÅŸarÄ±sÄ±z oldu.',
                'csrf_failed_refresh' => 'GÃ¼venlik doÄŸrulamasÄ± baÅŸarÄ±sÄ±z oldu. SayfayÄ± yenileyip tekrar deneyin.',
                'email_password_required' => 'E-posta ve ÅŸifre alanlarÄ± zorunludur.',
                'invalid_email' => 'GeÃ§erli bir e-posta adresi girin.',
                'invalid_credentials' => 'E-posta veya ÅŸifre hatalÄ±.',
                'role_inactive' => 'Bu kullanÄ±cÄ±ya baÄŸlÄ± rol pasif durumda.',
                'login_success_redirect' => 'GiriÅŸ baÅŸarÄ±lÄ±. YÃ¶nlendiriliyorsunuz.',
                'login_error' => 'GiriÅŸ iÅŸlemi sÄ±rasÄ±nda bir hata oluÅŸtu.',
                'logout_success' => 'Oturum kapatÄ±ldÄ±.',
                'invalid_session' => 'GeÃ§erli bir oturum bulunamadÄ±.',
                'session_already_open' => 'Oturum zaten aÃ§Ä±k.',
                'session_locked' => 'Oturum kilitlendi.',

                // Lock (Kilitleme) Hata ve Ä°ÅŸlemleri
                'lock_infra_missing' => 'Oturum kilitleme altyapÄ±sÄ± hazÄ±r deÄŸil. Ayarlar > Eksikleri Kur Ã§alÄ±ÅŸtÄ±rÄ±n.',
                'lock_infra_not_ready' => 'Oturum kilitleme altyapÄ±sÄ± hazÄ±r deÄŸil.',
                'lock_not_active' => 'Oturum kilitleme aktif deÄŸil. Profilinizden 4 haneli key tanÄ±mlayÄ±n.',
                'lock_error' => 'Oturum kilitlenirken bir hata oluÅŸtu.',
                'lock_disabled_session_opened' => 'Kilitleme ayarÄ± devre dÄ±ÅŸÄ±. Oturum aÃ§Ä±ldÄ±.',
                'lock_opened' => 'Oturum kilidi aÃ§Ä±ldÄ±.',
                'unlock_error' => 'Oturum kilidi aÃ§Ä±lÄ±rken bir hata oluÅŸtu.',
                'key_must_be_4_digits' => 'Key 4 haneli sayÄ±sal olmalÄ±dÄ±r.',
                'key_wrong' => 'Key hatalÄ±.',
            ],
            'en' => [
                'login_title' => 'Sign In',
                'login_heading' => 'Sign in to your account',
                'email' => 'Email address',
                'email_placeholder' => 'example@domain.com',
                'password' => 'Password',
                'forgot_password' => 'Forgot password',
                'password_placeholder' => 'Your password',
                'show_password' => 'Show password',
                'remember_me' => 'Remember me on this device',
                'login_button' => 'Sign In',
                'terms_accept_prefix' => 'By signing in you accept',
                'terms_accept_link' => 'the terms of use',
                'terms_accept_suffix' => '.',
                'lock_title' => 'Session Lock',
                'lock_info' => 'Session is locked. Enter your key to continue.',
                'lock_key_label' => 'Key (4 digits)',
                'unlock_button' => 'Unlock',
                'nav_lock_session' => 'Lock Session',
                'nav_logout' => 'Logout',
                'login_other_account' => 'Sign in with another account',
                'forgot_title' => 'Forgot Password',
                'forgot_heading' => 'Forgot your password?',
                'forgot_description' => 'Enter your email address. We will start password reset in the next step.',
                'forgot_send' => 'Send Reset Link',
                'forgot_email_sent' => 'If the email exists, a reset link has been sent.',
                'forgot_email_send_error' => 'Reset email could not be sent. Check mail configuration.',
                'forgot_table_missing' => 'Password reset infrastructure is not ready. Run Settings > Install Missing.',
                'back_to_login' => 'Back to login',
                'reset_title' => 'Reset Password',
                'reset_heading' => 'Set a new password',
                'reset_token_missing' => 'Reset link is missing or invalid.',
                'reset_token_invalid' => 'Reset link is invalid or expired.',
                'reset_password' => 'New Password',
                'reset_password_confirm' => 'Repeat New Password',
                'reset_submit' => 'Update Password',
                'reset_success' => 'Your password has been updated. You can sign in now.',
                'reset_error' => 'An error occurred while resetting password.',
                'password_min_6' => 'Password must be at least 6 characters.',
                'password_mismatch' => 'Password fields do not match.',
                'terms_title' => 'Terms of Use',
                'back_to_login_button' => 'Back to Login',
                'terms_h1' => '1. General Terms',
                'terms_p1' => 'All users are responsible for secure and authorized system use.',
                'terms_h2' => '2. Account Security',
                'terms_p2' => 'Users must protect session credentials and report suspicious access.',
                'terms_h3' => '3. Data Usage',
                'terms_p3' => 'All data must be used in compliance with policies and regulations.',
                'terms_h4' => '4. Final Provision',
                'terms_p4' => 'This is an initial draft and can be expanded for application needs.',
                'csrf_failed_refresh' => 'Security validation failed. Refresh and try again.',
                'email_password_required' => 'Email and password are required.',
                'invalid_email' => 'Enter a valid email address.',
                'invalid_credentials' => 'Email or password is incorrect.',
                'role_inactive' => 'The role assigned to this user is inactive.',
                'login_success_redirect' => 'Login successful. Redirecting.',
                'login_error' => 'An error occurred during login.',
                'csrf_failed' => 'Security validation failed.',
                'logout_success' => 'Session ended.',
                'invalid_session' => 'No valid session found.',
                'lock_infra_missing' => 'Session lock infrastructure is not ready. Run Settings > Install Missing.',
                'lock_not_active' => 'Session lock is not active. Set a 4-digit key in profile.',
                'session_locked' => 'Session locked.',
                'lock_error' => 'An error occurred while locking session.',
                'session_already_open' => 'Session is already open.',
                'key_must_be_4_digits' => 'Key must be a 4-digit number.',
                'lock_infra_not_ready' => 'Session lock infrastructure is not ready.',
                'lock_disabled_session_opened' => 'Lock setting is disabled. Session opened.',
                'key_wrong' => 'Key is incorrect.',
                'lock_opened' => 'Session lock opened.',
                'unlock_error' => 'An error occurred while unlocking session.',
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

