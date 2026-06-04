# Kirpi Intelligence Platform (KIP)

## Core Hazırlık Notu

KIP geliştirmesine geçmeden önce Kirpi Core tarafında aşağıdaki yapısal standartlar tamamlandı:

- AI schema registry temeli ve standart modüllerde `ai/schema.json` yayınlama modeli.
- Yetki kontrollü schema discovery.
- Schema discovery için JSON/CSV/XLS export standardı.
- Ortak notification metadata modeli.
- Template Registry ve Documents Registry.
- Server-side CSV/XLS export standardı.
- Notifications, Documents, Audit, Users ve Roles modüllerinde export entegrasyonu.
- Roles için Permission Catalog ve Role-Permission Matrix export.
- UTF-8 çeviri standardı.
- Tema, layout ve PWA temeli.

Bu hazırlıklar KIP Faz 2 ve sonrası için veri keşfi, yetki sınırları, event analizi ve rapor üretimi altyapısını standart hale getirir.

---

## Amaç

Kirpi Core içerisine entegre edilecek ortak bir yapay zeka ve bilgi erişim platformu geliştirmek.

Bu platformun amacı chatbot üretmek değildir.

Amaç;

* Doğal dil ile veri sorgulama
* Kurumsal veri analizi
* Yetkili veri erişimi
* İş süreçlerinin gözlemlenmesi
* Tool Calling
* Event Driven Intelligence
* Gelecekteki isoAI altyapısının oluşturulması

olarak belirlenmiştir.

---

# Temel Prensipler

## 1. AI ürünlerin içinde değil Core içerisinde bulunmalıdır

Yanlış yaklaşım:

```text
Kalibre+ -> AI
HR+ -> AI
CMS+ -> AI
QDMS -> AI
```

Doğru yaklaşım:

```text
Kalibre+
HR+
CMS+
QDMS
      ↓
Kirpi Intelligence Platform
      ↓
Kirpi Core
```

Bütün ürünler aynı AI altyapısını kullanmalıdır.

---

## 2. Model bağımlılığı oluşturulmamalıdır

Sistem herhangi bir modele bağlı olmamalıdır.

Desteklenmesi planlanan modeller:

* Needle
* Qwen
* OpenAI
* Gemini
* Claude
* DeepSeek

Model değişikliği ürünleri etkilememelidir.

---

## 3. Veri hiçbir zaman doğrudan dış modellere gönderilmemelidir

Temel güvenlik prensibi:

```text
Database
   ↓
Kirpi Core
   ↓
AI Gateway
   ↓
LLM
```

Asla:

```text
LLM
 ↓
Database
```

olmamalıdır.

---

## 4. AI mevcut RBAC sistemini aşamaz

AI;

* Yetki kontrolü yapamaz
* Yetki atlayamaz
* Kullanıcının erişemediği veriyi göremez

Bütün sorgular mevcut izin sisteminden geçmelidir.

---

# Sistem Mimarisi

```text
Kirpi Intelligence Platform

├── Schema Registry
├── Vector Search
├── Query Engine
├── Tool Registry
├── Model Manager
├── AI Gateway
├── AI Permissions
├── AI Audit Log
├── Event Bus
└── Insight Engine
```

---

# Faz 1 — Schema Registry

## Amaç

Sistemdeki tüm modüllerin veri yapılarının merkezi olarak tanımlanması.

Örnek:

```json
{
  "module": "calibration",
  "entity": "device",
  "table": "devices",
  "description": "Calibration devices",
  "fields": [
    {
      "name": "serial_number",
      "description": "Device serial number"
    },
    {
      "name": "next_calibration_date",
      "description": "Next calibration date"
    }
  ]
}
```

Her modül kendi şemasını yayınlayacaktır.

Örnek modüller:

* Kalibre+
* HR+
* CMS+
* QDMS
* Teklifbaz

## Discovery Export Standardı

KIP servisleri schema bilgisini Core üzerinden dışarı almalıdır.

Standart endpoint:

```text
ai/actions/export-schema?format=json
```

Desteklenen formatlar:

* JSON
* CSV
* XLS

Export mevcut discovery filtrelerini kullanır:

* `module`
* `entity`
* `table`
* `permission`
* `discovery_q`
* `filterable_only`
* `include_sensitive`
* `limit`

Hassas alanlar varsayılan olarak dışarı verilmez. `include_sensitive=1` yalnızca `ai.schema.manage` yetkisi olan kullanıcılar için çalışır.

## Schema Quality Gate

KIP Faz 2 metadata indeksleme öncesinde schema kayıtları kalite kontrolünden geçmelidir.

Kontrol edilen başlıklar:

* Eksik entity açıklaması
* Eksik permission slug
* Aktif field içermeyen entity
* Eksik field açıklaması
* Eksik field tipi
* Hassas olabilecek ama `is_sensitive` işaretlenmemiş alanlar

Hassas alan adayları aşağıdaki kelimelerle taranır:

```text
password, token, secret, email, ip, path, payload, json, body, agent, hash, key
```

Kalite raporu endpoint'i:

```text
ai/actions/export-quality?format=json
```

Bu endpoint yalnızca `ai.schema.manage` yetkisi olan kullanıcılar tarafından kullanılmalıdır.

---

