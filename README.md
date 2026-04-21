# KirpiCore

Core moduller tamamlandi:

- `auth`
- `dashboard`
- `users`
- `roles`
- `profile`
- `notifications`
- `mail`
- `audit`
- `settings`
- `queue`
- `backup`
- `health`
- `throttle`
- `api`

## Modul Manifest Yapisi

Moduller icin geriye uyumlu `module.json` yapisi eklendi.

- Konum: `modules/<module>/module.json`
- Mevcut yapi korunur: `module.json` olmayan modul de calismaya devam eder.
- Detay: `docs/MODULE_MANIFEST.md`

## Modul Yonetimi

Yonetim menusu altina modul yonetim ekrani eklendi.

- Route: `settings/modules`
- Goruntuleme yetkisi: `settings.view`
- Toggle yetkisi: `settings.update`

Ozellikler:

- Modul listesi (`module.json` + `app_modules` registry birlesik gorunum)
- Enable/Disable toggle
- Core moduller devre disi birakilamaz
- Bagimlilik kullanan aktif moduller varsa disable engellenir

Not:

- Yeni `app_modules` tablosu icin deploy sonrasi `Ayarlar -> Eksikleri Kur` calistirin.

## Dokploy ile calistirma

Bu repo artik Docker Compose ile dogrudan deploy edilebilir.

### 1) Dokploy'de yeni Compose uygulamasi ac

- Repository: bu repo
- Compose file: `docker-compose.yml`

### 2) Environment Variables tanimla

Minimum gerekli degiskenler:

- `BASE_URL=https://sizin-domaininiz`
- `DB_PASS=guclu_sifre`
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_TRUST_PROXY=true`
- `SETUP_KEY=guclu_bir_setup_key`

Opsiyonel:

- `AUTO_DB_INSTALL=true` (default: `true`)
- `AUTO_WEB_SETUP=true` (default: `true`)
- `AUTO_DB_ENSURE_MISSING=false` (default: `false`) - `true` ise uygulama acilisinda eksik tablo kontrolu yapip eksikleri kurar
- `AUTO_DB_ENSURE_COLUMNS=true` (default: `true`) - eksik kritik kolonlari otomatik tamamlar
- `AUTO_DB_ENSURE_INDEXES=true` (default: `true`) - `true` ise eksik performans indekslerini otomatik tamamlar
- `SESSION_COOKIE_DOMAIN=` (onerilen: bos birakin; isterseniz sadece host verin, ornek: `core.kirpinetwork.com`)
- `SESSION_IDLE_TIMEOUT_SECONDS=7200` (default: `7200`) - belirli sure pasif kalan session otomatik sifirlanir
- `SESSION_ID_ROTATE_SECONDS=900` (default: `900`) - session fixation riskini azaltmak icin session id periyodik yenilenir
- `SECURITY_HEADERS_ENABLED=true` (default: `true`) - CSP, permissions-policy ve ek guvenlik headerlarini aktif eder
- `BACKUP_RETENTION_COUNT=20` (default: `20`) - son N backup disindakiler otomatik silinir
- `BACKUP_VERIFY_DRY_RUN=true` (default: `true`) - backup dogrulamada gecici veritabanina restore testi yapar
- `BACKUP_INCLUDE_SYSTEM_TABLES=false` (default: `false`) - `db_backups` ve `db_backup_restores` tablolarini dump'e dahil eder
- `THROTTLE_ENABLED=true` (default: `true`)
- `THROTTLE_LOGIN_LIMIT=5`, `THROTTLE_LOGIN_WINDOW=600`, `THROTTLE_LOGIN_BLOCK=900`
- `THROTTLE_CRITICAL_LIMIT=10`, `THROTTLE_CRITICAL_WINDOW=60`, `THROTTLE_CRITICAL_BLOCK=120`
- `THROTTLE_GLOBAL_POST_LIMIT=180`, `THROTTLE_GLOBAL_POST_WINDOW=60`, `THROTTLE_GLOBAL_POST_BLOCK=60`
- `THROTTLE_API_LIMIT=120`, `THROTTLE_API_WINDOW=60`, `THROTTLE_API_BLOCK=120`
- `THROTTLE_API_AUTH_LIMIT=10`, `THROTTLE_API_AUTH_WINDOW=300`, `THROTTLE_API_AUTH_BLOCK=600`
- `API_TOKEN_TTL_SECONDS=2592000` (default: 30 gun)
- `API_REQUEST_LOG_RETENTION_DAYS=90` (default: `90`) - API request log kayitlarini bu gunden eskiyse otomatik temizler
- `API_ENABLED=true` (default: `true`) - REST API genel anahtari

### 3) Deploy et

Deploy sonrasi uygulama su akisi otomatik calistirir:

1. DB baglantisini bekler
2. `php shell.php db:install` ile core + module semalarini kurar
3. Apache ile uygulamayi ayaga kaldirir

Saglik kontrol endpoint:

- `GET /healthz`

## Lokal Docker test

```bash
docker compose up -d --build
```

Ardindan:

- Uygulama: `http://localhost:8080`
- MySQL: compose icinde `db:3306`

