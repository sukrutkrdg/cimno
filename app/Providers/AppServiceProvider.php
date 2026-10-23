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
        RateLimiter::for('pin-login', function (Request $request) {
            $phone = preg_replace('/\D+/', '', (string) $request->input('phone'));
            $phoneKey = $phone !== '' ? $phone : 'no-phone';

            return [
                Limit::perMinutes(10, 8)->by('pin-phone:' . $phoneKey),
                Limit::perMinute(30)->by('pin-ip:' . $request->ip()),
            ];
        });
    }
}
