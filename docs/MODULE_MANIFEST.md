# KirpiCore Module Manifest (`module.json`)

Bu dokuman, moduller icin opsiyonel manifest yapisini tanimlar.
Mevcut sistemi bozmaz; `module.json` olmayan modul de varsayilan degerlerle yuklenir.
Bu belge ayni zamanda KirpiCore modullerinin gelistirme standardini tanimlar.

## Dosya Konumu

- `modules/<module_key>/module.json`

## Modul Gelistirme Standardi

Her modul, asagidaki yapidan ihtiyacina uygun olan dosyalari icermelidir:

- `modules/<module_key>/module.json`
- `modules/<module_key>/language.php`
- `modules/<module_key>/routes.php`
- `modules/<module_key>/pages/`
- `modules/<module_key>/actions/`
- `modules/<module_key>/modals/`
- `modules/<module_key>/partials/`
- `modules/<module_key>/scripts/`
- `modules/<module_key>/database/schema.sql`
- `modules/<module_key>/database/permissions.sql`

Notlar:

- `language.php` dosyasi modul seviyesinde zorunlu standarttir.
- Bir modulde UI veya action yoksa ilgili klasorlerin bos olmasi sorun degildir.
- `database/*` dosyalari yoksa setup bu modulu veritabani adiminda pas gecer.

## Ornek

```json
{
  "key": "users",
  "name": "Users",
  "description": "Kullanici yonetimi",
  "version": "1.0.0",
  "enabled": true,
  "core": true,
  "load_order": 30,
  "requires": [],
  "author": "Kirpi Core"
}
```

## Alanlar

- `key` (string): Modul teknik anahtari.
- `name` (string): Panel/insan okunur ad.
- `description` (string): Kisa aciklama.
- `version` (string): Modul versiyonu.
- `enabled` (bool): `false` ise module ait `routes.php` yuklenmez.
- `core` (bool): Core modul mu bilgisi.
- `load_order` (int): Modul yukleme sirasi (kucukten buyuge).
- `requires` (array<string>): Gelecekte bagimlilik kontrolu icin ayrilan alan.
- `author` (string): Modul gelistirici bilgisi.

## Dil Dosyasi Standardi (`language.php`)

Her modul, kendi ceviri fonksiyonunu saglar:

- Fonksiyon adi modul bazli olmalidir. Ornek: `users_lang()`, `auth_lang()`, `api_lang()`.
- Imza: `function <module>_lang(string $key, ?string $default = null): string`
- `tr` ve `en` sozlukleri ayni anahtar setini korumaya calismalidir.
- Locale kaynagi: `APP_LOCALE` (`tr` varsayilan).
- Bulunamayan anahtarlarda geri donus sirasi:
  - aktif locale
  - `tr`
  - `$default`
  - `$key`

Kullanim:

- Sayfa/action basinda: `require_once BASE_PATH . '/modules/<module_key>/language.php';`
- Sabit metinler dogrudan yazilmak yerine `*_lang('key')` ile okunur.

## Geriye Uyumluluk

- `module.json` yoksa default degerler kullanilir.
- Mevcut route yapisi ve modul dizin yapisi aynen korunur.
- `language.php` olmayan eski moduller teknik olarak calisabilir; ancak yeni standartta eklenmesi gerekir.

## Registry ve Runtime

- DB registry tablosu: `app_modules`
- Runtime'da modul listesi:
  - Manifest degerleri
  - `app_modules` override degerleri (`is_enabled`, `load_order`, `is_core`)
- Route yukleme yalnizca `enabled=true` moduller icin yapilir.

## Kurulum ve Schema Davranisi

- Core kurulum: `database/core.sql`
- Modul schema kurulumlari: `modules/*/database/schema.sql`
- Modul permission kurulumlari: `modules/*/database/permissions.sql`
- Setup su kurallarla calisir:
  - Dosya yoksa atlanir.
  - Dosya varsa statement bazinda calistirilir.
  - Idempotent SQL tercih edilir (`IF NOT EXISTS`, `INSERT IGNORE`, vb.).

## Yonetim Ekrani

- Route: `settings/modules`
- Core moduller (`is_core=1`) disable edilemez.
- Bir modul diger aktif moduller tarafindan `requires` ile kullaniliyorsa disable edilmez.

## Kodlama Kurallari (Ozet)

- Modul, yalniz kendi alanindaki dil anahtarlarini kullanir.
- Action cevaplari tutarli JSON formatinda olur (`status`, `message`, opsiyonel `data`).
- UI metinleri ve tablo basliklari dil dosyasindan gelir.
- Yeni modul eklerken once `module.json` + `language.php` olusturulur, sonra route/page/action yazilir.
