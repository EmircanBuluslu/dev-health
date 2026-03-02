# Dev:Health Paketini Yayınlama Rehberi

## 1. GitHub'a Yükleme

```bash
cd DevHealt
git init
git add .
git commit -m "Initial commit: Dev:Health v1.0.0"
git branch -M main
git remote add origin https://github.com/EmircanBuluslu/dev-health.git
git push -u origin main
```

## 2. Packagist'e Kayıt

1. [Packagist.org](https://packagist.org)'a giriş yapın
2. "Submit" butonuna tıklayın
3. GitHub repo URL'nizi girin: `https://github.com/EmircanBuluslu/dev-health`
4. "Check" butonuna tıklayın

## 3. Otomatik Güncelleme (Webhook)

GitHub repo ayarlarından:
1. Settings → Webhooks → Add webhook
2. Payload URL: `https://packagist.org/api/github?username=EmircanBuluslu`
3. Content type: `application/json`
4. Events: "Just the push event"
5. Active: ✓

## 4. Versiyon Etiketleme

```bash
git tag -a v1.0.0 -m "Release v1.0.0"
git push origin v1.0.0
```

## 5. composer.json Düzenlemeleri (Opsiyonel)

Yayınlamadan önce composer.json'u güncelleyin:

```json
{
    "name": "devhealth/laravel-health",
    "description": "Laravel project health checker - scans for security, configuration, and structural risks",
    "keywords": ["laravel", "health", "security", "doctor", "cli", "audit"],
    "homepage": "https://github.com/EmircanBuluslu/dev-health",
    "license": "MIT",
    "authors": [
        {
            "name": "Emircan BULUŞLU",
            "email": "bulusluemircan723@gmail.com"
        }
    ],
    "support": {
        "issues": "https://github.com/EmircanBuluslu/dev-health/issues",
        "source": "https://github.com/EmircanBuluslu/dev-health"
    }
}
```

## 6. README.md Güncelleme

README.md dosyasına badge'ler ekleyin:

```markdown
[![Latest Version](https://img.shields.io/packagist/v/devhealth/laravel-health.svg)](https://packagist.org/packages/devhealth/laravel-health)
[![Total Downloads](https://img.shields.io/packagist/dt/devhealth/laravel-health.svg)](https://packagist.org/packages/devhealth/laravel-health)
[![License](https://img.shields.io/packagist/l/devhealth/laravel-health.svg)](https://packagist.org/packages/devhealth/laravel-health)
```

## 7. Yayınlandıktan Sonra

Artık herkes şu şekilde kurabilir:

```bash
composer require devhealth/laravel-health --dev
```

## Private Paket (Alternatif)

Eğer paketi açık kaynak yapmak istemiyorsanız:

### Satis ile Private Packagist

1. [Private Packagist](https://packagist.com) hesabı açın
2. Repo'nuzu ekleyin
3. Kullanıcılara auth.json verin

### Veya VCS Repository

Kullanıcılar composer.json'a ekler:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/EmircanBuluslu/dev-health"
        }
    ]
}
```
