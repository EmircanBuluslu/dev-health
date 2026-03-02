# Changelog

Bu dosya Dev:Health projesindeki tüm önemli değişiklikleri içerir.

Format [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) standardına uygundur.

## [1.0.0] - 2026-03-02

### Eklenenler
- İlk stabil sürüm
- DebugCheck: Production ortamında debug modu kontrolü
- DebugCodeCheck: Kodda unutulmuş console.log(), dd(), dump() gibi debug kodlarını tespit etme
  - JavaScript: console.log, console.debug, console.error, alert, debugger
  - PHP: dd(), dump(), var_dump(), print_r(), ray()
  - Vue/React/TypeScript dosyaları desteği
  - Word boundary kullanarak false positive önleme
- RouteAuthCheck: Auth middleware eksik rotaları tespit etme
- DuplicateRouteCheck: Çakışan rota tespiti
- UnusedRouteCheck: Kullanılmayan rota ve controller metodu tespiti
  - Test/debug/temp gibi şüpheli rotalar
  - İsimsiz closure rotaları
  - Duplicate rota isimleri
  - Kullanılmayan CRUD metodları
- EnvSyncCheck: .env ve .env.example senkronizasyon kontrolü
- MigrationConflictCheck: Migration çakışma tespiti
- CLI, JSON ve HTML çıktı formatları
- A-D arası risk notlandırma sistemi
- Renkli terminal çıktısı
- Laravel 10 ve 11 desteği
- PHP 8.2+ desteği
- Service Provider auto-discovery

### Güvenlik
- Production ortamında debug modu uyarısı
- Auth middleware eksikliği tespiti
- Debug kodu tespiti (veri sızıntısı riski)

## [Unreleased]

### Planlanıyor
- Özel kontrol ekleme desteği
- Konfigürasyon dosyası desteği
- Daha fazla güvenlik kontrolü
- CI/CD entegrasyonu
- Slack/Discord bildirim desteği
