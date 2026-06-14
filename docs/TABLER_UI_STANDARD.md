# Kirpi Core Tabler UI Standardı

Kirpi Core arayüzünün görsel tasarım sistemi Tabler'dir. Core içinde Tabler'in sunduğu bir bileşen için ikinci bir tema katmanı oluşturulmaz.

## Temel Kurallar

- Bileşenler önce resmi Tabler HTML yapısı ve sınıflarıyla kurulur.
- Light ve dark görünüm Tabler tema tokenları tarafından yönetilir.
- `assets/css/app.css` yalnızca Kirpi Core'a özgü yerleşim, davranış ve Tabler'de bulunmayan işlevsel ihtiyaçları kapsar.
- Tabler bileşenlerinde özel arka plan, border, shadow, hover veya renk tanımı eklenmez.
- JavaScript hook sınıfları `js-` ön ekiyle kullanılır ve görsel stil taşımaz.
- Tabler Icons mevcutsa elle SVG üretilmez.

## Navbar Pilotu

Navbar, bu standarda geçen ilk Core bölümüdür:

- Header, marka, collapse ve dropdown yapilari native Tabler siniflarini kullanir.
- Mobil menü Bootstrap/Tabler collapse davranışıyla çalışır; ek bir mobil menü yöneticisi bulunmaz.
- Bildirim sayacı `badge badge-sm bg-red text-red-fg` standardını kullanır.
- Bildirim dropdown'u `dropdown-menu-card`, `card`, `list-group` ve `card-footer` yapısına dayanır.
- Okundu aksiyonu arka plan hover'i kullanmaz; hover yalnızca check ikonunu vurgular.
- "Tüm bildirimleri gör" aksiyonu card footer içinde düz bağlantıdır.

Sonraki ekran geçişlerinde aynı yaklaşım uygulanacak ve mevcut özel CSS yalnızca davranış veya yerleşim için gerekli olduğu kanıtlanırsa korunacaktır.
