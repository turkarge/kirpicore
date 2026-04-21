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
                // Giriş Sayfası
                'login_title' => 'Giriş Yap',
                'login_heading' => 'Hesabınıza giriş yapın',
                'email' => 'E-posta adresi',
                'email_placeholder' => 'ornek@alanadi.com',
                'password' => 'Şifre',
                'password_placeholder' => 'Şifreniz',
                'show_password' => 'Şifreyi göster',
                'remember_me' => 'Bu cihazda beni hatırla',
                'login_button' => 'Giriş Yap',
                'login_other_account' => 'Farklı hesap ile giriş yap',
                'terms_accept_prefix' => 'Giriş yaparak',
                'terms_accept_link' => 'kullanım şartlarını',
                'terms_accept_suffix' => 'kabul etmiş olursunuz.',

                // Şifremi Unuttum
                'forgot_title' => 'Şifremi Unuttum',
                'forgot_heading' => 'Şifrenizi mi unuttunuz?',
                'forgot_description' => 'E-posta adresinizi girin. Şifre sıfırlama sürecini sonraki adımda bağlayacağız.',
                'forgot_send' => 'Sıfırlama Bağlantısı Gönder',
                'back_to_login' => 'Giriş ekranına dön',

                // Kullanım Şartları
                'terms_title' => 'Kullanım Şartları',
                'back_to_login_button' => 'Girişe Dön',
                'terms_h1' => '1. Genel Hükümler',
                'terms_p1' => 'Bu uygulamayı kullanan tüm kullanıcılar, sistemin güvenli ve yetkili kullanımından sorumludur.',
                'terms_h2' => '2. Hesap Güvenliği',
                'terms_p2' => 'Kullanıcılar, oturum bilgilerini korumakla yükümlüdür. Yetkisiz erişim şüphesi halinde sistem yöneticisine bilgi verilmelidir.',
                'terms_h3' => '3. Veri Kullanımı',
                'terms_p3' => 'Sistem üzerinde oluşturulan, görüntülenen veya işlenen tüm veriler kurum politikalarına ve ilgili mevzuata uygun şekilde kullanılmalıdır.',
                'terms_h4' => '4. Son Hüküm',
                'terms_p4' => 'Bu metin başlangıç sürümüdür. Nihai kullanım şartları daha sonra uygulamaya özel şekilde genişletilebilir.',

                // Oturum Kilidi (Lock)
                'lock_title' => 'Oturum Kilidi',
                'lock_info' => 'Oturum kilitlendi. Devam etmek için key girin.',
                'lock_key_label' => 'Key (4 haneli)',
                'unlock_button' => 'Kilidi Aç',

                // Hata ve Bilgilendirme Mesajları
                'csrf_failed' => 'Güvenlik doğrulaması başarısız oldu.',
                'csrf_failed_refresh' => 'Güvenlik doğrulaması başarısız oldu. Sayfayı yenileyip tekrar deneyin.',
                'email_password_required' => 'E-posta ve şifre alanları zorunludur.',
                'invalid_email' => 'Geçerli bir e-posta adresi girin.',
                'invalid_credentials' => 'E-posta veya şifre hatalı.',
                'role_inactive' => 'Bu kullanıcıya bağlı rol pasif durumda.',
                'login_success_redirect' => 'Giriş başarılı. Yönlendiriliyorsunuz.',
                'login_error' => 'Giriş işlemi sırasında bir hata oluştu.',
                'logout_success' => 'Oturum kapatıldı.',
                'invalid_session' => 'Geçerli bir oturum bulunamadı.',
                'session_already_open' => 'Oturum zaten açık.',
                'session_locked' => 'Oturum kilitlendi.',

                // Lock (Kilitleme) Hata ve İşlemleri
                'lock_infra_missing' => 'Oturum kilitleme altyapısı hazır değil. Ayarlar > Eksikleri Kur çalıştırın.',
                'lock_infra_not_ready' => 'Oturum kilitleme altyapısı hazır değil.',
                'lock_not_active' => 'Oturum kilitleme aktif değil. Profilinizden 4 haneli key tanımlayın.',
                'lock_error' => 'Oturum kilitlenirken bir hata oluştu.',
                'lock_disabled_session_opened' => 'Kilitleme ayarı devre dışı. Oturum açıldı.',
                'lock_opened' => 'Oturum kilidi açıldı.',
                'unlock_error' => 'Oturum kilidi açılırken bir hata oluştu.',
                'key_must_be_4_digits' => 'Key 4 haneli sayısal olmalıdır.',
                'key_wrong' => 'Key hatalı.',
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
