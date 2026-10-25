# Natro Hosting Deploy — Nasıl Kuruldu (cPanel)

Bu proje Natro "Sınırsız Pro Hosting" üzerinde **cPanel** (CloudLinux,
LiteSpeed) ile canlıya alındı — `enerjikcimnastik.com`, 2026-10-25.
İlk denemede hesap Plesk üzerindeydi ve PHP 7.4/MariaDB 5.5 ile
Laravel 12 çalışmıyordu; Natro destek "cPanel kullanın, istediğiniz PHP
sürümünü ve güncel MariaDB'yi kullanabilirsiniz" dedi ve hesap cPanel'e
geçirildi. Bu dosya gerçekte izlenen adımları anlatır — ileride tekrar
deploy etmek ya da başka bir hesapta aynısını yapmak için.

## Hesap Bilgileri

- Panel: `https://cpls24.srvpanel.com:2083` (cPanel kullanıcı: `u2920424`)
- SSH yok, Composer paneli yok — **Git™ Version Control** (cPanel'in
  kendi aracı) ve **Cron Jobs** üzerinden deploy edildi.
- PHP: **8.5** (Select PHP Version aracından seçildi — yerel geliştirme
  ortamıyla aynı sürüm).
- Veritabanı: MariaDB **10.6.27** (InnoDB destekli, JSON kolon sorunu yok).

## Kurulum Adımları (özet)

1. **Git clone**: cPanel → Git™ Version Control → "Clone a Repository" →
   `https://github.com/sukrutkrdg/cimno.git` → path `enerjikcimnastik`
   (sonuç: `/home/u2920424/enerjikcimnastik`, repo dizini web kökü
   *dışında*, güvenli).
2. **`vendor/` yükleme**: SSH/Composer olmadığı için `vendor/` yerelde
   `composer install --no-dev --optimize-autoloader` ile üretildi, zip'lenip
   cPanel Dosya Yöneticisi'nden `enerjikcimnastik/` içine yüklenip orada
   açıldı (Çıkar).
3. **`.env`**: Yerelde `.env.production` olarak hazırlanıp (taze `APP_KEY`,
   taze VAPID anahtarları, gerçek veritabanı bilgileri ile) sunucuya
   `enerjikcimnastik/.env` olarak yüklendi.
4. **Veritabanı**: cPanel → MySQL® Veritabanları'ndan `u2920424_app`
   veritabanı ve `u2920424_app` kullanıcısı oluşturuldu (tam yetkiyle).
5. **`config/database.php`**: `'engine' => 'InnoDB'` eklendi — bu sunucunun
   varsayılan tablo motoru MyISAM'dı (max key length 1000 bayt), bu da
   `failed_jobs` tablosundaki composite index migration'ını kırıyordu.
   InnoDB'yi açıkça istemek sorunu çözdü.
6. **Migration + seed**: SSH olmadığından, cPanel **Cron Jobs** aracında
   geçici bir görev olarak çalıştırıldı:
   ```
   /usr/bin/php /home/u2920424/enerjikcimnastik/artisan migrate:fresh --seed --force
   ```
   (çıktısı bir log dosyasına yönlendirilip kontrol edildi, sonra görev
   silindi — tek seferlik işlemler için bu yöntem kullanılacak).
