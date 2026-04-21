# Mail Sablon Sistemi

KirpiCore mail altyapisinda artik sablon tabanli gonderim desteklenir.

## Genel Mimari

- Sablon tablasi: `mail_templates`
- Core render/gonderim fonksiyonlari: `core/mail.php`
- Yonetim ekrani: `mail/templates`

Sablon secenekleri:

- `template_key` (benzersiz teknik anahtar)
- `name` (gosterim adi)
- `subject` (placeholder destekli konu)
- `html_body` (placeholder destekli HTML govde)
- `is_active` (aktif/pasif)
- `is_system` (core sablonu, silinemez)

## Varsayilan Core Sablonlari

Ilk kurulumda/ilk kullanimda sistem tarafindan senkronlanan ana sablonlar:

- `auth.password_reset`
- `queue.test_mail`
- `mail.test_manual`
- `users.session_dropped`
- `users.lock_key_reset`

Not:

- `is_system=1` sablonlar silinemez.
- Isterseniz konu/govde alanlari guncellenebilir.

## Placeholder Kurallari

Standart degiskenler:

- `{{app_name}}`
- `{{app_url}}`
- `{{year}}`

Sablon-ozel degiskenler:

- `auth.password_reset`: `{{user_name}}`, `{{reset_link}}`, `{{expires_minutes}}`
- `queue.test_mail`: `{{user_name}}`, `{{sent_at}}`
- `users.session_dropped`: `{{user_name}}`
- `users.lock_key_reset`: `{{user_name}}`

Render kurali:

- `{{var}}` -> HTML escape uygulanir
- `{{{var}}}` -> raw HTML olarak yazilir

`mail.test_manual` sablonunda bilerek `{{{message_html}}}` kullanilir.

## TinyMCE Editor

Sablon duzenleme ekraninda (`mail/templates`) `html_body` alanlari TinyMCE ile acilir.

- WYSIWYG + HTML code gorunumu
- Form submit oncesi editor icerigi otomatik textarea'ya yazilir
- CDN: `https://cdn.jsdelivr.net/npm/tinymce@7.2.1/tinymce.min.js`

Lisans:

- `license_key: 'gpl'` olarak ayarlidir.

## Izinler ve Route'lar

Ekran:

- `GET mail/templates` -> `mail.view`

Actionlar:

- `POST mail/actions/template-create` -> `mail.view`
- `POST mail/actions/template-update` -> `mail.view`
- `POST mail/actions/template-delete` -> `mail.view`

Not:

- Sistem sablonlari action tarafinda ek kontrolle silinemez.

## Teknik Fonksiyonlar

`core/mail.php`:

- `kirpi_mail_templates_table_ready()`
- `kirpi_mail_default_templates()`
- `kirpi_mail_sync_system_templates()`
- `kirpi_mail_get_template()`
- `kirpi_mail_render_placeholders()`
- `kirpi_mail_extract_placeholders()`
- `kirpi_send_templated_mail()`

## Entegrasyon Noktalari

- Auth forgot-password maili -> `auth.password_reset`
- Queue test mail job'i -> `queue.test_mail`
- Users oturum dusurme bildirimi -> `users.session_dropped`
- Users lock key sifirlama bildirimi -> `users.lock_key_reset`
- Mail test ekrani manuel gonderim -> `mail.test_manual`

## Sorun Giderme

### Sablon kaydederken hata

1. `Ayarlar > Eksikleri Kur` calistirin (`mail_templates` tablosu icin)
2. Browser cache temizleyin (`Ctrl+F5`)
3. CSP nedeniyle editor script'i engelleniyor mu console'dan kontrol edin

### JSON parse hatasi (`Unexpected token '﻿'`)

- `assets/js/app.js` tarafinda BOM temizleme aktif.
- Action dosyalarinin UTF-8 (BOM'suz) kayitli oldugunu dogrulayin.

### TinyMCE lisans uyarisi

- Editor init config'te `license_key: 'gpl'` ayari bulunur.