Notlar:

- Compose servislerinde log rotasyonu aktiftir (`10m`, `5` dosya).

## Web Setup Arayuzu

Ilk kurulum icin:

- `https://sizin-domaininiz/setup.php`

Bu arayuz:

1. `SETUP_KEY` dogrular
2. Core + module schema kurar
3. `Super Admin` ve `Default User` rollerini olusturur
4. Formdan girdigin admin kullaniciyi olusturur/gunceller
5. Kurulan tablolar ve ozet raporu gosterir

Kurulum bitince guvenlik icin:

- `AUTO_WEB_SETUP=false`
- `SETUP_KEY` degerini degistir veya bosalt

## Sorun Giderme

### "Guvenlik dogrulamasi basarisiz oldu"

Bu hata genelde eski session cookie kaynaklidir.

1. Domain cookie'lerini temizle
2. `SESSION_COOKIE_DOMAIN` degerini bos birakip redeploy et
3. Login sayfasini yeniden acip tekrar dene

### "Yukleme dizini olusturulamadi"

Profil avatar yukleme icin uygulama su dizinleri kullanir:

- `uploads`
- `uploads/avatars`
- `logs`
- `storage`

Container acilisinda bu dizinler otomatik olusturulur ve `www-data` icin yazilabilir hale getirilir.

Eger hata devam ederse:

1. Container icinde bu dizinlerin varligini kontrol et
2. Yazma izinlerini kontrol et
3. Reverse proxy veya volume mount ile `uploads` yolunun read-only olmadigini dogrula

## Guvenlik Izleme Sayfasi

Yonetim menusu altina `Guvenlik Izleme` sayfasi eklendi.

- Route: `security/view`
- Permission: `security.view`

Bu sayfa su kontrolleri gosterir:

- Uygulama guvenlik ayarlari (`APP_ENV`, `APP_DEBUG`, `APP_TRUST_PROXY`, `AUTO_WEB_SETUP`, `SETUP_KEY`)
- Session cookie guvenlik bilgileri
- Kritik klasorlerin varlik/yazilabilirlik/izin durumu
- Veritabanindaki mevcut tablolar

## Health + Metrics Modulu

Yonetim menusu altina `Health Metrics` sayfasi eklendi.

- Route: `health/view`
- Permission: `health.view`

Bu sayfa matrix formatinda su bilesenleri izler:

- Application
- Database (latency)
- Queue
- Mail
- Backup
- Disk
- Session
- Throttle

## Monitoring Menusu

Yonetim menusu sadelemek için izleme ekranlari `Yonetim` menusu altinda, listenin en altina toplandi:

- `Audit Log`
- `Guvenlik Izleme`
- `Health Metrics`
- `Jobs Queue`

## Rate Limit / Throttle

Sistem genelinde POST islemleri ve kritik actionlar icin istek limiti uygulanir.

- Login brute-force korumasi (`auth/actions/login`)
- Kritik action korumasi (`backup`, `mail`, `queue`, `settings/actions/install-missing`)
- Genel POST limiti

Not:

- Throttle tablosu: `request_throttles`
- Limit asiminda `429` doner ve `Retry-After` header set edilir

## REST API (v1)

Temel REST API iskeleti eklendi.

- Route prefix: `api/v1`
- Auth: `Authorization: Bearer <token>`
- Response format: `status`, `message`, `data`, `meta`
- API aktif/pasif: `api.enabled` (Ayarlar ekranindan) veya `API_ENABLED=true/false` (env)

Endpointler:

- `POST /api/v1/auth/token` (email + password ile token alir)
- `GET /api/v1/me` (token sahibinin profil bilgisi)
- `GET /api/v1/users` (kullanici listesi, `users.view` yetkisi gerekir)
- `POST /api/v1/users` (kullanici olusturma, `users.create`)
- `PATCH /api/v1/users/{id}` (kullanici guncelleme, `users.edit`)
- `POST /api/v1/users/{id}/status` (durum guncelleme, `users.status`)

