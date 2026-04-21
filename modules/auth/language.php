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
                'login_title' => 'Giris Yap',
                'login_heading' => 'Hesabiniza giris yapin',
                'email' => 'E-posta adresi',
                'email_placeholder' => 'ornek@alanadi.com',
                'password' => 'Sifre',
                'forgot_password' => 'Sifremi unuttum',
                'password_placeholder' => 'Sifreniz',
                'show_password' => 'Sifreyi goster',
                'remember_me' => 'Bu cihazda beni hatirla',
                'login_button' => 'Giris Yap',
                'terms_accept_prefix' => 'Giris yaparak',
                'terms_accept_link' => 'kullanim sartlarini',
                'terms_accept_suffix' => 'kabul etmis olursunuz.',
                'lock_title' => 'Oturum Kilidi',
                'lock_info' => 'Oturum kilitlendi. Devam etmek icin key girin.',
                'lock_key_label' => 'Key (4 haneli)',
                'unlock_button' => 'Kilidi Ac',
                'login_other_account' => 'Farkli hesap ile giris yap',
                'forgot_title' => 'Sifremi Unuttum',
                'forgot_heading' => 'Sifrenizi mi unuttunuz?',
                'forgot_description' => 'E-posta adresinizi girin. Sifre sifirlama surecini sonraki adimda baglayacagiz.',
                'forgot_send' => 'Sifirlama Baglantisi Gonder',
                'back_to_login' => 'Giris ekranina don',
                'terms_title' => 'Kullanim Sartlari',
                'back_to_login_button' => 'Girise Don',
                'terms_h1' => '1. Genel Hukumler',
                'terms_p1' => 'Bu uygulamayi kullanan tum kullanicilar, sistemin guvenli ve yetkili kullanimindan sorumludur.',
                'terms_h2' => '2. Hesap Guvenligi',
                'terms_p2' => 'Kullanicilar, oturum bilgilerini korumakla yukumludur. Yetkisiz erisim suphesi halinde sistem yoneticisine bilgi verilmelidir.',
                'terms_h3' => '3. Veri Kullanimi',
                'terms_p3' => 'Sistem uzerinde olusturulan, goruntulenen veya islenen tum veriler kurum politikalarina ve ilgili mevzuata uygun sekilde kullanilmalidir.',
                'terms_h4' => '4. Son Hukum',
                'terms_p4' => 'Bu metin baslangic surumudur. Nihai kullanim sartlari daha sonra uygulamaya ozel sekilde genisletilebilir.',
                'csrf_failed_refresh' => 'Guvenlik dogrulamasi basarisiz oldu. Sayfayi yenileyip tekrar deneyin.',
                'email_password_required' => 'E-posta ve sifre alanlari zorunludur.',
                'invalid_email' => 'Gecerli bir e-posta adresi girin.',
                'invalid_credentials' => 'E-posta veya sifre hatali.',
                'role_inactive' => 'Bu kullaniciya bagli rol pasif durumda.',
                'login_success_redirect' => 'Giris basarili. Yonlendiriliyorsunuz.',
                'login_error' => 'Giris islemi sirasinda bir hata olustu.',
                'csrf_failed' => 'Guvenlik dogrulamasi basarisiz oldu.',
                'logout_success' => 'Oturum kapatildi.',
                'invalid_session' => 'Gecerli bir oturum bulunamadi.',
                'lock_infra_missing' => 'Oturum kilitleme altyapisi hazir degil. Ayarlar > Eksikleri Kur calistirin.',
                'lock_not_active' => 'Oturum kilitleme aktif degil. Profilinizden 4 haneli key tanimlayin.',
                'session_locked' => 'Oturum kilitlendi.',
                'lock_error' => 'Oturum kilitlenirken bir hata olustu.',
                'session_already_open' => 'Oturum zaten acik.',
                'key_must_be_4_digits' => 'Key 4 haneli sayisal olmalidir.',
                'lock_infra_not_ready' => 'Oturum kilitleme altyapisi hazir degil.',
                'lock_disabled_session_opened' => 'Kilitleme ayari devre disi. Oturum acildi.',
                'key_wrong' => 'Key hatali.',
                'lock_opened' => 'Oturum kilidi acildi.',
                'unlock_error' => 'Oturum kilidi acilirken bir hata olustu.',
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
                'login_other_account' => 'Sign in with another account',
                'forgot_title' => 'Forgot Password',
                'forgot_heading' => 'Forgot your password?',
                'forgot_description' => 'Enter your email address. We will start password reset in the next step.',
                'forgot_send' => 'Send Reset Link',
                'back_to_login' => 'Back to login',
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
