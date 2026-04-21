# Kirpi Core

Kurumsal, moduler ve Docker-odakli PHP yonetim altyapisi.

Bu dokuman, Kirpi Core sistemini lokal ortamda ve Dokploy uzerinden uretim ortaminda calistirmak icin tek kaynak kurulum kilavuzudur.

## 1. Genel Bakis

Kirpi Core modulleri:

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

Yapi ozellikleri:

- Modul manifest destegi (`modules/<module>/module.json`)
- Dinamik modul etkinlestirme/devre disi birakma
- Web tabanli setup + shell tabanli setup
- API token ve scope yonetimi
- Backup/restore, health metrics, throttle ve audit log

## 2. On Kosullar

- Docker + Docker Compose
- Dokploy (uretim kurulumu icin)
- MySQL 8.x (compose ile gelir)
- Domain + TLS (uretim ortami icin onerilir)

## 3. Hizli Baslangic (Lokal Docker)

```bash
docker compose up -d --build
```

Erisim:

- Uygulama: `http://localhost:8080`
- DB host (container ici): `db:3306`

Ilk kurulum:

- `http://localhost:8080/setup.php`

## 4. Dokploy ile Kurulum

### 4.1 Uygulama Olusturma

1. Dokploy panelinde yeni bir **Compose Application** olusturun.
2. Repository olarak bu projeyi secin.
3. Compose file olarak `docker-compose.yml` belirleyin.

### 4.2 Environment Settings

Asagidaki degerleri Dokploy `Environment Settings` alanina girin.

### Zorunlu Degiskenler

```env
APP_NAME="Kirpi Core"
APP_VER=1.0.15
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=Europe/Istanbul
APP_DEFAULT_ROUTE=dashboard/view
BASE_URL=https://core.kirpinetwork.com
APP_TRUST_PROXY=true
APP_LOCALE=tr

DB_HOST=db
DB_PORT=3306
DB_NAME=kirpicore
DB_USER=root
DB_PASS=CHANGE_ME
AUTO_DB_INSTALL=true
DB_SSL_MODE=DISABLED

MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_mail@example.com
MAIL_PASSWORD=CHANGE_ME
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_mail@example.com
MAIL_FROM_NAME="Kirpi Core"
```

### Guvenlik ve Session

```env
SESSION_COOKIE_DOMAIN=
SESSION_IDLE_TIMEOUT_SECONDS=7200
SESSION_ID_ROTATE_SECONDS=900
SECURITY_HEADERS_ENABLED=true
AUTH_LOGIN_COVER_IMAGE=https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1400&q=80
```

### Setup ve Otomatik DB Kontrolleri

```env
AUTO_WEB_SETUP=false
SETUP_KEY=
AUTO_DB_ENSURE_MISSING=false
AUTO_DB_ENSURE_INDEXES=true
```

Not:

- `AUTO_WEB_SETUP=true` yaparsaniz `setup.php` web arayuzu production'da acik olur.
- Production icin setup tamamlandiktan sonra `AUTO_WEB_SETUP=false` onerilir.

### Backup Ayarlari

```env
BACKUP_RETENTION_COUNT=20
BACKUP_VERIFY_DRY_RUN=true
BACKUP_INCLUDE_SYSTEM_TABLES=false
```

### Throttle Ayarlari

```env
THROTTLE_ENABLED=true
THROTTLE_LOGIN_LIMIT=5
THROTTLE_LOGIN_WINDOW=600
THROTTLE_LOGIN_BLOCK=900
THROTTLE_CRITICAL_LIMIT=10
THROTTLE_CRITICAL_WINDOW=60
THROTTLE_CRITICAL_BLOCK=120
THROTTLE_GLOBAL_POST_LIMIT=180
THROTTLE_GLOBAL_POST_WINDOW=60
THROTTLE_GLOBAL_POST_BLOCK=60
THROTTLE_API_LIMIT=120
THROTTLE_API_WINDOW=60
THROTTLE_API_BLOCK=120
THROTTLE_API_AUTH_LIMIT=10
THROTTLE_API_AUTH_WINDOW=300
THROTTLE_API_AUTH_BLOCK=600
```

### API Ayarlari

```env
API_TOKEN_TTL_SECONDS=2592000
API_REQUEST_LOG_RETENTION_DAYS=90
```

