# KirpiCore API Alert Thresholds

Bu dokuman, API Metrics ekrani verilerine gore ilk alarm esiklerini tanimlar.
Baslangic degerleri 1-2 haftalik gozum icin konservatif tutulmustur.

## Izlenecek metrikler

- `total` (toplam istek)
- `status_401`
- `status_403`
- `status_429`
- `status_5xx`
- `avg_duration_ms`

## Onerilen alarm seviyeleri

### 1 Saat penceresi

- `status_5xx >= 10` -> Kritik
- `status_429 >= 40` -> Uyari
- `status_401 >= 60` -> Uyari (kimlik denemeleri artmis olabilir)
- `avg_duration_ms >= 1200` -> Uyari

### 24 Saat penceresi

- `status_5xx >= 50` -> Kritik
- `status_429 >= 300` -> Uyari
- `status_401 >= 500` -> Uyari
- `status_403 >= 500` -> Bilgilendirme/Uyari (scope veya role degisiklikleri kontrol edilmeli)

## Oran bazli ek kontrol (onerilir)

- `5xx_orani = status_5xx / total`
  - `%1+` -> Uyari
  - `%3+` -> Kritik

- `429_orani = status_429 / total`
  - `%5+` -> Uyari (throttle ayari veya istek paternleri incelenmeli)

## Olay aninda hizli aksiyon

- 401 artisi:
  - Token olusturma loglarini ve login kaynaklarini kontrol et.
  - Gerekiyorsa `THROTTLE_API_AUTH_*` limitlerini gecici sikilastir.

- 403 artisi:
  - Yeni scope/permission deployu var mi kontrol et.
  - Token scope secenekleri ve role yetkilerini gozden gecir.

- 429 artisi:
  - Trafik piki veya hatali istemci dongusu var mi bak.
  - `THROTTLE_API_*` degerlerini veriye gore tune et.

- 5xx artisi:
  - Son deploy diff'ini kontrol et.
  - DB durumu, queue, disk ve mail baglantilarini hizli denetle.

## 2 hafta sonra kalibrasyon

- Esikler canli trafige gore revize edilmeli.
- Hedef: yanlis pozitifleri azaltirken gercek sorunlari erken yakalamak.
