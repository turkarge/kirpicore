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
- `SESSION_COOKIE_DOMAIN=` (onerilen: bos birakin; isterseniz sadece host verin, ornek: `core.kirpinetwork.com`)
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

Endpointler:

- `POST /api/v1/auth/token` (email + password ile token alir)
- `GET /api/v1/me` (token sahibinin profil bilgisi)
- `GET /api/v1/users` (kullanici listesi, `users.view` yetkisi gerekir)

Not:

- API token tablosu: `api_tokens`
- Token olusturma endpoint'i brute-force'a karsi throttle ile korunur
- API cagrilari da throttle kapsamindadir

## Mail Modulu

Yonetim menusu altinda `Mail Test` ekrani bulunur.

- Route: `mail/test`
- Permissions: `mail.view`, `mail.test`

Ozellikler:

- Env tabanli mail konfigurasyon durumunu listeler
- Test e-posta gonderimi yapar
- Son 20 gonderim kaydini (mail log) gosterir

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
- Mail konfigurasyonunu panelden yonetme (DB override)
- Mail sifresi icin guvenli guncelleme (bos birakilirsa mevcut deger korunur)
- Ayar degisikliklerini audit log'a yazma
- Sistem tablo kontrolu ve eksik schema kurulumunu panelden tek tikla calistirma

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