### 4.3 Deploy

Deploy sonrasi container acilis akisi:

1. DB baglantisini bekler
2. `php shell.php db:install` ile core + modul semalarini kurar
3. Uygulamayi Apache ile ayaga kaldirir

Health endpoint:

- `GET /healthz`

## 5. Setup (Web ve CLI)

### Web Setup

- URL: `/setup.php`
- Beklenen: setup key + admin bilgileri
- Sonuc: temel tablolar, roller, admin kullanicisi

### CLI Setup

```bash
php shell.php db:install
```

Parcali komutlar:

```bash
php shell.php db:create
php shell.php db:core:install
php shell.php db:modules:install
php shell.php db:status
php shell.php db:tables
```

## 6. Yonetim Menusu ve Kritik Moduller

- `settings/view`: genel ayarlar + eksik tablo/index kurulum araci
- `settings/modules`: modul yonetimi (enable/disable, dependency kontrolu)
- `security/view`: guvenlik izleme paneli
- `health/view`: health + metrics paneli
- `backup/view`: backup/restore/download/delete
- `queue/view`: job queue yonetimi
- `mail/test` ve `mail/templates`: mail test + template yonetimi

## 7. REST API (v1)

Base route: `api/v1`

Temel endpointler:

- `POST /api/v1/auth/token`
- `GET /api/v1/me`
- `GET /api/v1/users`
- `POST /api/v1/users`
- `PATCH /api/v1/users/{id}`
- `POST /api/v1/users/{id}/status`

Ek endpointler:

- `GET /api/v1`
- `GET /api/v1/postman-collection`
- `GET /api/v1/postman`
- `GET /api/v1/postman-collection.json`

Dokumanlar:

- `docs/API_USERS.md`
- `docs/API_RELEASE_CHECKLIST.md`
- `docs/API_ALERT_THRESHOLDS.md`

Smoke test:

```bash
php shell.php api:smoke https://core.kirpinetwork.com admin@kirpi.local 123456
```

## 8. Backup ve Restore

CLI:

```bash
php shell.php backup:create [label]
php shell.php backup:restore <backup_id>
php shell.php backup:verify <backup_id>
php shell.php backup:cleanup [keep_count]
```

Panel:

- Olustur
- Dogrula (checksum + dry-run)
- Restore
- Indir
- Sil

## 9. Sorun Giderme

### 9.1 "Guvenlik dogrulamasi basarisiz oldu"

Neden:

- Eski/yanlis cookie
- Domain degisimi sonrasi session uyumsuzlugu

Cozum:

1. Tarayici cookie temizleyin
2. `SESSION_COOKIE_DOMAIN` degerini bos birakin (onerilen)
3. Yeniden deploy edin
4. Tekrar giris yapin

### 9.2 MySQL container unhealthy

Logda `unknown variable 'default-authentication-plugin=mysql_native_password'` goruluyorsa:

- MySQL 8.4 ile uyumsuz eski startup argumanlari kaldirin.
- DB volume bozuk/eski ise temiz volume ile yeniden baslatin.

### 9.3 Upload dizini olusturulamadi

Kontrol edin:

- `uploads`
- `uploads/avatars`
- `logs`
- `storage`

Container kullanicisinin (`www-data`) yazma izni olmali.

## 10. Guvenlik Onerileri (Production)

- `APP_ENV=production`
- `APP_DEBUG=false`
- `AUTO_WEB_SETUP=false`
- `SETUP_KEY` bos veya rotate edilmis
- Guclu `DB_PASS`
- Guclu SMTP sifresi / app password
- Reverse proxy arkasinda `APP_TRUST_PROXY=true`
- Duzenli backup + restore testi

## 11. Ilgili Dokumanlar

- [SETUP.md](SETUP.md)
- [docs/MODULE_MANIFEST.md](docs/MODULE_MANIFEST.md)
- [docs/API_USERS.md](docs/API_USERS.md)
- [docs/MAIL_TEMPLATES.md](docs/MAIL_TEMPLATES.md)

## 12. Notlar

- Modul mimarisinde `module.json` geriye uyumlu tasarlanmistir; olmayan moduller yine calisir.
- Core modul bagimliliklari nedeniyle bazi moduller devre disi birakilamaz.
