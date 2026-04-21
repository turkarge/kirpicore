# Kirpi Core Setup Guide

Bu dokuman, Kirpi Core sisteminin lokal ve Dokploy uretim ortaminda standart kurulum akisini tanimlar.

## 1. Kurulum Modlari

- Lokal Docker kurulumu (gelistirme/test)
- Dokploy uzerinden production kurulumu
- CLI ile veritabani kurulum/onarim islemleri

## 2. Lokal Kurulum (Docker)

```bash
docker compose up -d --build
```

Erisim:

- Uygulama: `http://localhost:8080`
- Setup: `http://localhost:8080/setup.php`

## 3. Dokploy Kurulumu

### 3.1 Uygulama Olusturma

1. Dokploy panelinde yeni `Compose Application` olusturun.
2. Repo olarak Kirpi Core reposunu secin.
3. Compose file: `docker-compose.yml`.

### 3.2 Environment Settings

Asagidaki blok, Dokploy icin referans production baseline'dir.

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

SESSION_COOKIE_DOMAIN=
SESSION_IDLE_TIMEOUT_SECONDS=7200
SESSION_ID_ROTATE_SECONDS=900
SECURITY_HEADERS_ENABLED=true
AUTH_LOGIN_COVER_IMAGE=https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1400&q=80

DB_HOST=db
DB_PORT=3306
DB_NAME=kirpicore
DB_USER=root
DB_PASS=CHANGE_ME
AUTO_DB_INSTALL=true
DB_SSL_MODE=DISABLED
AUTO_DB_ENSURE_MISSING=false
AUTO_DB_ENSURE_INDEXES=true

MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_mail@example.com
MAIL_PASSWORD=CHANGE_ME
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_mail@example.com
MAIL_FROM_NAME="Kirpi Core"

AUTO_WEB_SETUP=false
SETUP_KEY=

BACKUP_RETENTION_COUNT=20
BACKUP_VERIFY_DRY_RUN=true
BACKUP_INCLUDE_SYSTEM_TABLES=false

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

API_TOKEN_TTL_SECONDS=2592000
API_REQUEST_LOG_RETENTION_DAYS=90
```

### 3.3 Deploy Sonrasi Akis

Container acilisinda sistem:

1. DB baglantisini bekler
2. `php shell.php db:install` calistirir
3. Uygulamayi ayaga kaldirir

Saglik endpoint:

- `GET /healthz`

## 4. Web Setup (Opsiyonel)

`AUTO_WEB_SETUP=true` iken:

- `/setup.php` uzerinden setup key ile kurulum yapilabilir.

Guvenlik onerisi:

- Kurulum bittikten sonra `AUTO_WEB_SETUP=false`
- `SETUP_KEY` degerini bosaltin veya degistirin

## 5. CLI Setup ve Bakim Komutlari

Tum kurulum:

```bash
php shell.php db:install
```

Parcali kurulum:

```bash
php shell.php db:create
php shell.php db:core:install
php shell.php db:modules:install
```

Tek modul kurulumu:

```bash
php shell.php db:modules:install notifications
```

Durum kontrol:

```bash
php shell.php db:status
php shell.php db:tables
```

## 6. Dogrulama Checklist

- `BASE_URL` dogru mu?
- `DB_*` bilgileri dogru mu?
- `MAIL_*` bilgileri dogru mu?
- `APP_ENV=production` ve `APP_DEBUG=false` mi?
- `/healthz` endpoint'i `200` donuyor mu?
- Admin girisi ve temel sayfalar aciliyor mu?

## 7. Sik Karsilasilan Sorunlar

### "Guvenlik dogrulamasi basarisiz oldu"

1. Tarayici cookie temizleyin
2. `SESSION_COOKIE_DOMAIN` degerini bos birakin
3. Yeniden deploy edin

### MySQL unhealthy / privilege table hatalari

- Eski/uyumsuz MySQL startup argumanlarini kaldirin
- Bozuk/eski DB volume kullaniyorsaniz temiz volume ile yeniden baslatin

### "Yukleme dizini olusturulamadi"

- `uploads`, `uploads/avatars`, `logs`, `storage` dizinlerini ve yazma izinlerini kontrol edin

## 8. Ilgili Dokumanlar

- [README.md](README.md)
- [docs/MODULE_MANIFEST.md](docs/MODULE_MANIFEST.md)
- [docs/API_USERS.md](docs/API_USERS.md)
- [docs/MAIL_TEMPLATES.md](docs/MAIL_TEMPLATES.md)
