# KirpiCore

Core moduller tamamlandi:

- `auth`
- `dashboard`
- `users`
- `roles`
- `profile`
- `notifications`

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

Opsiyonel:

- `AUTO_DB_INSTALL=true` (default: `true`)

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
