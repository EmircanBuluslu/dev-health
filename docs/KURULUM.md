# Dev:Health Kurulum Rehberi

## Hızlı Kurulum

### 1. Composer ile Kurulum

```bash
composer require devhealth/laravel-health --dev
```

### 2. Kullanım

```bash
php artisan dev:health
```

Bu kadar! Laravel'in service provider auto-discovery özelliği sayesinde paket otomatik olarak yüklenir.

## Farklı Çıktı Formatları

### JSON Formatı
```bash
php artisan dev:health --format=json --output=rapor.json
```

### HTML Formatı
```bash
php artisan dev:health --format=html --output=rapor.html
```

## Manuel Kurulum (Gerekirse)

Eğer auto-discovery çalışmazsa, `config/app.php` veya `bootstrap/providers.php` dosyasına ekleyin:

```php
// Laravel 11+
return [
    App\Providers\AppServiceProvider::class,
    DevHealth\LaravelHealth\HealthServiceProvider::class,
];
```

## Gereksinimler

- PHP 8.2+
- Laravel 10 veya 11

## Sorun Giderme

### "Command not found" hatası alıyorsanız:

```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### Kontroller çalışmıyorsa:

Ortam değişkeninizi kontrol edin. Paket sadece `local`, `development` ve `staging` ortamlarında çalışır.

```bash
# .env dosyasında
APP_ENV=local
```
