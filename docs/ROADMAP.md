# Kirpi Core Yol Haritası

Bu belge, Core geliştirme sırasını ve tamamlanan standartları izlemek için kullanılır.

## Tamamlanan Core Standartları

### UI ve Shell

- Kullanıcı menüsünden tema seçimi: `light`, `dark`, `system`.
- Kullanıcı menüsünden görünüm seçimi: geniş/dar layout tercihi.
- Login ekranında tema seçimi.
- Tema varlıkları ve kullanıcı tercihleri kalıcı hale getirildi.
- Menü üretimi `module.json > menu` standardına bağlandı.
- Content menü grubu Türkçeleştirildi.

### Kurulum ve Docker

- Dokploy uyumlu Docker akışı güçlendirildi.
- Compose tarafında host port publish kaldırıldı; proxy üzerinden `app:80` standardı netleştirildi.
- Container bağımlılıkları `zip/unzip` eksikleri için düzeltildi.
- Health check ve otomatik DB kurulum akışı doğrulandı.

### Modül Standartları

- Her modül için `module.json` ve `language.php` standardı netleştirildi.
- Türkçe çevirilerde `UTF-8 (BOM'suz)` standardı zorunlu hale getirildi.
- Tarih gösterimi `kirpi_format_datetime()` standardına taşındı.
- Standart modüllere AI schema manifestleri eklenmeye başlandı.

### PWA

- `manifest.webmanifest` ve `service-worker.js` temeli eklendi.
- Offline fallback ve temel asset cache davranışı standardize edildi.

### Template ve Documents

- Core Template Registry eklendi.
- Core Documents Registry eklendi.
- Template ve Documents modülleri Content menü grubu altına alındı.
- Standart modüller yeni template/document altyapısına uyumlu hale getirilmeye başlandı.

### Notifications

- Template tabanlı notification render akışı eklendi.
- Standart modül olayları notification sistemine bağlandı.
- Notification metadata alanları eklendi:
  - `template_key`
  - `source_module`
  - `entity_type`
  - `entity_id`
  - `data_json`
- Notification listesine metadata filtreleri eklendi.

### Report ve Export

- Ortak export helper eklendi: `core/export.php`.
- CSV ve XLS çıktıları sunucu tarafında üretilecek standart endpoint yapısına bağlandı.
- Export butonları gerçek link olarak çalışır; JS varsa mevcut filtreler URL'e eklenir.
- Aşağıdaki modüllerde server-side export tamamlandı:
  - `notifications`
  - `documents`
  - `audit`
  - `users`
  - `roles`
- Roles tarafında ek exportlar:
  - Permission Catalog
  - Role-Permission Matrix

## Devam Eden Standartlaştırma

- Report/export davranışının kalan standart modüllere yayılması.
- Template ve Documents entegrasyonlarının modül bazında derinleştirilmesi.
- Notification event üretiminin tüm CRUD akışlarında tutarlı hale getirilmesi.
- AI öncesi schema/metadata kapsamının genişletilmesi.

## Yarın Planı

1. **Template export ve filtre standardı** - Tamamlandı
   - Template listesine CSV/XLS export eklendi.
   - Template türü, modül, kod, aktiflik ve metin arama filtreleri standardize edildi.

2. **Documents filtre standardı** - Tamamlandı
   - Documents ekranına arama, belge türü, entity türü ve entity ID filtreleri eklendi.
   - Mevcut export endpoint'i bu filtreleri okuyacak şekilde genişletildi.

3. **Settings/Modules export**
   - Modül registry listesini CSV/XLS export et.
   - Menü registry görünümü için export gereksinimini değerlendir.

4. **Standard UI kontrolü**
   - Export butonları tüm ilgili ekranlarda gerçek `<a>` link olarak kalsın.
   - JS yalnızca filtre ekleme sorumluluğunu taşısın.
   - Butonlar, ikonlar ve metinler dil dosyasından gelsin.

5. **KIP hazırlığı**
   - AI schema manifestlerini kalan standart modüllerde tamamla.
   - Schema discovery ekranında export/metadata kullanımı için gereksinimleri çıkar.

## Kısa Vadeli Sonraki Sıra

- Template export
- Documents filtre/export iyileştirmesi
- Settings/Modules export
- Mail templates export
- Backup/audit operasyon raporları
- KIP Faz 2 için metadata indeksleme hazırlığı
