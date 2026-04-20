# KirpiCore API Release Checklist

Bu dokuman, API degisikliklerini production ortamina guvenli sekilde almak icin kullanilir.

## 1) Pre-Release (Kod Hazirlik)

- [ ] Endpoint degisiklikleri `docs/API_USERS.md` icinde guncellendi.
- [ ] Yeni hata senaryolari icin `error_code` tanimlari eklendi.
- [ ] Scope degisiklikleri (`profile:read`, `users:*`) dogrulandi.
- [ ] `php -l` ile degisen PHP dosyalarinda syntax kontrolu temiz.
- [ ] Gerekli DB schema dosyalari (`modules/*/database/*.sql`) guncellendi.

## 2) Deploy Sonrasi Teknik Kontrol

- [ ] `Ayarlar -> Eksikleri Kur` calistirildi.
- [ ] API root endpoint: `GET /api/v1` -> `200`.
- [ ] Postman collection endpoint: `GET /api/v1/postman-collection` -> `200`.
- [ ] API Metrics sayfasi aciliyor: `api/metrics`.

## 3) Smoke Test

- [ ] CLI smoke komutu basarili:

```bash
php shell.php api:smoke https://core.kirpinetwork.com admin@kirpi.local <SIFRE>
```

- [ ] Beklenen adimlar:
  - full-scope token alinir
  - `/api/v1/me` ve `/api/v1/users` 200 doner
  - limited token ile `POST /api/v1/users` -> `403 scope_denied`

## 4) Operasyonel Kontrol (ilk 24 saat)

- [ ] `Yonetim -> API Metrics` ekraninda 5xx artisi yok.
- [ ] 401/403 oranlari beklenen seviyede.
- [ ] 429 oraninda beklenmeyen sicrama yok.
- [ ] Ortalama response suresi kabul edilebilir seviyede.

## 5) Rollback Karari (Gerekiyorsa)

- [ ] 5xx oraninda kalici yukselis varsa rollback planina gec.
- [ ] Kritik endpointlerde surekli 401/403 varsa scope/permission degisikliklerini geri al.
- [ ] DB migration kaynakli problemde son guvenli versiyona don + yedekten dogrula.
