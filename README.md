# Dev:Health - Laravel Sağlık Kontrolü

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-10%20%7C%2011-FF2D20?style=flat&logo=laravel" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License">
</p>

Laravel projelerinizde güvenlik, yapılandırma ve yapısal riskleri tespit eden CLI aracı.

## ✨ Özellikler

- 🔒 **Güvenlik Kontrolleri**: Debug modu, auth middleware eksiklikleri
- 🔄 **Yapılandırma Kontrolleri**: .env senkronizasyonu
- 🛣️ **Rota Kontrolleri**: Çakışan rotalar, eksik middleware'ler
- 📊 **Migration Kontrolleri**: Çakışan kolon tanımları
- 🎨 **Çoklu Format Desteği**: CLI, JSON, HTML
- 📈 **Risk Notlandırma**: A-D arası otomatik değerlendirme

## 🚀 Hızlı Başlangıç

### Kurulum

```bash
composer require devhealth/laravel-health --dev
```

Laravel'in auto-discovery özelliği sayesinde paket otomatik olarak yüklenir. Ekstra yapılandırma gerekmez!

### Kullanım

```bash
php artisan dev:health
```

### Çıktı Formatları

```bash
# CLI formatında (varsayılan)
php artisan dev:health

# JSON formatında
php artisan dev:health --format=json --output=rapor.json

# HTML raporu
php artisan dev:health --format=html --output=rapor.html
```

## 📋 Kontroller

| Kontrol | Açıklama | Seviye |
|---------|----------|--------|
| **DebugCheck** | Production ortamında `APP_DEBUG=true` kontrolü | 🔴 Kritik |
| **DebugCodeCheck** | Kodda unutulmuş `console.log()`, `dd()`, `dump()` tespiti | 🟡 Orta |
| **RouteAuthCheck** | Auth middleware eksik rotaları tespit eder | 🔴 Kritik |
| **DuplicateRouteCheck** | Aynı URI+method'a sahip çakışan rotaları bulur | 🟡 Orta |
| **UnusedRouteCheck** | Test/debug rotaları ve kullanılmayan metodları tespit eder | 🟢 Düşük |
| **EnvSyncCheck** | `.env` ve `.env.example` senkronizasyonu | 🟡 Orta |
| **MigrationConflictCheck** | Migration'larda çakışan kolon tanımları | 🟡 Orta |

## 📊 Risk Seviyeleri

| Not | Durum | Açıklama |
|-----|-------|----------|
| **A** | 🟢 Mükemmel | 0-2 sorun |
| **B** | 🔵 İyi | 3-5 sorun |
| **C** | 🟡 Dikkat | 6-10 sorun |
| **D** | 🔴 Kritik | 10+ sorun |

## 📸 Örnek Çıktı

```
🏥 Dev:Health başlatılıyor...

═══ Debug Modu Kontrolü ═══
✓ Debug modu development ortamında açık (normal)

═══ Rota Kimlik Doğrulama Kontrolü ═══
⚠ Rota kimlik doğrulama middleware'i içermiyor: GET /api/users
   📁 routes/api.php:15
   💡 Bu rotaya 'auth:sanctum' middleware'i ekleyin

═══ 📊 Özet ═══
Toplam Kontrol: 25
✓ Başarılı: 20
⚠ Uyarı: 5
✗ Hata: 0

Skor: 75/100
Not: B
İyi - Birkaç küçük iyileştirme yapılabilir
```

## 🔧 Gereksinimler

- PHP 8.2 veya üzeri
- Laravel 10.x veya 11.x

## 📚 Dokümantasyon

- [Kurulum Rehberi](docs/KURULUM.md)
- [Debug Kodu Kontrolü](docs/DEBUG_CODE_CHECK.md)


---

