# Debug Kodu Kontrolü

## Ne Yapar?

Kodunuzda unutulmuş debug kodlarını tespit eder. Production ortamına çıkmadan önce bu kodların temizlenmesi gerekir.

## Neden Önemli?

### Güvenlik Riskleri
```javascript
// ❌ Hassas veri sızıntısı
console.log('User password:', user.password);
console.log('API Key:', process.env.API_KEY);
```

### Performance Sorunları
```php
// ❌ Her istekte log yazılıyor
foreach ($users as $user) {
    dump($user); // 10,000 kullanıcı = 10,000 dump!
}
```

### Profesyonellik
```javascript
// ❌ Kullanıcı browser console'da görür
alert('Test mesajı'); // Kullanıcıya görünür!
debugger; // Browser durur!
```

---

## Tespit Edilen Debug Kodları

### JavaScript/TypeScript/Vue/React

| Kod | Risk | Açıklama |
|-----|------|----------|
| `console.log()` | 🟡 Orta | En yaygın debug kodu |
| `console.debug()` | 🟡 Orta | Debug mesajları |
| `console.warn()` | 🟡 Orta | Uyarı mesajları |
| `console.error()` | 🟡 Orta | Hata mesajları |
| `console.info()` | 🟡 Orta | Bilgi mesajları |
| `console.table()` | 🟡 Orta | Tablo formatında log |
| `alert()` | 🔴 Yüksek | Kullanıcıya görünür popup |
| `debugger;` | 🔴 Yüksek | Browser'ı durdurur |

### PHP/Laravel

| Kod | Risk | Açıklama |
|-----|------|----------|
| `dd()` | 🔴 Yüksek | Die and dump - uygulamayı durdurur |
| `dump()` | 🟡 Orta | Veri dump eder |
| `var_dump()` | 🟡 Orta | PHP var dump |
| `print_r()` | 🟡 Orta | Array/object yazdırır |
| `var_export()` | 🟡 Orta | PHP var export |
| `ray()` | 🟡 Orta | Ray debug tool |
| `->dump()` | 🟡 Orta | Collection dump |
| `->dd()` | 🔴 Yüksek | Collection die and dump |

---

## Örnek Çıktı

```
═══ Debug Kodu Kontrolü ═══

⚠ Debug kodu tespit edildi: console.log() kullanımı
   📁 resources/js/components/UserList.vue:42
   💡 Production ortamına çıkmadan önce bu satırı kaldırın
   • code: console.log('Users loaded:', users);
   • type: console.log() kullanımı

⚠ Debug kodu tespit edildi: dd() kullanımı
   📁 app/Http/Controllers/UserController.php:28
   💡 Production ortamına çıkmadan önce bu satırı kaldırın
   • code: dd($user);
   • type: dd() kullanımı
```

---

## Hariç Tutulan Klasörler

Aşağıdaki klasörler taranmaz:

- `vendor/` - Composer paketleri
- `node_modules/` - NPM paketleri
- `storage/` - Depolama dosyaları
- `bootstrap/cache/` - Cache dosyaları
- `.git/` - Git dosyaları
- `tests/` - Test dosyaları
- `database/factories/` - Factory dosyaları
- `database/seeders/` - Seeder dosyaları

---

## Yorum Satırları

Yorum satırlarındaki debug kodları görmezden gelinir:

```php
// dd($user); // ✅ Bu tespit edilmez (yorum satırı)

dd($user); // ❌ Bu tespit edilir
```

```javascript
// console.log('test'); // ✅ Bu tespit edilmez

console.log('test'); // ❌ Bu tespit edilir
```

---

## Nasıl Düzeltilir?

### 1. Manuel Temizleme
```bash
# Tüm console.log'ları bul
grep -r "console.log" resources/js/

# Tüm dd()'leri bul
grep -r "dd(" app/
```

### 2. IDE ile Temizleme
- PHPStorm: Find in Files (Ctrl+Shift+F)
- VSCode: Search (Ctrl+Shift+F)

### 3. Git Hook ile Önleme
```bash
# .git/hooks/pre-commit
#!/bin/bash

if git diff --cached | grep -E "console\.log|dd\(|dump\("; then
    echo "❌ Debug kodu tespit edildi!"
    exit 1
fi
```

---

## Best Practices

### ✅ Doğru Kullanım

```php
// Logger kullan
Log::info('User logged in', ['user_id' => $user->id]);

// Exception fırlat
throw new \Exception('Invalid data');
```

```javascript
// Logger kullan
logger.info('User action', { userId: user.id });

// Error handling
try {
    // kod
} catch (error) {
    logger.error('Error occurred', error);
}
```

### ❌ Yanlış Kullanım

```php
// Production'da kalmamalı
dd($user);
dump($data);
var_dump($array);
```

```javascript
// Production'da kalmamalı
console.log('Debug:', data);
alert('Test');
debugger;
```

---

## CI/CD Entegrasyonu

### GitHub Actions

```yaml
- name: Check for debug code
  run: |
    php artisan dev:health --format=json --output=health.json
    if grep -q '"status":"FAIL"' health.json; then
      echo "❌ Debug kodu bulundu!"
      exit 1
    fi
```

### GitLab CI

```yaml
health_check:
  script:
    - php artisan dev:health --format=json --output=health.json
    - if grep -q '"status":"FAIL"' health.json; then exit 1; fi
```

---

