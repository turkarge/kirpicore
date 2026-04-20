# KirpiCore API v1 - Users

Bu dokuman, KirpiCore `users` API endpointlerinin pratik kullanimini anlatir.

## Base URL

- Production: `https://core.kirpinetwork.com`

## Hizli Test Yontemleri

### 1) Admin panelinden API Test Merkezi

- Yol: `Yonetim -> API Test`
- Sayfa: `settings/api-test`
- Bu ekranda:
  - Method secersin
  - Endpoint girersin
  - Bearer token eklersin
  - JSON body gonderebilirsin
  - HTTP status + response gorursun

### 2) PowerShell ile test

```powershell
$base = "https://core.kirpinetwork.com"
$token = "BURAYA_BEARER_TOKEN"

Invoke-RestMethod -Uri "$base/api/v1/me" -Headers @{ Authorization = "Bearer $token" } -Method GET
Invoke-RestMethod -Uri "$base/api/v1/users?page=1&per_page=5" -Headers @{ Authorization = "Bearer $token" } -Method GET
```

## Auth

- Header: `Authorization: Bearer <access_token>`
- Token endpoint: `POST /api/v1/auth/token`

Token alma ornegi:

```bash
curl -X POST "https://core.kirpinetwork.com/api/v1/auth/token" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@kirpi.local",
    "password": "123456",
    "token_name": "api-test",
    "scopes": ["profile:read", "users:read"]
  }'
```

Not:
- `401 Kullanici bilgileri hatali.` donerse email/sifre yanlistir.

## Ortak Cevap Formati

Basarili cevap:

```json
{
  "status": "success",
  "message": "OK",
  "data": {},
  "meta": {}
}
```

Hata cevabi:

```json
{
  "status": "error",
  "message": "Aciklayici hata mesaji",
  "error_code": "validation_error",
  "data": {}
}
```

Not:
- Tum hata cevaplarinda `error_code` bulunur.

## Endpointler

### 1) Kullanicilari Listele

- Method: `GET`
- Path: `/api/v1/users`
- Permission: `users.view`
- Scope: `users:read`

Query parametreleri:

- `page` (int, default `1`)
- `per_page` (int, default `20`, max `100`)
- `search` (string)
- `role_id` (int)
- `status` (`0` veya `1`)

Ornek:

```bash
curl "https://core.kirpinetwork.com/api/v1/users?page=1&per_page=20" \
  -H "Authorization: Bearer <TOKEN>"
```

### 2) Kullanici Olustur

- Method: `POST`
- Path: `/api/v1/users`
- Permission: `users.create`
- Scope: `users:create`

Body (JSON):

```json
{
  "name": "Test User",
  "email": "test.user@kirpi.local",
  "password": "123456",
  "password_confirm": "123456",
  "role_id": 2,
  "is_active": true
}
```

Notlar:

- `name`, `email`, `password` zorunludur.
- `password` min 6 karakter.
- `password_confirm` verilmezse `password` ile ayni kabul edilir.
- `role_id` opsiyonel.
- `is_active` opsiyonel (default `true`).

### 3) Kullanici Guncelle

- Method: `PATCH`
- Path: `/api/v1/users/{id}`
- Permission: `users.edit`
- Scope: `users:update`

Body (JSON) - en az bir alan:

```json
{
  "name": "Yeni Ad",
  "email": "yeni.mail@kirpi.local",
  "password": "newpass123",
  "password_confirm": "newpass123",
  "role_id": 2,
  "is_active": true
}
```

Notlar:

- Super Admin kullanici pasife alinamaz.
- Sistemde en az 1 aktif Super Admin kalacak sekilde kontrol vardir.

### 4) Kullanici Durumunu Guncelle

- Method: `POST`
- Path: `/api/v1/users/{id}/status`
- Permission: `users.status`
- Scope: `users:status`

Body (JSON):

```json
{
  "is_active": false
}
```

Not:

- Super Admin kullanici pasife alinamaz.

## Postman Collection

Asagidaki URL'lerden biriyle collection indirebilirsin:

- `/api/v1/postman-collection`
- `/api/v1/postman`
- `/api/v1/postman-collection.json`

Tam URL ornegi:

`https://core.kirpinetwork.com/api/v1/postman-collection`

## Scope Notlari

- `*` -> tum API scope'lari acik
- `profile:read` -> `/api/v1/me`
- `users:read` -> `GET /api/v1/users`
- `users:create` -> `POST /api/v1/users`
- `users:update` -> `PATCH /api/v1/users/{id}`
- `users:status` -> `POST /api/v1/users/{id}/status`

## Sik HTTP Kodlari

- `200` Basarili
- `201` Kayit olusturuldu
- `401` Token yok/gecersiz/suresi dolmus veya kimlik bilgisi yanlis
- `403` Yetki yok
- `404` Kayit bulunamadi
- `422` Dogrulama hatasi
- `429` Rate limit asildi
- `500` Sunucu hatasi

## Throttle

API endpointleri su limitlere tabidir:

- `THROTTLE_API_*` genel API limiti
- `THROTTLE_API_AUTH_*` token endpoint limiti

## CLI Smoke Test

Tek komutla temel API akislarini test edebilirsin:

```bash
php shell.php api:smoke https://core.kirpinetwork.com admin@kirpi.local 123456
```

Bu komut su kontrolleri yapar:
- Token alma (full scope)
- `GET /api/v1/me`
- `GET /api/v1/users`
- Limited token ile scope deny kontrolu (`POST /api/v1/users` -> `403 scope_denied`)
