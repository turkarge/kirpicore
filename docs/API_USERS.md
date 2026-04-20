# KirpiCore API v1 - Users

Bu dokuman, KirpiCore icindeki `users` API endpointlerinin teknik kullanimini anlatir.

## Base URL

- Production ornek: `https://core.kirpinetwork.com`

## Auth

- Header: `Authorization: Bearer <access_token>`
- Token alma endpointi: `POST /api/v1/auth/token`

## Ortak Cevap Formati

```json
{
  "status": "success",
  "message": "OK",
  "data": {},
  "meta": {}
}
```

Hata durumunda:

```json
{
  "status": "error",
  "message": "Aciklayici hata mesaji",
  "data": {}
}
```

## Endpointler

### 1) Kullanicilari Listele

- Method: `GET`
- Path: `/api/v1/users`
- Permission: `users.view`

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

---

### 2) Kullanici Olustur

- Method: `POST`
- Path: `/api/v1/users`
- Permission: `users.create`

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

Ornek:

```bash
curl -X POST "https://core.kirpinetwork.com/api/v1/users" \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test.user@kirpi.local",
    "password": "123456",
    "password_confirm": "123456",
    "role_id": 2,
    "is_active": true
  }'
```

---

### 3) Kullanici Guncelle

- Method: `PATCH`
- Path: `/api/v1/users/{id}`
- Permission: `users.edit`

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

Ornek:

```bash
curl -X PATCH "https://core.kirpinetwork.com/api/v1/users/5" \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Updated Name",
    "is_active": true
  }'
```

---

### 4) Kullanici Durumunu Guncelle

- Method: `POST`
- Path: `/api/v1/users/{id}/status`
- Permission: `users.status`

Body (JSON):

```json
{
  "is_active": false
}
```

Notlar:

- Super Admin kullanici pasife alinamaz.

Ornek:

```bash
curl -X POST "https://core.kirpinetwork.com/api/v1/users/5/status" \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{"is_active": false}'
```

## Sık HTTP Kodlari

- `200` Basarili
- `201` Kayit olusturuldu
- `401` Token yok/gecersiz/suresi dolmus
- `403` Yetki yok
- `404` Kayit bulunamadi
- `422` Dogrulama hatasi
- `429` Rate limit asildi
- `500` Sunucu hatasi

## Throttle

API endpointleri su limitlere tabidir:

- `THROTTLE_API_*` genel API limiti
- `THROTTLE_API_AUTH_*` token endpoint limiti

