# KirpiCore Setup

KirpiCore veritabani kurulumu iki katmanli ilerler:

- `core` temel tablolari kurar (`roles`, `users`)
- moduller kendi schema dosyalarini `modules/<modul>/database/*.sql` altinda tutar

## 1) Ortam dosyasi

```bash
cp .env.example .env
```

`.env` icinde DB ayarlarinizi guncelleyin (`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`).

## 2) Tum kurulumu tek komutla yap

```bash
php shell.php db:install
```

Bu komut:

1. Veritabani yoksa olusturur (`db:create`)
2. `database/core.sql` dosyasini uygular
3. Tum modullerdeki `database/*.sql` dosyalarini uygular
4. Permission semasi varsa katalogu senkronlar

## 3) Parcali kurulum komutlari

```bash
php shell.php db:create
php shell.php db:core:install
php shell.php db:modules:install
```

Tek modul semasi yuklemek icin:

```bash
php shell.php db:modules:install notifications
```

## 4) Geriye donuk komutlar

Asagidaki komutlar hala calisir:

```bash
php shell.php db:permissions:install
php shell.php db:notifications:install
```

## 5) Kontrol

```bash
php shell.php db:status
php shell.php db:tables
```