Scope modeli:

- `*` tum endpointlere erisir
- `profile:read` -> `GET /api/v1/me`
- `users:read` -> `GET /api/v1/users`
- `users:create` -> `POST /api/v1/users`
- `users:update` -> `PATCH /api/v1/users/{id}`
- `users:status` -> `POST /api/v1/users/{id}/status`

Not:

- API token tablosu: `api_tokens`
- Token olusturma endpoint'i brute-force'a karsi throttle ile korunur
- API cagrilari da throttle kapsamindadir
- Super Admin, Profil sayfasindan API token olusturabilir, listeleyebilir ve revoke edebilir
- API root kontrol endpoint: `GET /api/v1`
- Detayli users API dokumani: `docs/API_USERS.md`
- API release checklist: `docs/API_RELEASE_CHECKLIST.md`
- API alarm esikleri: `docs/API_ALERT_THRESHOLDS.md`
- Hata cevaplarinda `error_code` alani vardir

CLI smoke test:

- `php shell.php api:smoke [base_url] <email> <password>`
- Ornek: `php shell.php api:smoke https://core.kirpinetwork.com admin@kirpi.local 123456`

### API Test Merkezi (Panel)

Postman gerektirmeden panelden canli API testi yapabilirsiniz.

- Menu: `Yonetim -> API Test`
- Route: `settings/api-test`
- Permission: `settings.view`

Bu ekranda:

- Method secilir (`GET`, `POST`, `PATCH`, `DELETE`)
- Endpoint girilir (or: `/api/v1/me`)
- Bearer token eklenir
- JSON body gonderilir
- HTTP status + response anlik gorulur

### API Metrics (Panel)

- Menu: `Yonetim -> API Metrics`
- Route: `api/metrics`
- Permission: `health.view`

Bu ekranda son 24 saat icin:

- Toplam API cagri sayisi
- 2xx / 4xx / 5xx dagilimi
- 401 / 403 / 429 sayilari
- En cok cagrilan endpointler
- Son hatalar (`error_code` dahil)

Zaman filtresi:

- `1 Saat`
- `24 Saat`
- `7 Gun`

### Postman ile hizli test

Repo icinde hazir collection dosyasi:

- `postman/KirpiCore_API_v1.postman_collection.json`

Collection indirme endpointleri:

- `GET /api/v1/postman-collection`
- `GET /api/v1/postman`
- `GET /api/v1/postman-collection.json`

Adimlar:

1. Postman > Import > bu JSON dosyasini secin
2. Collection variables icinde `base_url`, `email`, `password` degerlerini kontrol edin
3. Sirayla calistirin:
   - `Auth - Create Token`
   - `Me - Current User`
   - `Users - List`
   - `Users - Create`
   - `Users - Update (PATCH)`
   - `Users - Status Update`

`Auth - Create Token` istegi basarili olursa `access_token` degiskenine otomatik yazilir.

## Mail Modulu

Yonetim menusu altinda `Mail Test` ekrani bulunur.

- Route: `mail/test`
- Permissions: `mail.view`, `mail.test`

Ozellikler:

- Env tabanli mail konfigurasyon durumunu listeler
- Test e-posta gonderimi yapar
- Son 20 gonderim kaydini (mail log) gosterir
- Mail sablon yonetim ekranina gecis saglar (`mail/templates`)

### Mail Sablon Yonetimi

Yonetim menusu altinda `Mail Sablonlari` ekrani bulunur.

- Route: `mail/templates`
- Permission: `mail.view`

Ozellikler:

- Yeni sablon olusturma
- Var olan sablonlari duzenleme
- Ozel sablon silme
- Sistem sablonlarini koruma (`is_system=1`, silinemez)
- Placeholder listesi gosterimi
- TinyMCE ile HTML govde duzenleme

Core tarafinda varsayilan sablonlar otomatik senkronlanir:

- `auth.password_reset`
- `queue.test_mail`
- `mail.test_manual`
- `users.session_dropped`
- `users.lock_key_reset`

Notlar:

- Sablon tablosu: `mail_templates`
- Kurulum sonrasi gerekirse `Ayarlar -> Eksikleri Kur` calistirin
- Detayli dokuman: `docs/MAIL_TEMPLATES.md`

