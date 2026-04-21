# KirpiCore Module Manifest (`module.json`)

Bu doküman, modüller için opsiyonel manifest yapısını tanımlar.
Mevcut sistemi bozmaz; `module.json` olmayan modül de varsayılan değerlerle yüklenir.
Bu belge aynı zamanda KirpiCore modüllerinin geliştirme standardını tanımlar.

## Dosya Konumu

- `modules/<module_key>/module.json`

## Modül Geliştirme Standardı

Her modül, aşağıdaki yapıdan ihtiyacına uygun olan dosyaları içermelidir:

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

- `language.php` dosyası modül seviyesinde zorunlu standarttır.
- Bir modülde UI veya action yoksa ilgili klasörlerin boş olması sorun değildir.
- `database/*` dosyaları yoksa setup bu modülü veritabanı adımında pas geçer.

## Örnek

```json
{
  "key": "users",
  "name": "Users",
  "description": "Kullanıcı yönetimi",
  "version": "1.0.0",
  "enabled": true,
  "core": true,
  "load_order": 30,
  "requires": [],
  "author": "Kirpi Core"
}
```

## Alanlar

- `key` (string): Modül teknik anahtarı.
- `name` (string): Panel/insan okunur ad.
- `description` (string): Kısa açıklama.
- `version` (string): Modül versiyonu.
- `enabled` (bool): `false` ise module ait `routes.php` yüklenmez.
- `core` (bool): Core modül mü bilgisi.
- `load_order` (int): Modül yükleme sırası (küçükten büyüğe).
- `requires` (array<string>): Gelecekte bağımlılık kontrolü için ayrılan alan.
- `author` (string): Modül geliştirici bilgisi.
- `menu` (array<object>): Modülün navigasyona eklemek istediği menü öğeleri.

## Menü Standardı (`module.json > menu`)

Kirpi Core, menüleri artık modül manifestlerinden üretir.

Sabit kurallar:

- `Dashboard` her zaman ilk sıradadır (`weight=1`, sabit).
- `Yönetim` her zaman son sıradadır (`weight=999`, sabit).
- Modüllerden gelen tüm menüler bu iki sabit öğe arasına veya `Yönetim` altına yerleşir.

`menu` öğesi alanları:

- `title` (string, zorunlu): Menüde görünen başlık
- `title_key` (string, opsiyonel): Modül `language.php` içindeki çeviri anahtarı. Varsa başlık bununla çözülür.
- `icon` (string, opsiyonel): Tabler icon class (örn: `ti ti-users`)
- `url` (string, zorunlu): Route path (örn: `users/view`)
- `permission` (string|null, opsiyonel): Yetki kontrol anahtarı
- `placement` (string): `top` veya `management`
- `group` (string): `management` içindeki grup anahtarı (`default`, `monitoring`, ...)
- `weight` (int): Sıralama ağırlığı (küçük değer önce gelir)

Notlar:

- `placement=top`: Dashboard ile Yönetim arasında üst menüde gösterilir.
- `placement=management`: Yönetim dropdown içinde gösterilir.
- `group=monitoring`: Yönetim altında `Monitoring / İzleme` alt grubuna otomatik alınır.
- `title_key` kullanıldığında menü etiketi `<module>_lang('<title_key>')` ile çekilir.
- Route mevcut değilse veya kullanıcının yetkisi yoksa menü öğesi otomatik gizlenir.

Örnek:

```json
{
  "key": "users",
  "name": "Users",
  "menu": [
    {
      "title": "Kullanicilar",
      "icon": "ti ti-users",
      "url": "users/view",
      "permission": "users.view",
      "placement": "management",
      "group": "default",
      "weight": 100
    }
  ]
}
```

## Dil Dosyası Standardı (`language.php`)

Her modül, kendi çeviri fonksiyonunu sağlar:

- Fonksiyon adı modül bazlı olmalıdır. Örnek: `users_lang()`, `auth_lang()`, `api_lang()`.
- İmza: `function <module>_lang(string $key, ?string $default = null): string`
- `tr` ve `en` sözlükleri aynı anahtar setini korumaya çalışmalıdır.
- Locale kaynağı: `APP_LOCALE` (`tr` varsayılan).
- Bulunamayan anahtarlarda geri dönüş sırası:
  - aktif locale
  - `tr`
  - `$default`
  - `$key`

Kullanım:

- Sayfa/action başında: `require_once BASE_PATH . '/modules/<module_key>/language.php';`
- Sabit metinler doğrudan yazılmak yerine `*_lang('key')` ile okunur.

## Geriye Uyumluluk

- `module.json` yoksa default değerler kullanılır.
- Mevcut route yapısı ve modül dizin yapısı aynen korunur.
- `language.php` olmayan eski modüller teknik olarak çalışabilir; ancak yeni standartta eklenmesi gerekir.

## Registry ve Runtime

- DB registry tablosu: `app_modules`
- Runtime'da modül listesi:
  - Manifest değerleri
  - `app_modules` override değerleri (`is_enabled`, `load_order`, `is_core`)
- Route yükleme yalnızca `enabled=true` modüller için yapılır.

## Kurulum ve Schema Davranışı

- Core kurulum: `database/core.sql`
- Modül schema kurulumları: `modules/*/database/schema.sql`
- Modül permission kurulumları: `modules/*/database/permissions.sql`
- Setup şu kurallarla çalışır:
  - Dosya yoksa atlanır.
  - Dosya varsa statement bazında çalıştırılır.
  - Idempotent SQL tercih edilir (`IF NOT EXISTS`, `INSERT IGNORE`, vb.).

## Yönetim Ekranı

- Route: `settings/modules`
- Core modüller (`is_core=1`) disable edilemez.
- Bir modül diğer aktif modüller tarafından `requires` ile kullanılıyorsa disable edilmez.

## Kodlama Kuralları (Özet)

- Modül, yalnız kendi alanındaki dil anahtarlarını kullanır.
- Action cevapları tutarlı JSON formatında olur (`status`, `message`, opsiyonel `data`).
- UI metinleri ve tablo başlıkları dil dosyasından gelir.
- Yeni modül eklerken önce `module.json` + `language.php` oluşturulur, sonra route/page/action yazılır.
