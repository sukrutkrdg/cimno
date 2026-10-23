# Natro Hosting'e Deploy Checklist

Bu proje Vercel yerine Natro (paylaşımlı/VPS cPanel hosting) üzerinde çalışacak
şekilde hazırlandı. Aşağıdaki adımları sırayla takip edin.

## 1. Sunucu Gereksinimleri

- **PHP 8.3+** (cPanel → "MultiPHP Manager" üzerinden seçin)
- **MySQL 5.7+ / MariaDB 10.3+**
- Gerekli PHP eklentileri: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo` (çoğu cPanel PHP paketinde varsayılan açık gelir, MultiPHP Manager → "PHP Extensions" sekmesinden kontrol edin)

## 2. Dosyaları Yükleme

- Git erişimi varsa (Natro bazı planlarda SSH/Git sunar): `git clone` + `composer install --no-dev --optimize-autoloader`
- Yoksa: `vendor/` klasörünü **yerel bilgisayarınızda** `composer install --no-dev --optimize-autoloader` ile üretip, projenin tamamını FTP/cPanel File Manager ile yükleyin (`vendor/` ve `node_modules` hariç `.gitignore` kurallarına bakın — `node_modules` yok, `public/uploads` ve `.env` yüklenmemeli, onları ayrıca elle oluşturacaksınız).

## 3. Doküman Kökü (Document Root)

Laravel'in `public/` klasörü, alan adının kök dizini olmalı. cPanel'de:

- **Seçenek A (önerilen, SSH/Terminal varsa):** Domain'in document root'unu doğrudan projenin `public/` klasörüne ayarlayın (cPanel → "Domains" → "Document Root" alanını düzenleyin).
- **Seçenek B (document root değiştirilemiyorsa):** Projeyi `public_html` dışına yükleyip, `public/` içeriğini `public_html`'e kopyalayın ve `public_html/index.php` içindeki `require __DIR__.'/../vendor/autoload.php'` ve `$app = require_once __DIR__.'/../bootstrap/app.php';` satırlarındaki yolları projenin gerçek konumuna göre düzeltin.

## 4. `.env` Dosyası

Sunucuda `.env.example`'ı kopyalayıp düzenleyin:

```
APP_NAME="Tekirdağ Enerjik Cimnastik"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://siteniz.com
APP_TIMEZONE=Europe/Istanbul

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kullaniciadi_enerjik
DB_USERNAME=kullaniciadi_dbuser
DB_PASSWORD=guclu-bir-sifre

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
```

- `APP_KEY` boş bırakmayın: `php artisan key:generate` çalıştırın (SSH yoksa yerelde üretip `.env`'e yapıştırın).
- **`APP_DEBUG=false` kritiktir** — `true` kalırsa hata sayfalarında veritabanı bilgileri ve stack trace sızabilir.
- cPanel'de oluşturduğunuz MySQL veritabanı ve kullanıcı adları genelde `cpaneluser_` önekiyle gelir; `DB_DATABASE`/`DB_USERNAME` buna göre ayarlanmalı.

## 5. Veritabanı Migrasyonları

```
php artisan migrate --force
```

Demo verisiyle başlamak isterseniz (**dikkat: gerçek veriye dokunmadan önce** test ortamında deneyin):

```
php artisan db:seed --force
```

## 6. Zamanlanmış Görevler (Cron)

Günlük hatırlatma kuyruğu (`routes/console.php` → `enerjik:daily-tasks`) için cPanel'de
**Cron Jobs** bölümünden şu satırı ekleyin (her dakika çalışır, Laravel kendi içinde saatini kontrol eder):

```
* * * * * cd /home/kullaniciadi/public_html && php artisan schedule:run >> /dev/null 2>&1
```

Bu kurulmazsa doğum günü/vade hatırlatma mesajları hiç üretilmez. Aynı satır,
15 dakikada bir çalışan `enerjik:ders-bildirimi-gonder` (Web Push "ders saati
yaklaşıyor" bildirimi) görevini de tetikler — ayrı bir cron satırı eklemenize
gerek yok, Laravel zamanlayıcısı ikisini de bu tek `schedule:run` çağrısından yönetir.

### Web Push Bildirimleri (Ders Saati Yaklaşıyor) — Tek Seferlik Kurulum

```
php artisan enerjik:vapid-anahtar-uret
```

Çıktıdaki `VAPID_PUBLIC_KEY` / `VAPID_PRIVATE_KEY` satırlarını `.env` dosyasına
yapıştırın. Bu adım atlanırsa veli portalındaki "🔔 Bildirimler" butonu pasif
kalır, sistemin geri kalanı (WhatsApp hatırlatmaları dâhil) etkilenmez.

## 7. Dosya İzinleri

```
chmod -R 755 storage bootstrap/cache
```

`storage/` ve `bootstrap/cache/` dizinlerinin web sunucusu tarafından yazılabilir olması gerekir. Galeri fotoğraf yüklemeleri için `public/uploads/` otomatik oluşturulur; önceden oluşturup `chmod 755 public/uploads` yapmanız da sorun değildir.

## 8. SSL / HTTPS

Kamera ile QR okuma özelliği (veli ekranındaki "Kamerayı Aç ve Salon QR Afişini Okut")
**yalnızca HTTPS üzerinde çalışır**. Natro'nun ücretsiz Let's Encrypt SSL'ini
cPanel → "SSL/TLS Status" üzerinden aktif edin ve `APP_URL`'in `https://` ile başladığından
emin olun.

## 9. İlk Giriş & Acil Durum Şifre Sıfırlama

- Demo seed çalıştırıldıysa varsayılan giriş: `yonetici@enerjikcimnastik.com` / seed'de tanımlı şifre. **Canlıya geçmeden önce bu şifreyi değiştirin** (Personel sekmesinden kendi hesabınızın şifresini güncelleyin).
- Süper yönetici şifresini unutursanız, SSH/Terminal erişiminiz varsa:
  ```
  php artisan admin:sifre-sifirla yonetici@enerjikcimnastik.com YeniGuvenliSifre123
  ```

## 10. Son Kontrol Listesi

- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] `.env` dosyası `.gitignore`'da (asla repoya commit edilmemeli)
- [ ] `php artisan migrate --force` hatasız tamamlandı
- [ ] Cron job eklendi (`schedule:run`)
- [ ] SSL aktif, site `https://` ile açılıyor
- [ ] `/panel/giris` sayfasında "Hızlı Rol Testi" bölümü **görünmüyor** (yalnızca local/testing ortamında görünür; production'da otomatik gizlenir, ekstra işlem gerekmez)
- [ ] Varsayılan demo şifreler değiştirildi
- [ ] `storage/` ve `bootstrap/cache/` yazılabilir
- [ ] `php artisan enerjik:vapid-anahtar-uret` çalıştırılıp `.env`'e eklendi (Web Push bildirimleri için, opsiyonel ama önerilir)
