<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Natro/paylaşımlı hosting gibi ortamlarda eski MySQL/MariaDB (utf8mb4 + innodb_large_prefix
        // kapalı) sürümleri 767 byte'tan uzun unique index'lerde hata verebilir. Varsayılan string
        // uzunluğunu 191'e sabitlemek, migration'lardaki unique() string kolonları için güvenli.
        Schema::defaultStringLength(191);

        // Veli portalı/QR yoklama girişi 4 haneli PIN (10.000 olasılık) kullanır; eski
        // throttle:30,1 yalnızca IP bazlıydı, yani birkaç rotasyonlu IP ile bilinen bir
        // telefon numarasının PIN'i saatler içinde taranabilirdi. Bu limit asıl olarak
        // GÖNDERİLEN TELEFON NUMARASINA göre sınırlar (rotasyonlu IP'ler bunu aşamaz),
        // ayrıca tek bir IP'nin çok sayıda farklı telefon denemesine karşı ayrı bir tavan koyar.
        //
        // ÖNEMLİ: /mobil ve /qr-yoklama rotaları GET ile hem PIN girişini HEM DE giriş
        // yapılmış velinin sekme gezinmesini (çocuklarım/aidat/karne/duyurular) aynı
        // controller üzerinden yönetiyor. Sekme gezinirken phone/pin tekrar gönderilmez
        // (oturumda tutulur), bu da isteği paylaşılan 'no-phone' kovasına düşürüyordu —
        // bu kova TÜM ziyaretçiler arasında ortak ve 10 dakikada sadece 8 istekle
        // sınırlıydı, yani birkaç kişinin normal sekme gezinmesi bile tüm siteyi
        // kilitleyip herkese 429 "Too Many Requests" veriyordu. Bunu önlemek için
        // limiter'ı sadece GERÇEK bir PIN deneme isteğinde (telefon VE pin birlikte
        // gönderildiğinde) uyguluyoruz; sade sayfa görüntüleme/sekme gezinmesi
        // sınırsız kalır.
        RateLimiter::for('pin-login', function (Request $request) {
            $inputPhone = $request->input('phone');
            $inputPin = $request->input('pin');

            if ($inputPhone === null || $inputPin === null) {
                return Limit::none();
            }

            $phone = preg_replace('/\D+/', '', (string) $inputPhone);
            $phoneKey = $phone !== '' ? $phone : 'no-phone';

            return [
                Limit::perMinutes(10, 8)->by('pin-phone:' . $phoneKey),
                Limit::perMinute(30)->by('pin-ip:' . $request->ip()),
            ];
        });
    }
}