7. **Belge kökü (document root) cPanel'de değiştirilemiyor** (bu hesapta
   "Alan Adları" aracı 403 veriyor — ana domain için sabit `public_html`).
   Bunun yerine klasik cPanel-Laravel çözümü uygulandı:
   - `enerjikcimnastik/public/*` içeriği `public_html/`'e **kopyalandı**
     (taşınmadı — kaynak `public/` dizini de olduğu gibi duruyor, gelecekte
     güncellemelerde tekrar kopyalanacak).
   - `public_html/index.php` düzenlendi: `__DIR__.'/../vendor/...'` ve
     `__DIR__.'/../bootstrap/...'` yolları `__DIR__.'/../enerjikcimnastik/vendor/...'`
     ve `.../enerjikcimnastik/bootstrap/...` olarak değiştirildi (gerçek
     Laravel uygulaması `public_html` dışında, `enerjikcimnastik/` içinde
     yaşıyor).
   - **ÖNEMLİ — `.htaccess`**: `public/` içindeki `.htaccess` dosyası da
     `public_html`'e kopyalanmalı! İlk denemede Dosya Yöneticisi'nde
     "gizli dosyaları göster" kapalıyken "Tümünü Seç" yapıldığından
     `.htaccess` kopyalanmadı ve `public_html`'de Natro'nun eski
     varsayılan `.htaccess`'i kaldı — bu da ana sayfa dışındaki tüm
     rotaların (`/panel`, `/mobil` vb.) 404 vermesine yol açtı (LiteSpeed
     seviyesinde, Laravel'e hiç ulaşmadan). Dosya Yöneticisi
     Ayarlar'dan "Gizli Dosyaları Göster (dotfiles)" **açılmalı**, sonra
     `.htaccess` ayrıca kopyalanmalı.
   - Eski `public_html` içeriği (Natro'nun varsayılan "coming soon"
     şablonu: bootstrap-3.3.7, index.html vb.) silinmeden
     `/home/u2920424/eski-site-yedek/` klasörüne taşındı (web kökü
     dışında, zararsız duruyor).
8. **Cron (kalıcı)**: cPanel Cron Jobs'a eklendi:
   ```
   * * * * * /usr/bin/php /home/u2920424/enerjikcimnastik/artisan schedule:run >> /dev/null 2>&1
   ```
   Hem günlük hatırlatma kuyruğunu hem de 15 dakikada bir çalışan Web
   Push "ders saati yaklaşıyor" görevini bu tek satır yönetiyor.
9. **Bir Blade bug'ı da bu oturumda bulunup düzeltildi**: `home.blade.php`
   içindeki JSON-LD `'@context'` anahtarı, Laravel 12'nin yeni `@context`
   Blade direktifiyle çakışıp sayfaya ham PHP kodu sızdırıyordu. Düzeltme:
   `'@@context'` (Blade'in `@`'i literal yazdırma kaçışı).

## Otomatik Deploy (2026-10-25'ten itibaren)

`git` binary'si sunucuda mevcut (`/usr/bin/git`) ve cPanel Git
Version Control'un dışından da (plain `git pull`) çalışıyor. Bu yüzden
kalıcı bir Cron Job eklendi:

```
*/5 * * * * cd /home/u2920424/enerjikcimnastik && git pull origin main > /home/u2920424/deploy.log 2>&1
```

Bu sayede `main` dalına her `git push` yapıldığında, en geç 5 dakika
içinde kod sunucuda otomatik güncelleniyor — cPanel panelinde "Update
from Remote" butonuna manuel basmaya gerek yok. Çıktı
`/home/u2920424/deploy.log` dosyasına yazılıyor (her çalıştırmada
üzerine yazılıyor, en son durumu gösterir).

**Önemli sınır — bu cron SADECE `enerjikcimnastik/` reposunu günceller:**
- `public/` içinde değişen statik dosyalar (görsel, `manifest.json` vb.)
  otomatik olarak `public_html/`'e kopyalanmıyor — bu hâlâ manuel.
- Veritabanı şeması değiştiyse (yeni migration), `migrate --force` hâlâ
  ayrı bir geçici Cron Jobs görevi olarak elle tetiklenmeli.
- Blade/view önbelleği normalde dosya zaman damgasına göre otomatik
  yenileniyor, ek işlem gerekmiyor.

## Hâlâ Yapılacaklar

- [ ] **SSL/HTTPS**: Bu hesabın "smb" (sınırlı) panel görünümünde manuel
      Let's Encrypt butonu yok; cPanel'in AutoSSL'i sunucu seviyesinde
      otomatik/periyodik çalışıyor olmalı (genelde domain DNS doğru
      işaret ettikten sonraki ilk döngüde, birkaç saat içinde). Birkaç
      saat sonra `https://enerjikcimnastik.com` kontrol edilmeli; hâlâ
      çalışmıyorsa Natro destek'e "AutoSSL bu domain için hiç çalışmadı,
      manuel tetikleyebilir misiniz?" diye sorulmalı. Kamera ile QR okuma
      özelliği SSL olmadan çalışmaz.
- [ ] **Varsayılan admin şifresi değiştirilmeli**: `yonetici@enerjikcimnastik.com`
      / `enerjik2026` — canlıya tam geçmeden önce Personel sekmesinden
      değiştirilmeli.
- [ ] `database/vercel.sqlite` / Vercel deployment'ı artık gerekli mi
      karar verilmeli (DNS zaten Natro'ya bakıyor).

## Gelecekte Kod Güncellemesi Nasıl Yapılır

SSH yok, ama artık otomatik deploy cron'u var (yukarıdaki bölüme bakın).
Her kod değişikliğinde:
1. Yerelde commit + `git push origin main`.
2. En geç 5 dakika içinde cron otomatik `git pull` yapıp sunucudaki
   `enerjikcimnastik/` reposunu günceller — manuel "Update from Remote"
   tıklamaya gerek yok (istenirse yine de cPanel → Git™ Version
   Control'den anında tetiklenebilir).
3. `public/` klasöründe değişen statik dosya varsa (ör. yeni bir
   görsel, `manifest.json`), aynı dosyaları tekrar `public_html`'e
   kopyalamayı unutma (otomatik senkronize olmuyor, cron sadece git
   reposunu günceller).
4. Veritabanı şeması değiştiyse, Cron Jobs'tan geçici bir
   `php artisan migrate --force` görevi ekleyip çalıştır, sonra sil.
5. Blade/view değişikliği sonrası görünüm eskiyse, aynı şekilde geçici
   `php artisan view:clear` görevi çalıştırılabilir (genelde gerekmez,
   Laravel dosya zaman damgasına göre otomatik yeniden derler).
