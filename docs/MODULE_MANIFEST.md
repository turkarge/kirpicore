# KirpiCore Module Manifest (`module.json`)

Bu dokuman, moduller icin opsiyonel manifest yapisini tanimlar.
Mevcut sistemi bozmaz; `module.json` olmayan modul de varsayilan degerlerle yuklenir.

## Dosya Konumu

- `modules/<module_key>/module.json`

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

## Geriye Uyumluluk

- `module.json` yoksa default degerler kullanilir.
- Mevcut route yapisi ve modul dizin yapisi aynen korunur.

## Registry ve Runtime

- DB registry tablosu: `app_modules`
- Runtime'da modul listesi:
  - Manifest degerleri
  - `app_modules` override degerleri (`is_enabled`, `load_order`, `is_core`)
- Route yukleme yalnizca `enabled=true` moduller icin yapilir.

## Yönetim Ekrani

- Route: `settings/modules`
- Core moduller (`is_core=1`) disable edilemez.
- Bir modul diger aktif moduller tarafindan `requires` ile kullaniliyorsa disable edilmez.
