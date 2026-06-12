# Kirpi Core Ekran Test Kontrol Listesi

Test tarihi: `____-__-__`  
Test eden: `________________`  
Ortam / URL: `________________`  
Commit / sürüm: `________________`  
Tarayıcı ve cihaz: `________________`

## Kullanım

Her testin başına aşağıdaki sonuçlardan birini yazın:

- `[x]` Başarılı
- `[!]` Hatalı
- `[-]` Test edilemedi
- `[ ]` Henüz test edilmedi
- `[N/A]` Bu ortamda uygulanamaz

Hatalı veya test edilemeyen her madde için bölüm sonundaki not alanına test kodunu, hata mesajını ve mümkünse ekran görüntüsü adını yazın.

## 1. Genel Shell ve Dashboard

- `[ ] GEN-01` Login ekranı açılıyor; logo, metinler ve form taşmadan görünüyor.
- `[ ] GEN-02` Login ekranında Light, Dark ve System tema seçimleri çalışıyor.
- `[ ] GEN-03` Geçerli kullanıcıyla giriş başarılı; hatalı şifre doğru uyarıyı gösteriyor.
- `[ ] GEN-04` Dashboard hatasız açılıyor; kartlar ve hızlı bağlantılar çalışıyor.
- `[ ] GEN-05` Yönetim menüsü grupları doğru sırada açılıp kapanıyor.
- `[ ] GEN-06` Kullanıcı menüsündeki Light, Dark ve System seçimleri tüm sayfalara uygulanıyor.
- `[ ] GEN-07` Geniş ve dar görünüm seçimi çalışıyor ve sayfa değişiminde korunuyor.
- `[ ] GEN-08` Profil bağlantısı, bildirim dropdown'u ve çıkış işlemi çalışıyor.
- `[ ] GEN-09` AI balonu açılıyor, kapanıyor ve ekran içeriğiyle çakışmıyor.
- `[ ] GEN-10` Mobil görünümde menü, tablolar, formlar ve butonlar kullanılabilir durumda.
- `[ ] GEN-11` Türkçe karakterler doğru; bozuk `Ä`, `Å`, `Ã` karakterleri görünmüyor.
- `[ ] GEN-12` Tarayıcı konsolunda kritik JavaScript hatası oluşmuyor.

Notlar: `____________________________________________________________________`

## 2. Kullanıcılar, Roller ve Profil

- `[ ] ACC-01` Kullanıcı listesi açılıyor; arama, filtre ve sayfalama çalışıyor.
- `[ ] ACC-02` CSV ve XLS kullanıcı export dosyaları indiriliyor ve içerikleri doğru.
- `[ ] ACC-03` Test kullanıcısı oluşturma, düzenleme ve aktif/pasif işlemleri çalışıyor.
- `[ ] ACC-04` Kullanıcı oturum düşürme ve lock key sıfırlama işlemleri doğru uyarı veriyor.
- `[ ] ACC-05` Rol listesi açılıyor; arama, filtre ve sayfalama çalışıyor.
- `[ ] ACC-06` Rol oluşturma, düzenleme ve aktif/pasif işlemleri çalışıyor.
- `[ ] ACC-07` Permission Catalog ve Role-Permission Matrix ekranları doğru yükleniyor.
- `[ ] ACC-08` Rol izinleri güncelleniyor ve ilgili kullanıcı erişimine yansıyor.
- `[ ] ACC-09` Rol ve yetki export dosyaları indiriliyor ve içerikleri doğru.
- `[ ] ACC-10` Profil bilgileri güncelleniyor; tema/layout tercihleri korunuyor.
- `[ ] ACC-11` API token oluşturma ve iptal işlemleri çalışıyor; token sonradan açık gösterilmiyor.
- `[ ] ACC-12` Ekran kilitleme, kilit açma ve logout akışları çalışıyor.

Notlar: `____________________________________________________________________`

## 3. İçerik Yönetimi

- `[ ] CNT-01` Template Registry açılıyor; tür, modül, kod, aktiflik ve arama filtreleri çalışıyor.
- `[ ] CNT-02` Email, Print ve Content template türleri doğru listeleniyor.
- `[ ] CNT-03` Template oluşturma, düzenleme ve aktif/pasif işlemleri çalışıyor.
- `[ ] CNT-04` TinyMCE Light/Dark teması kullanıcı temasıyla eşleşiyor.
- `[ ] CNT-05` Template CSV/XLS export mevcut filtreleri koruyor.
- `[ ] CNT-06` Documents ekranı açılıyor; arama, belge türü ve entity filtreleri çalışıyor.
- `[ ] CNT-07` Test belgesi yükleniyor, indiriliyor ve doğru entity bilgisiyle listeleniyor.
- `[ ] CNT-08` Test belgesi silme onayı ve silme işlemi çalışıyor.
- `[ ] CNT-09` Documents CSV/XLS export mevcut filtreleri koruyor.