Not:

- `MAIL_HOST` doluysa SMTP ile gonderim denenir
- `MAIL_HOST` bos ise PHP `mail()` fallback kullanilir

## Audit Modulu

Yonetim menusu altinda `Audit Log` ekrani bulunur.

- Route: `audit/list`
- Permission: `audit.view`

Ozellikler:

- Login/logout ve temel yonetim aksiyonlari icin audit kaydi toplar
- Son 200 kaydi listeler
- Status/module/action/user filtreleme sunar
- Her kayit icin route, method, IP ve detay JSON bilgisi gosterir

## Settings Modulu

Yonetim menusu altinda `Ayarlar` ekrani bulunur.

- Route: `settings/view`
- Permissions: `settings.view`, `settings.update`

Ozellikler:

- Uygulama adini panelden guncelleme (`app.name`)
- REST API aktif/pasif yonetimi (`api.enabled`)
- Mail konfigurasyonunu panelden yonetme (DB override)
- Mail sifresi icin guvenli guncelleme (bos birakilirsa mevcut deger korunur)
- Ayar degisikliklerini audit log'a yazma
- Sistem tablo + indeks kontrolu ve eksiklerini panelden tek tikla tamamlama
- Session verilerini (maskelenmis) modal ile goruntuleme

## Profile Modulu

Yonetim menusu uzerinden profil sayfasi:

- Route: `profile/view`
- Permissions: `profile.view`, `profile.edit`

Ek ozellik:

- Super Admin icin profil ekraninda API token olusturma bolumu
- Super Admin icin API token listeleme + revoke islemi
- Olusturulan token guvenlik geregi sadece bir kez gosterilir
- Token satirinda kopyalama ikonu vardir (guvenlik geregi sadece bu oturumda olusturulan tokenlar kopyalanabilir)
- Profil ekraninda kullanici 4-6 haneli lock key tanimlayabilir ve oturum kilitlemeyi aktif/pasif yapabilir

## Auth Lock (Oturum Kilitleme)

- Navbar'da `user-key` ikonu ile aktif oturum kilitlenebilir
- Kilitli ekranda lock key ile oturum acilir
- `users.session.drop` yetkisi olan kullanici hedef kullanicinin aktif oturumlarini sonlandirabilir
- `users.lock.reset` yetkisi olan kullanici hedef kullanicinin lock key bilgisini sifirlayip lock ozelligini pasif yapabilir
- Bu iki yonetim islemi sonrasi hedef kullaniciya bildirim gonderilir

## Queue Modulu

Yonetim menusu altinda `Jobs Queue` ekrani bulunur.

- Route: `queue/view`
- Permissions: `queue.view`, `queue.manage`

Ozellikler:

- Asenkron job kuyrugu (`jobs_queue` tablosu)
- Test mail job'i kuyruga ekleme
- Worker `run once` tetikleme
- Failed joblari tekrar kuyruğa alma

CLI:

- `php shell.php queue:work-once [queue_name]`
- `php shell.php queue:work [max_jobs] [queue_name]`

## Backup / Restore Modulu

Yonetim menusu altinda `Backup Restore` ekrani bulunur.

- Route: `backup/view`
- Permissions: `backup.view`, `backup.create`, `backup.restore`, `backup.download`, `backup.delete`

Ozellikler:

- `mysqldump` ile SQL backup olusturma
- Kayitli backup dosyalarini listeleme
- Backup dosyasini panelden indirme
- Tek tik backup dogrulama (SHA-256 + dry-run restore)
- Tek tik restore komutu calistirma
- Backup kaydini ve dosyasini silme
- Restore gecmisini loglama
- Otomatik retention temizligi (son N backup tutulur)

CLI:

- `php shell.php backup:create [label]`
- `php shell.php backup:restore <backup_id>`
- `php shell.php backup:verify <backup_id>`
- `php shell.php backup:cleanup [keep_count]`

Not:

- Bazi `mysqldump` istemcileri `--ssl-mode` desteklemez (MariaDB gibi). Bu nedenle sadece genel `--ssl` flag'i kullaniliyor.
- `DB_SSL_MODE=DISABLED` veya `PREFERRED` iken ekstra SSL parametresi gecilmez.
- Restore testinde (`Dogrula`) gecici bir veritabani olusturulur ve islem sonunda otomatik silinir. DB kullanicisinin `CREATE/DROP DATABASE` yetkisi olmalidir.