# Faz 2 — Vector Search

## Amaç

Doğal dil ile ilgili veri yapılarının bulunması.

Örnek:

Kullanıcı:

```text
Bu ay geciken kalibrasyonlar
```

Vektör arama sonucu:

```text
Calibration
Device
Due Date
Completed Date
Customer
```

ilgili entity'ler döndürülür.

Bu aşamada sadece metadata indekslenir.

Gerçek veriler indekslenmez.

---

# Faz 3 — Query Engine

## Amaç

Doğal dil sorgularını güvenli SQL sorgularına dönüştürmek.

Akış:

```text
Soru
 ↓
Schema Search
 ↓
LLM
 ↓
SQL Generation
 ↓
SQL Guard
 ↓
Database
 ↓
Result
 ↓
Summary
```

---

## SQL Guard

Tehlikeli sorgular engellenmelidir.

İzin verilen:

```sql
SELECT
```

Engellenen:

```sql
DELETE
UPDATE
DROP
ALTER
TRUNCATE
```

İlk sürüm yalnızca READ ONLY çalışmalıdır.

---

# Faz 4 — AI Gateway

## Amaç

Ürünlerin model bağımsız çalışması.

Ürünler:

```php
AI::ask($question);
```

çağırır.

Model seçimini Core yapar.

Örnek:

```text
Basit veri sorgusu
↓
Text-to-SQL
```

```text
Basit analiz
↓
Qwen Local
```

```text
Derin analiz
↓
OpenAI / Gemini
```

---

# Faz 5 — Tool Registry

## Amaç

Modüllerin fonksiyonlarını AI tarafından çağrılabilir hale getirmek.

Örnek:

```json
{
  "name": "create_calibration",
  "description": "Create calibration task"
}
```

```json
{
  "name": "list_overdue_devices",
  "description": "List overdue devices"
}
```

Her modül kendi araçlarını yayınlar.

---

# Faz 6 — Needle Integration

Needle ana sohbet modeli olarak kullanılmayacaktır.

Needle'ın görevi:

* Intent Detection
* Tool Selection
* Function Calling

Örnek:

Kullanıcı:

```text
Kalibrasyonu geçen cihazları getir
```

Needle:

```json
{
  "tool": "list_overdue_devices"
}
```

üretir.

---

# Faz 7 — Local Intelligence Layer

Yerel çalışan küçük modeller.

Önerilen:

* Qwen3 0.6B
* Qwen3 1.7B

Görevleri:

* Özetleme
* Trend analizi
* Rapor yorumlama
* İçgörü üretimi

Bu katman CPU üzerinde çalışabilmelidir.

---

# Faz 8 — External Intelligence Layer

Sadece gerekli durumlarda kullanılacaktır.

Örnek:

* Kapsamlı rapor üretimi
* Uzun dönem trend analizi
* Karmaşık kalite değerlendirmeleri

Bu katmanda:

* OpenAI
* Gemini
* Claude

kullanılabilir.

---

# Veri Güvenliği Stratejisi

## Veri Minimizasyonu

Dış modele sadece gerekli veri gönderilir.

Yanlış:

```text
Müşteri adı
Personel adı
Telefon
E-posta
Tam kayıtlar
```

Doğru:

```json
{
  "late_devices": 18,
  "late_percentage": 12.4
}
```

---

## PII Masking

Maskeleme uygulanmalıdır.

Örnek:

```text
CUSTOMER_42
USER_17
DEVICE_91
```

---

## AI Data Firewall

Kirpi Core içerisinde bir güvenlik katmanı bulunmalıdır.

Görevleri:

* Veri maskeleme
* Prompt filtreleme
* Çıktı filtreleme
* DLP kontrolleri

---

# AI Audit Log

Bütün AI işlemleri kayıt altına alınmalıdır.

Kayıt örnekleri:

```text
Kim sordu?
Ne sordu?
Hangi model kullanıldı?
Hangi sorgu çalıştı?
Hangi sonuç döndü?
```

Kurumsal izlenebilirlik sağlanmalıdır.

---

# Gelecek Vizyonu — isoAI

Uzun vadeli hedef:

Kullanıcının sürekli soru sorması yerine sistemin olayları gözlemlemesi.

Örnek:

```json
{
  "event": "calibration_overdue",
  "days": 14
}
```

Insight Engine:

```text
Bu durum son 3 ayda artış göstermektedir.
Kök neden analizi önerilir.
```

şeklinde öneriler üretebilir.

---

# İlk 90 Gün Yol Haritası

## Sprint 1

* Core AI Module
* Schema Registry
* AI Audit Log
* Model Adapter Interface

## Sprint 2

* Vector Search
* Entity Discovery
* Metadata Indexing

## Sprint 3

* Text-to-SQL
* SQL Guard
* Result Summarization

## Sprint 4

* Qwen Local Integration
* AI Gateway

## Sprint 5

* Tool Registry
* Needle Integration

---

# Sonuç

Kirpi Intelligence Platform;

bir chatbot sistemi değil,

Kurumsal Bilgiye Erişim,
Kurumsal İçgörü Üretimi,
Tool Calling,
Event Intelligence
ve gelecekteki isoAI altyapısını sağlayan Core seviyesinde stratejik bir platform olarak konumlandırılacaktır.