Notlar: `____________________________________________________________________`

## 4. İletişim

- `[ ] COM-01` Bildirim listesi açılıyor; filtreler ve sayfalama çalışıyor.
- `[ ] COM-02` Tek bildirimi ve tüm bildirimleri okundu işaretleme çalışıyor.
- `[ ] COM-03` Bildirim ayarları kaydediliyor ve yeniden açıldığında korunuyor.
- `[ ] COM-04` Bildirim CSV/XLS export mevcut filtreleri koruyor.
- `[ ] COM-05` Mail Test ekranı açılıyor; yapılandırma durumu doğru gösteriliyor.
- `[ ] COM-06` Geçerli mail ayarı varsa test maili gönderiliyor; yoksa anlaşılır hata gösteriliyor.
- `[ ] COM-07` Mail şablonları listeleniyor; oluşturma, düzenleme ve silme çalışıyor.
- `[ ] COM-08` Mail şablonu CSV/XLS export dosyaları doğru.
- `[ ] COM-09` İletişim aksiyonları audit ve notification kaydı üretiyor.

Notlar: `____________________________________________________________________`

## 5. Operasyon ve İzleme

- `[ ] OPS-01` Backup ekranı açılıyor ve mevcut kayıtları listeliyor.
- `[ ] OPS-02` Test backup oluşturma, doğrulama ve indirme işlemleri çalışıyor.
- `[ ] OPS-03` Test backup silme işlemi onay sonrası çalışıyor.
- `[ ] OPS-04` Backup ve restore log export dosyaları doğru.
- `[ ] OPS-05` Restore işlemi yalnız güvenli test ortamında deneniyor ve sonucu audit'e yazılıyor.
- `[ ] OPS-06` Queue ekranı açılıyor; durum sayaçları ve kayıt listesi doğru.
- `[ ] OPS-07` Test mail job enqueue ve `work once` işlemleri çalışıyor.
- `[ ] OPS-08` Başarısız job retry işlemi ve Queue export çalışıyor.
- `[ ] OPS-09` Audit Overview açılıyor; modül özetleri ve export çalışıyor.
- `[ ] OPS-10` Audit Log arama, modül, aksiyon, durum ve tarih filtreleri çalışıyor.
- `[ ] OPS-11` Audit Log CSV/XLS export mevcut filtreleri koruyor.
- `[ ] OPS-12` Health ekranında app ve DB kontrolleri sağlıklı gösteriliyor.
- `[ ] OPS-13` Security ekranı açılıyor; güvenlik olayları ve export çalışıyor.
- `[ ] OPS-14` API Metrics ekranı açılıyor; sayaçlar, filtreler ve export çalışıyor.
- `[ ] OPS-15` Env Reader mevcut env anahtarlarını gösteriyor; secret/token/password değerleri maskeli.

Notlar: `____________________________________________________________________`

## 6. Sistem Ayarları ve Modüller

- `[ ] SYS-01` Ayarlar ekranı açılıyor; sekmeler ve alanlar taşmadan görünüyor.
- `[ ] SYS-02` Kritik olmayan ayarlar kaydediliyor ve yeniden açıldığında korunuyor.
- `[ ] SYS-03` Secret alanları açık metin olarak geri gösterilmiyor.
- `[ ] SYS-04` API Test ekranı geçerli/geçersiz token sonuçlarını doğru gösteriyor.
- `[ ] SYS-05` Modüller ekranında tüm Core modülleri ve sürümleri doğru listeleniyor.
- `[ ] SYS-06` Eksik kurulum kontrolü ve `install missing` işlemi doğru sonuç veriyor.
- `[ ] SYS-07` Test edilebilir bir modülün aç/kapat işlemi menü ve route davranışına yansıyor.
- `[ ] SYS-08` Modül registry CSV/XLS export dosyaları doğru.
- `[ ] SYS-09` Menü Yönetimi ekranında grup, sıra, başlık, URL ve permission bilgileri doğru.
- `[ ] SYS-10` Menü registry CSV/XLS export dosyaları doğru.
- `[ ] SYS-11` Sistem ayarı ve modül işlemleri audit/notification kaydı üretiyor.

Notlar: `____________________________________________________________________`

## 7. Kirpi Intelligence v1.0

- `[ ] AI-01` AI Dashboard özetleri, hızlı işlemler ve son sync bilgisi doğru.
- `[ ] AI-02` Schema Sync başarılı; entity/field/index sayaçları güncelleniyor.
- `[ ] AI-03` Schema Discovery arama ve tüm filtreleri çalışıyor.
- `[ ] AI-04` Schema JSON/CSV/XLS export mevcut filtreleri koruyor; hassas alanlar varsayılan gizli.
- `[ ] AI-05` Schema Quality ekranı ve JSON/CSV/XLS export çalışıyor.
- `[ ] AI-06` Query Planner doğru aday tablo ve alanları getiriyor.
- `[ ] AI-07` Query Flow yalnız aktif `sql_generation` adapterlarını listeliyor.
- `[ ] AI-08` Provider Ayarları kaydediliyor; Adapter Tipi sayfa yenilenince değişmiyor.
- `[ ] AI-09` Provider bağlantı testi beklenen JSON sözleşmesini doğruluyor.
- `[ ] AI-10` `aktif kullanıcıları listele` testi açık alanlı tek SELECT candidate üretiyor.
- `[ ] AI-11` Aday SQL içinde reasoning, açıklama, markdown veya `<think>` görünmüyor.
- `[ ] AI-12` `SELECT *`, izin dışı tablo ve izin dışı alan testleri Guard tarafından bloklanıyor.
- `[ ] AI-13` Query Flow sonucu Preview Allowed, Execution Kapalı ve Veri Okuma Hayır gösteriyor.
- `[ ] AI-14` SQL Guard yakalanan tabloları, alanları ve blok nedenlerini doğru gösteriyor.
- `[ ] AI-15` SQL Preview SQL çalıştırmıyor; EXPLAIN env kapalıysa bloklu kalıyor.
- `[ ] AI-16` AI Audit Log planner, candidate, preview, guard ve provider test kayıtlarını gösteriyor.
- `[ ] AI-17` Debug JSON Kopyala çalışıyor; secret değerleri bulunmuyor veya maskeli.
- `[ ] AI-18` AI ekranları Light/Dark tema ve mobil görünümde okunabilir.

Notlar: `____________________________________________________________________`

## 8. API ve Yetki Negatif Testleri

- `[ ] SEC-01` Oturumsuz yönetim sayfası isteği login ekranına yönleniyor.
- `[ ] SEC-02` Yetkisiz kullanıcı menüde izin verilmeyen ekranları görmüyor.
- `[ ] SEC-03` Yetkisiz route isteği `403` veya standart erişim reddi sonucu veriyor.
- `[ ] SEC-04` POST aksiyonları geçersiz CSRF token ile reddediliyor.
- `[ ] SEC-05` API token olmadan korumalı API endpoint'i erişimi reddediyor.
- `[ ] SEC-06` Geçerli token ile `/api/v1/me` doğru kullanıcıyı döndürüyor.
- `[ ] SEC-07` Hassas alanlar API/schema/export çıktılarında izin olmadan görünmüyor.
- `[ ] SEC-08` Hata ekranları stack trace, DB şifresi veya secret göstermiyor.

Notlar: `____________________________________________________________________`

## 9. Sonuç Özeti

| Alan | Başarılı | Hatalı | Test Edilemedi | N/A |
|---|---:|---:|---:|---:|
| Genel Shell ve Dashboard |  |  |  |  |
| Kullanıcılar, Roller ve Profil |  |  |  |  |
| İçerik Yönetimi |  |  |  |  |
| İletişim |  |  |  |  |
| Operasyon ve İzleme |  |  |  |  |
| Sistem Ayarları ve Modüller |  |  |  |  |
| Kirpi Intelligence |  |  |  |  |
| API ve Yetki |  |  |  |  |
| **Toplam** |  |  |  |  |

Genel karar: `[ ] Yayına uygun` `[ ] Düzeltme sonrası tekrar test` `[ ] Bloke`

Kritik bulgular:

1. `__________________________________________________________________`
2. `__________________________________________________________________`
3. `__________________________________________________________________`

Ek notlar / ekran görüntüsü bağlantıları:

`__________________________________________________________________________`
